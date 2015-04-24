<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");
include_once("../model/Group.php");
include_once("../model/User.php");
include_once("../model/Batchsend.php");


class batchsend_index extends baseWeixin
{

    /**
     * 创建群发
     */
    public function initBatchAction()
    {
        $sFielename = $GLOBALS['FILEPATH'] . "12yuexiao.jpg";
        $sToken = $this->getAccessToken();
        //上传图文消息
        $aMediaIds = Media::getPermanentNum('thumb');
        $aNews = Media::initNewsTmp($aMediaIds);
        //调用上传news接口
        $aNews = Media::uploadNewsFile($sToken,$aNews);
        //调用分组接口,判断该news用于哪个分组
        $oGroup = new Group();
        $aGroupList = $oGroup->getGroupList($sToken);
        //这里现在是想分到100和101分组
        $aSendList = array(100,101);//群发的分组
        $iSendTime = time();//群发的时间
        $aNews["group_id"] = implode(",",$aSendList);
        $aNews["send_time"] = $iSendTime;
        //将数据存入数据库中
        $oDB = $this->getDB();
        $oDB->insert("batchsend", $aNews);

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
        foreach ($abatchSendList as $key=>$value) {
            //生成群发模版
            $sAction = ($value["type"] != "vedio") ? $value["type"]."Temp" : "mpvideoTemp";
            $aGroupID = explode(",",$value["group_id"]);
            foreach ($aGroupID as $k => $v) {
                $sTemp = Batchsend::$sAction($v,$value["media_id"]);
                $sReturn[] = Batchsend::patchSendByGroup($sToken,$sTemp);
            }
        }
        print_r($sReturn);
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
