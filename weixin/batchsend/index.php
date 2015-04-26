<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");
include_once("../model/Group.php");
include_once("../model/User.php");
include_once("../model/Batchsend.php");


class batchsend_index extends baseWeixin
{

    private static $batchTime = array('00:00:00','12:00:00','18:00:00','22:00:00');//群发时间点，定死的

    /**
     * 这个专门用来做测试用的
     */
    public function testAction()
    {
        $sToken = $this->getAccessToken();
        //$aMediaNumList = Media::getPermanentNum($sToken);//媒体文件数目列表
        $aMediaList = Media::getMediaByType($sToken,"news");//新闻媒体列表
        $aGroupList = Group::getGroupList($sToken);
        $this->assign('aGroupList',$aGroupList);
        $this->assign('aMediaList',$aMediaList);
        $this->assign('batchTime',self::$batchTime);
        $this->display("/weixin/index.phtml");
        die;
    }

    /**
     * 创建群发
     */
    public function initBatchAction()
    {
        $sToken = $this->getAccessToken();
        if (empty($_POST['group_id']) || $_POST['group_id'] == 10000) {
            showError("请选择分组",true);
        }
        if (empty($_POST['media_id'])) {
            showError("请选择图文模版",true);
        }
        if (empty($_POST['batchDate'])) {
            showError("请选择群发日期",true);
        }
        if (empty($_POST['batchTime'])) {
            showError("请选择群发时间",true);
        }
        $iSendTime = $_POST['batchDate'].' '.self::$batchTime[$_POST['batchTime']-1];//群发的时间
        $aNews['created_at'] = time();
        $aNews['type'] = 'news';
        $aNews['media_id'] = $_POST['media_id'];
        $aNews["group_id"] = $_POST['group_id'];
        $aNews["send_time"] = strtotime($iSendTime);
        //将数据存入数据库中
        $oDB = $this->getDB();
        $oDB->insert("batchsend", $aNews);
        showOk('创建定时群发成功','?action=test');

        /**
        //调用上传缩略图接口
        $sMedia_id = $oMedia->uploadFile($sFielename, $sToken, "thumb");
         */
    }

    /**
     * 扫描群发表，群发队列中消息
     */
    public function batchSendAction()
    {
        $sToken = $this->getAccessToken();
        $abatchSendList = Batchsend::batchSendList();
        $sReturn = array();
        if (!empty($abatchSendList)) {
            foreach ($abatchSendList as $key=>$value) {
                //生成群发模版
                $sAction = ($value["type"] != "vedio") ? $value["type"]."Temp" : "mpvideoTemp";
                $aGroupID = explode(",",$value["group_id"]);
                foreach ($aGroupID as $k => $v) {
                    $sTemp = Batchsend::$sAction($v,$value["media_id"]);
                    $sReturn[] = Batchsend::batchSendByGroup($sToken,$sTemp);
                }
            }
        }

        print_r($sReturn);
    }

    /**
     * 预览群发信息
     */
    public function PreviewAction()
    {
        $aOpenIDs[] = 'oKuqYjrEzNxwGJ23m8-GfU406bX0';//zfx
        $aOpenIDs[] = 'oKuqYjjzqfTsxjluCF_EE1C1h3Uk';//东成
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getMediaByType($sToken,"news");//新闻媒体列表
        foreach($aOpenIDs as $key => $value) {
            batchsend::batchSendPreview($sToken,$value,$aMediaList['item'][1]['media_id'],'news');
        }
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
            $aUserInfo[] = User::getUserInfo($sToken,$value);
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