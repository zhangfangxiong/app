<?php
include_once("baseTool.php");
include_once("model/province.php");
include_once("model/city.php");
include_once('../lib/memcache.php');
include_once('../lib/excelReader.php');


class qqDetailTool extends baseTool
{
    private $_iImportSupportType = array(".xls", ".csv", ".txt");//支持导入的格式
    const EXLODENUM = 50000;//每次读取表的数据条数
    const BOY = "男";
    const GIRL = "女";
    private $_aCheck = array(0 => "请选择", 1 => "回答问题", 2 => "拒加", 3 => "审核问题", 4 => "需要验证", 5 => "直接添加", 6 => "其他");

    public function indexAction()
    {
        $this->display("qqDetail/index.phtml");
    }

    public function explodeFileListAction()
    {
        $oDB = $this->getDB();
        $oProvince = new Model_Provice();
        $aProvice = $oProvince->getProvinceList();
        $this->assign('aProvice', $aProvice);
        $oCity = new Model_city();
        $aCity = $oCity->getCityList();
        $this->assign('aCity', $aCity);
        $this->display("qqDetail/explodeFileList.phtml");
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
        $iImportType = $_POST['iImportType'];
        //$sFileName = $_GET['sFileName'];
        if (!$sFileName) {
            showError("请选择文件", true);
        }
        if (!isset($_POST['iImportType'])) {
            showError("请选择导入种类", true);
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
        $this->display("qqDetail/getFileList.phtml");
    }

    private function _importCsv($sFileName)//csv格式导入
    {
        $file = fopen($GLOBALS['FILEPATH'] . $sFileName, "r");

        while (!feof($file)) {
            print_r(fgetcsv($file));
        }

        fclose($file);
    }

    /**
     * 修改字段
     * @param $sFileName
     * @param $iType1验证种类2生日导入
     * @return bool
     */
    private function _updateTxt($sFileName)
    {
        $oFile = fopen($GLOBALS['FILEPATH'] . $sFileName, "r");
        $aCheck = array_flip($this->_aCheck);
        if ($oFile) {
            $oDB = $this->getDB();
            $i = 1;
            while (!feof($oFile)) {
                //组装成存入数据表格式
                $sData = preg_replace("/\s/", "", fgets($oFile));
                $aData = explode("|", $sData);
                if (count($aData) != 2) {
                    //showError("txt第".$i."行数据格式有误",true);
                    continue;
                }

                if (!empty($aData)) {
                    if ($_POST['iImportType'] == 1) {
                        if (isset($aCheck[$aData[1]])) {
                            $aData[1] = $aCheck[$aData[1]];
                        } else {
                            $aData[1] = 0;//其他
                        }
                        $aDataChange = array('checktype' => $aData[1]);
                        $sWhere = "qqnum='" . $aData[0] . "'";
                        $oDB->update("qqDetail", $aDataChange, $sWhere);
                    } else {
                        if ($_POST['iImportType'] == 2) {
                            $iBirth = strtotime($aData[1]);
                            if (!$iBirth) {
                                //showError("txt第".$i."行数据生日格式有误",true);
                                continue;
                            }
                            $sql = "INSERT INTO qqDetail (qqnum,birth) VALUES ($aData[0],$iBirth) ON DUPLICATE KEY UPDATE birth=$iBirth";
                            $oDB->query($sql);
                        }
                    }

                }
                $i++;
            }
        }
        fclose($oFile);

        return true;
    }

    private function _importTxt($sFileName)//txt格式导入
    {
        $oFile = fopen($GLOBALS['FILEPATH'] . $sFileName, "r");
        $iTime = (isset($_POST['CreateTime']) && $_POST['CreateTime']) ? strtotime($_POST['CreateTime']) : time();
        if ($oFile) {
            $oDB = $this->getDB();
            $oProvince = new Model_Provice();
            $oCity = new Model_city();
            $i = 1;
            while (!feof($oFile)) {
                //组装成存入数据表格式
                $sData = preg_replace("/\s/", "", fgets($oFile));
                $aData = explode("|", $sData);
                if (count($aData) != 5) {
                    //showError("txt第".$i."行数据格式有误",true);
                    continue;
                }
                if ($sData) {
                    $aProvince = $oProvince->getProvinceByName($aData[1]);
                    $aCity = $oCity->getCityByName($aData[2]);
                    $iProvinceID = empty($aProvince) ? 0 : $aProvince['code'];
                    $iCityID = empty($aCity) ? 0 : $aCity['code'];
                    if ($aData[3] == self::BOY) {
                        $aData[3] = 1;
                    } else {
                        if ($aData[3] == self::GIRL) {
                            $aData[3] = 2;
                        } else {
                            $aData[3] = 0;
                        }
                    }

                    $aTmp = array(
                        'qqnum' => $aData[0],
                        'province' => $iProvinceID,
                        'city' => $iCityID,
                        'sex' => $aData[3],
                        'age' => $aData[4],
                        'importtime' => $iTime
                    );
                    $oDB->insert('qqDetail', $aTmp, 'ignore');
                }
                $i++;
            }
        }
        fclose($oFile);

        return true;
    }

    private function _importxls($sFileName)//xls格式的最多支持6W数据，需要先把内存调到512
    {
        $oProvince = new Model_Provice();
        $oCity = new Model_city();
        echo (memory_get_usage() / 1024) . "<br>";
        $oData = new Spreadsheet_Excel_Reader($GLOBALS['FILEPATH'] . $sFileName);
        echo (memory_get_usage() / 1024) . "<br>";
        $aData = $oData->sheets[0]['cells'];
        $iTime = (isset($_POST['CreateTime']) && $_POST['CreateTime']) ? strtotime($_POST['CreateTime']) : time();
        $oDB = $this->getDB();
        if (!empty($aData)) {
            foreach ($aData as $key => $value) {
                if ($key == 1) {
                    continue;
                }
                $aProvince = $oProvince->getProvinceByName($value[2]);
                $aCity = $oCity->getCityByName($value[3]);
                $iProvinceID = empty($aProvince) ? 0 : $aProvince['code'];
                $iCityID = empty($aCity) ? 0 : $aCity['code'];
                if ($value[4] == self::BOY) {
                    $value[4] = 1;
                } else {
                    if ($value[4] == self::GIRL) {
                        $value[4] = 2;
                    } else {
                        $value[4] = 0;
                    }
                }

                $aTmp = array(
                    'qqnum' => $value[1],
                    'province' => $iProvinceID,
                    'city' => $iCityID,
                    'sex' => $value[4],
                    'age' => $value[5],
                    'importtime' => $iTime
                );
                $oDB->insert('qqDetail', $aTmp, 'ignore');
            }
        }
        echo (memory_get_usage() / 1024) . "<br>";
        echo count($aData);
        die;
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
        switch ($iSuffix) {
            case ".xls":
                $this->_importxls($sFileName);
                break;
            case ".csv":
                $this->_importcsv($sFileName);
                break;
            case ".txt":
                if ($_POST['iImportType'] > 0) {
                    $this->_updateTxt($sFileName);
                } else {
                    $this->_importtxt($sFileName);
                }

                break;
        }

        return true;
    }

    /**
     * 导出文件
     */
    public function exportFileAction()
    {
        if (!isset($_POST['sExplodeName']) || !$_POST['sExplodeName']) {
            showError("请输入导出名", true);
        }
        $sFileName = $_POST['sExplodeName'];
        $iSuffix = substr($sFileName, strpos($sFileName, '.'), strlen($sFileName) - 1);
        if ($iSuffix != ".txt") {
            showError("不支持导出" . $iSuffix . "格式文件", true);
        }
        //获取本文件目录的文件夹地址
        $aFilesNames = scandir($GLOBALS['FILEPATH']);
        if (in_array($sFileName, $aFilesNames)) {
            showError("已存在已该名存在的文件，请更改文件名以免覆盖", true);
        }
        if (!isset($_POST['sExplodeNum']) || !$_POST['sExplodeNum']) {
            showError("请输入导出数目", true);
        }
        if (isset($_POST['check_age'])) {
            if ($_POST['check_age'] == 1 && !$_POST['startAge']) {
                showError("您选择的年龄区间，请至少输入开始年龄", true);
            }
            if ($_POST['check_age'] == 2 && !$_POST['startBirth']) {
                showError("您选择的生日区间，请至少输入开始生日", true);
            }
            if ($_POST['check_age'] == 2 && !preg_match(
                    "/^(19|20)[0-9]{2}-[0,1][1-9]-[0,3][1-9]$/ix",
                    $_POST['startBirth']
                )
            ) {
                showError("生日格式有误", true);
            }
            if ($_POST['check_age'] == 2 && $_POST['endBirth'] && !preg_match(
                    "/^(19|20)[0-9]{2}-[0,1][1-9]-[0,3][1-9]$/ix",
                    $_POST['endBirth']
                )
            ) {
                showError("生日格式有误", true);
            }
        }

        $aData = array();
        if ($_POST['sex']) {
            $aData['sex'] = $_POST['sex'];
        }
        if (isset($_POST['check_age'])) {
            if ($_POST['check_age'] == 1) {
                if ($_POST['startAge']) {
                    $aData['startAge'] = $_POST['startAge'];
                }
                if ($_POST['endAge']) {
                    $aData['endAge'] = $_POST['endAge'];
                }
            } elseif ($_POST['check_age'] == 2) {
                if ($_POST['startBirth']) {
                    $aData['startBirth'] = $_POST['startBirth'];
                }
                if ($_POST['endBirth']) {
                    $aData['endBirth'] = $_POST['endBirth'];
                }
            }
        }
        if (isset($_POST['province']) && $_POST['province']) {
            $aData['province'] = $_POST['province'];
        }
        if (isset($_POST['city']) && $_POST['city']) {
            $aData['city'] = $_POST['city'];
        }
        if (isset($_POST['checktype']) && $_POST['checktype']) {
            $aData['checktype'] = $_POST['checktype'];
        }
        $sWhere = "";
        if (!empty($aData)) {
            foreach ($aData as $key => $value) {
                if ($key == "startAge") {
                    $sWhere .= "age>=" . intval($value) . " AND ";
                } elseif ($key == "endAge") {
                    $sWhere .= "age<=" . intval($value) . " AND ";
                } elseif ($key == "startBirth") {
                    $sWhere .= "birth>=" . strtotime($value) . " AND ";
                } elseif ($key == "endBirth") {
                    $sWhere .= "birth<=" . strtotime($value) . " AND ";
                } elseif ($key == "city" || $key == "province") {
                    $sTemp = implode(",", $value);
                    $sWhere .= $key . " IN (" . $sTemp . ") AND ";
                } else {
                    $sWhere .= $key . "=" . $value . " AND ";
                }
            }
        }
        $sSql = "SELECT COUNT(*) AS count FROM  qqDetail";
        if ($sWhere) {
            $sSql .= " WHERE " . $sWhere . " 1 ";
        }
        $oDb = $this->getDB();
        $iTotalNum = $oDb->get_one($sSql);//总条数
        $iTotalNum = MIN($iTotalNum['count'], $_POST['sExplodeNum']);
        $sSql = "SELECT * FROM  qqDetail ";
        if ($sWhere) {
            $sSql .= "WHERE " . $sWhere . " 1 ";
        }
        $iStart = 0;//第一条记录id
        $iLimit = self::EXLODENUM;//每次读取的条数
        $iExplodeTimes = ceil($iTotalNum / $iLimit);//需要读取的总次数
        $sFilePath = $GLOBALS['FILEPATH'];
        $sFileName = $sFilePath . $sFileName;
        $fp = fopen($sFileName, "w+");
        $sTime = time();
        for ($i = 0; $i < $iExplodeTimes; $i++) {
            $sql = $sSql;
            $iStart += $i * $iLimit;
            $iListNum = $iTotalNum - $i * $iLimit;//剩余条数
            $iLimitNum = $iListNum > $iLimit ? $iLimit : $iListNum;
            $sql .= "LIMIT " . $iStart . "," . $iLimitNum;
            $aData = $oDb->get_all($sql);
            $oProvince = new Model_Provice();
            $oCity = new Model_city();
            foreach ($aData as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($k == "province") {
                        $aProvince = $oProvince->getProvinceByID($v);
                        if (!empty($aProvince)) {
                            $aData[$key][$k] = $aProvince["name"];
                        }
                    } elseif ($k == "city") {
                        $aCity = $oCity->getCityByID($v);
                        if (!empty($aCity)) {
                            $aData[$key][$k] = $aCity["name"];
                        }
                    } elseif ($k == "sex") {
                        if ($v == 1) {
                            $aData[$key][$k] = self::BOY;
                        } elseif ($v == 2) {
                            $aData[$key][$k] = self::GIRL;
                        } else {
                            $aData[$key][$k] = "无";
                        }
                    } elseif ($k == "checktype") {
                        $aData[$key][$k] = $this->_aCheck[$v];
                    } elseif ($k == "adoption") {
                        $aData[$key][$k] = date("Y-m-d H:i:s", $sTime);
                        $oDb->update("qqDetail", array("adoption" => $sTime), "id=" . $value['id']);
                    } elseif ($k == "importtime") {
                        unset($aData[$key][$k]);
                    } elseif ($k == "birth") {
                        $aData[$key][$k] = date("Y-m-d", $v);
                    }
                }
                unset($aData[$key]['id']);
                $sStr = implode("|", $aData[$key]);
                fwrite($fp, $sStr . "\r\n");
            }
        }
        fclose($fp);
        //$this->_operateLog(1, time());//记录导出log
        showOk("文件导出完成", $GLOBALS['CURRURL']);
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

    /**
     * 删除缓存
     */
    public function flushCacheAction()
    {
        $oMem = $this->getMem();
        $oMem->flush();
    }

}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
$qqTool = new qqDetailTool();
?>
