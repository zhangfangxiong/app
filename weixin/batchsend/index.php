<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");
include_once("../model/Group.php");
include_once("../model/User.php");
include_once("../model/Batchsend.php");


class batchsend_index extends baseWeixin
{

    //private static $batchTime = array('00:00:00', '12:00:00', '18:00:00', '22:00:00', '23:00:00');//群发时间点，定死的
    private static $batchTime = array('00:00:00', '12:00:00', '16:00:00', '22:00:00', '23:00:00');//群发时间点，定死的
    private static $ages = array();
    private static $date = array();
    protected static $aSendList = array();

    //清除缓存
    public function flushMemcacheAction()
    {
        $oMem = $this->getMem();
        $oMem->flush();
        echo '缓存清除成功';
    }

    //获取要群发的年龄段
    private function getSendAges()
    {
        if (empty(self::$ages)) {
            $iMax = 120;
            $aData = array();
            for ($i = 0; $i <= $iMax; $i++) {
                $aData[$i] = $i;
            }
            self::$ages = $aData;
        }
        return self::$ages;
    }

    //获取要群发的日期
    private function getSendDate()
    {
        if (empty(self::$date)) {
            $iMax = 31;
            $aData = array();
            for ($i = 1; $i <= $iMax; $i++) {
                $aData[$i] = $i;
            }
            self::$date = $aData;
        }
        return self::$date;
    }

    /**
     * 获取群发任务列表
     */
    public function batchListAction()
    {
        $aData = $this->getSendList();//群发列表
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getNewsWithKey($sToken, "news");//新闻媒体列表
        $this->assign('aMediaList', $aMediaList);
        $this->assign('aData', $aData);
        $this->assign('aTime', self::$batchTime);
        $this->display("/weixin/batchlist.phtml");
    }

    /**
     * 获取群发任务发送成功的log表
     */
    public function batchSeccListAction()
    {
        $aParam['startTime'] = $_POST['startTime'];
        $aParam['endTime'] = $_POST['endTime'];
        $aData = $this->getSendSeccList($aParam);
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getNewsWithKey($sToken, "news");//新闻媒体列表
        $this->assign('aMediaList', $aMediaList);
        $this->assign('aData', $aData);
        $this->assign('aParam',$aParam);
        $this->display("/weixin/batchsecclist.phtml");
    }

    /**
     * 编辑任务
     */
    public function editBatchAction()
    {

    }

    /**
     * 删除任务
     */
    public function deleteBatchAction()
    {
        if (empty($_GET['id'])) {
            showMsg("非法操作", false);
        }
        $oDb = $this->getDB();
        $result = $oDb->update('batchsend', array('status' => 0), 'id = ' . intval($_GET['id']));
        if ($result) {
            $oMem = $this->getMem();
            $oMem->delete('aSendList');
            showMsg("删除成功", true);
        }
        showMsg("删除失败", false);
    }

    /**
     * 这个专门用来做测试用的
     */
    public function testAction()
    {
        $sToken = $this->getAccessToken();
        //$aMediaNumList = Media::getPermanentNum($sToken);//媒体文件数目列表
        $aMediaList = Media::getMediaByType($sToken, "news");//新闻媒体列表
        //$aGroupList = Group::getGroupList($sToken);//获取分组
        //$this->assign('aGroupList',$aGroupList);
        $this->assign('aMediaList', $aMediaList);
        $this->assign('batchTime', self::$batchTime);
        $this->assign('aAgeList', $this->getSendAges());
        $this->assign('aDateList', $this->getSendDate());
        $this->display("/weixin/batchsend.phtml");
    }

    /**
     * 创建群发页面
     */
    public function initBatchPageAction()
    {
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getMediaByType($sToken, "news");//新闻媒体列表
        $this->assign('aMediaList', $aMediaList);
        $this->assign('batchTime', self::$batchTime);
        $this->assign('aAgeList', $this->getSendAges());
        $this->assign('aDateList', $this->getSendDate());
        if ($_GET['id']) {//是编辑界面
            $oDB = $this->getDB();
            $aData = $oDB->get_one('SELECT * FROM batchsend WHERE id = ' . intval($_GET['id']));
            if (!empty($aData)) {
                $this->assign('aData', $aData);
            }
        }
        $this->display("/weixin/batchsend.phtml");
    }

    /**
     * 创建群发操作（或编辑）
     */
    public function initBatchAction()
    {
        if (intval($_POST['batchAge']) == 10000) {
            showError("请选择年龄段", true);
        }
        if (empty($_POST['media_id'])) {
            showError("请选择图文模版", true);
        }
        if (empty($_POST['batchDate'])) {
            showError("请选择群发日期", true);
        }
        if ($_POST['batchTime'] === null) {
            showError("请选择群发时间", true);
        }
        $aNews['created_at'] = time();
        $aNews['type'] = 'news';
        $aNews['media_id'] = $_POST['media_id'];
        $aNews['send_date'] = $_POST['batchDate'];
        $aNews['send_time'] = $_POST['batchTime'];
        $aNews['send_age'] = intval($_POST['batchAge']);
        $aNews['status'] = 1;
        $oDB = $this->getDB();
        $oMem = $this->getMem();
        if (!empty($_POST['id'])) {
            //是编辑
            $result = $oDB->update('batchsend', $aNews, 'id = ' . intval($_POST['id']));
            if ($result) {
                $oMem->delete('aSendList');
                showOk('修改成功', '?action=batchList');
            }
        } else {
            //将群发列表存入memcache
            $aData = $this->getSendList();
            //将数据存入数据库中
            $oDB->insert("batchsend", $aNews);
            $aNews['id'] = $oDB->insert_id();
            $aData[] = $aNews;
            $oMem->set('aSendList', $aData);
            showOk('创建定时群发成功', '?action=batchList');
        }
    }

    //获取群发列表
    protected function getSendList()
    {
        if (empty(self::$aSendList)) {
            $oMem = $this->getMem();
            $aData = $oMem->get('aSendList');
            if (empty($aData)) {
                $oDB = $this->getDB();
                $aData = $oDB->get_all('SELECT * FROM batchsend WHERE status = 1');
                $oMem->set('aSendList', $aData);
            }
            self::$aSendList = $aData;
        }
        return self::$aSendList;
    }

    //按条件获取群发成功的log表
    protected function getSendSeccList($param = array())
    {
        $oDB = $this->getDB();
        $sql = 'SELECT * FROM batchsendlog WHERE 1';
        if (isset($param['startTime']) && !empty($param['startTime'])) {
            $sql .= ' AND iSeccTime >=' . strtotime($param['startTime']);
        }
        if (isset($param['endTime']) && !empty($param['endTime'])) {
            $sql .= ' AND iSeccTime <=' . strtotime($param['endTime']);
        }
        $sql .= ' ORDER BY iSeccTime DESC';
        $aData = $oDB->get_all($sql);
        return $aData;
    }

    /**
     * 扫描群发表，群发队列中消息
     */
    public function batchSendAction()
    {
        $aData = $this->getSendList();
        $aLog = array();
        if (!empty($aData)) {
            $iTime = time();
            $sCurrDate = date('Y-m-d');
            $iAllowableError = 3600;//容许误差
            $sToken = $this->getAccessToken();
            $aGroupList = Group::getGroupList($sToken);//获取分组
            if (!isset($aGroupList['groups']) || empty($aGroupList['groups'])) {
                return false;//无分组
            }
            $aTmp = array();
            foreach ($aGroupList['groups'] as $key => $value) {
                //重构数据结构
                $aTmp[$value['name']] = $value;
            }
            $aGroupList = $aTmp;
            $oDB = $this->getDB();
            foreach ($aData as $key => $value) {
                $sSendTime = self::$batchTime[$value['send_time']];//发送时间
                $sSendFullTime = $sCurrDate . ' ' . $sSendTime;//发送的完整时间
                if (date('j') == $value['send_date'] && abs(strtotime($sSendFullTime) - $iTime) <= $iAllowableError) {//判断发送时间是否为当前时间,允许一个小时误差
                    $sGroupName = date('Ym', strtotime('-' . $value['send_age'] . ' month'));//要发送的组名
                    //判断是否存在该分组
                    if (isset($aGroupList[$sGroupName])) {
                        $tmp['iSendID'] = $value['id'];
                        $tmp['iMediaID'] = $value['media_id'];
                        $tmp['iType'] = $value['type'];
                        $tmp['iSendAge'] = $value['send_age'];
                        $tmp['iSendDate'] = $value['send_date'];
                        $tmp['iSendTime'] = $sSendTime;
                        $tmp['iSeccTime'] = $iTime;
                        $tmp['iGroupID'] = $aGroupList[$sGroupName]['id'];
                        $tmp['iGroupName'] = $sGroupName;
                        //群发操作
                        $sAction = ($value["type"] != "vedio") ? $value["type"] . "Temp" : "mpvideoTemp";
                        $sTemp = Batchsend::$sAction($tmp['iGroupID'], $tmp['iMediaID']);
                        $aSendResult = Batchsend::batchSendByGroup($sToken, $sTemp);
                        //群发成功后写入log表
                        if (isset($aSendResult['errcode']) && $aSendResult['errcode'] == 0) {
                            $oDB->insert('batchsendlog', $tmp);
                            $aLog[] = $tmp;
                        }
                    }
                }
            }
        }
        print_r($aLog);
        die;
    }

    /**
     * 预览群发信息
     */
    public function PreviewAction()
    {
        $aOpenIDs[] = 'oKuqYjrEzNxwGJ23m8-GfU406bX0';//zfx
        //$aOpenIDs[] = 'oKuqYjjzqfTsxjluCF_EE1C1h3Uk';//东成
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getMediaByType($sToken, "news");//新闻媒体列表
        foreach ($aOpenIDs as $key => $value) {
            $aReturn[] = batchsend::batchSendPreview($sToken, $value, $aMediaList['item'][1]['media_id'], 'news');
        }
        print_r($aReturn);
    }

    /**
     * 获取用户信息
     */
    public function indexAction()
    {
        $sToken = $this->getAccessToken();
        //获取用户列表接口
        $oUser = new User();
        $aData = User::getUserList($sToken);
        $aUserInfo = array();
        foreach ($aData["data"]["openid"] as $key => $value) {
            $aUserInfo[] = User::getUserInfo($sToken, $value);
        }
        print_r($aUserInfo);
        die;
        $this->display("weixin/index.phtml");
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new batchsend_index();
?>
