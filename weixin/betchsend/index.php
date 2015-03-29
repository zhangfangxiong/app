<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");
include_once("../model/Group.php");
include_once("../model/User.php");


class batchsend_index extends baseWeixin
{
    public function indexAction()
    {
        $sFielename = $GLOBALS['FILEPATH'] . "m_happy.jpg";
        $sToken = $this->getAccessToken();

        //上传图文消息
        $oMedia = new Media();
        $aThumb = $oMedia->getMediaByType("thumb");
        if (empty($aThumb)) {
            //调用上传接口
            $sMedia_id = $oMedia->uploadFile($sFielename, $sToken, "thumb");
        } else {
            //测试阶段，先选取第一张有效缩略图
            $sMedia_id = $aThumb[0]['media_id'];
        }
        //这里到时候从数据库的模版中取
        $news[] = array(
            "thumb_media_id" => $sMedia_id,
            "author" => "zfx",
            "title" => "test",
            "content_source_url" => "http://www.baidu.com",
            "content" => "111111",
            "digest" => "232323",
            "show_cover_pic" => "1"

        );
        $news[] = array(
            "thumb_media_id" => $sMedia_id,
            "author" => "zfx1",
            "title" => "test1",
            "content_source_url" => "http://www.baidu.com1",
            "content" => "111111",
            "digest" => "232323",
            "show_cover_pic" => "1"

        );
        //$sMedia_id = $oMedia->uploadNewsFile($sToken,$news);
        $oUser = new User();
        $aData = $oUser->getUserList($sToken);
        $iOpenID = $aData['data']["openid"][0];
        $oGroup = new Group();
        $iData = $oGroup->updateUserGroup($sToken,$iOpenID,118);
        print_r($iData);
        die;


        $aData = $oGroup->getGroupList($sToken);
        $iGroupID = $aData["groups"][4]["id"];
        $aArr = array("group"=>array("id"=>$iGroupID,"name"=>"test6666666"));
        $aData = $oGroup->updateGroupName($sToken,$aArr);
        print_r($aData);
        die;

        //$sReturn = $aUpload->downloadfile($sMedia_id,$sToken);
        //调用下载接口
        $this->display("weixin/index.phtml");
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new batchsend_index();
?>
