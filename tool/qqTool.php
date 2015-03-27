<?php
include_once("baseTool.php");

class qqTool extends baseTool
{
    private $_iImportSupportType = array(".txt");//支持导入的格式
    const EXLODENUM = 50000;//每次读取表的数据条数

    public function indexAction()
    {
        $this->display("qqtool/index.phtml");
    }

    public function explodeFileListAction()
    {
        $sSql = "SELECT * FROM eximLog WHERE type=0";
        $oDB = $this->getDB();
        $aData = $oDB->get_all($sSql);

        $this->assign('aImportTimes', $aData);
        $this->display("qqtool/explodeFileList.phtml");
    }

    /**
     * 获取QQ列表
     */
    public function getQqListAction()
    {

    }

    /**
     * 导入QQ数据
     */
    public function importDataAction()
    {
        $sFileName = $_POST['sFileName'];
        if (!$sFileName) {
            showError("请选择文件", true);
        }
        $aFilesnames = scandir($GLOBALS['FILEPATH']);

        if (!in_array($sFileName, $aFilesnames)) {
            showError("该文件不存在", true);
        }
        $bResult = $this->_readFileBatch($sFileName);
        if ($bResult) {
            showOk("文件导入完成", $GLOBALS['CURRURL']);
        }
    }

    /**
     * 获取文件列表
     */
    public function getFileListAction()
    {
        //获取本文件目录的文件夹地址
        $aFilesNames = scandir($GLOBALS['FILEPATH']);
        //前面两个去掉
        array_shift($aFilesNames);
        array_shift($aFilesNames);
        $this->assign('aFilesNames', $aFilesNames);
        $this->display("qqtool/getFileList.phtml");
    }

    /**
     * 读取文件批处理
     * @param $sFileName
     * return array
     */
    private function _readFileBatch($sFileName)
    {
        $iSuffix = substr($sFileName, strpos($sFileName, '.'), strlen($sFileName) - 1);
        if (!in_array($iSuffix, $this->_iImportSupportType)) {
            showError("不支持该种格式", true);
        }
        $oFile = fopen($GLOBALS['FILEPATH'] . $sFileName, "r");
        $iTime = (isset($_POST['CreateTime']) && $_POST['CreateTime']) ? strtotime($_POST['CreateTime']) : time();
        if ($oFile) {
            $oDB = $this->getDB();
            while (!feof($oFile)) {
                //组装成存入数据表格式
                $sData = trim(fgets($oFile));
                if ($sData) {
                    $aTmp = array(
                        'qqnum' => $sData,
                        'CreateTime' => $iTime
                    );
                    $oDB->insert('qqlist', $aTmp, 'ignore');
                }
            }
        }
        fclose($oFile);
        $this->_operateLog(0, $iTime);//记录导入log
        return true;
    }

    /**
     * 导出文件
     */
    public function exportFileAction()
    {
        if (!$_POST['importType']) {
            showError("请选择导出方式", true);
        }
        if (!$_POST['sExplodeName']) {
            showError("请输入导出名", true);
        }
        $sFileName = $_POST['sExplodeName'];
        $iSuffix = substr($sFileName, strpos($sFileName, '.'), strlen($sFileName) - 1);
        if (!in_array($iSuffix, $this->_iImportSupportType)) {
            showError("不支持导出" . $iSuffix . "格式文件", true);
        }
        //获取本文件目录的文件夹地址
        $aFilesNames = scandir($GLOBALS['FILEPATH']);
        if (in_array($sFileName, $aFilesNames)) {
            showError("已存在已该名存在的文件，请更改文件名以免覆盖", true);
        }
        if ($_POST['importType'] == 1 && !$_POST['importtime']) {
            showError("请选择导出时间", true);
        }
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        if ($_POST['importType'] == 2 && !$startTime) {
            showError("请选择导出时间,开始时间必须输入", true);
        }
        if ($_POST['importType'] == 2 && !$endTime) {
            $endTime = date("Y-m-d H:i:s",time());
        }
        $oDb = $this->getDB();
        if ($_POST['importType'] == 1) {
            $sSql = "SELECT COUNT(*) AS count FROM qqlist WHERE CreateTime=" . strtotime($_POST['importtime']);
        } else {
            $sSql = "SELECT COUNT(*) AS count FROM qqlist WHERE CreateTime>='" . strtotime(
                    $startTime
                ) . "' AND CreateTime <='" . strtotime($endTime) . "'";
        }
        $iTotalNum = $oDb->get_one($sSql);//总条数
        $iTotalNum = $iTotalNum['count'];
        if ($_POST['importType'] == 1) {
            $sSql = "SELECT id FROM qqlist WHERE CreateTime=" . strtotime(
                    $_POST['importtime']
                ) . " order by id Limit 1";
        } else {
            $sSql = "SELECT id FROM qqlist WHERE CreateTime>='" . strtotime(
                    $startTime
                ) . "' AND CreateTime <='" . strtotime($endTime) . "' order by id Limit 1";
        }
        $iFirstID = $oDb->get_one($sSql);//第一条记录id
        $iFirstID = $iFirstID['id'];
        $iLimit = self::EXLODENUM;//每次读取的条数
        $iExplodeTimes = ceil($iTotalNum / $iLimit);//需要读取的总次数
        $sFilePath = $GLOBALS['FILEPATH'];
        $sFileName = $sFilePath . $sFileName;
        $fp = fopen($sFileName, "w+");
        for ($i = 0; $i < $iExplodeTimes; $i++) {
            $iStartID = $iFirstID;
            $iStartID += $i * $iLimit - 1;
            $iListNum = $iTotalNum - $i * $iLimit;//剩余条数
            $iLimitNum =  $iListNum > $iLimit ? $iLimit : $iListNum;
            $sSql = "SELECT * FROM qqlist LIMIT " . $iStartID . "," . $iLimitNum;
            $aData = $oDb->get_all($sSql);
            foreach ($aData as $key => $value) {
                fwrite($fp, $value['qqnum'] . "\n");
            }
        }
        fclose($fp);
        //$this->_operateLog(1, time());//记录导出log
        showOk("文件导出完成", $GLOBALS['CURRURL']);
        /**
         * Header("Content-type:application/octet-stream ");
         * Header("Accept-Ranges:bytes ");
         * header("Content-Disposition:attachment;filename=test.txt ");
         * header("Expires:   0 ");
         * header("Cache-Control:must-revalidate,post-check=0,pre-check=0 ");
         * header("Pragma:public ");
         */
    }

    /**
     * 导入导出的log
     */
    private function _operateLog($iType, $iTime)
    {
        $oDB = $this->getDB();
        $aData = array(
            'type' => $iType,
            'time' => $iTime,
            'username' => $this->getCurrUser()
        );

        return $oDB->insert('eximLog', $aData);
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
$qqTool = new qqTool();
?>
