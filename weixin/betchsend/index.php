<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");

class betchsend_index extends baseWeixin
{
    public function indexAction()
    {
        $sFielename = $GLOBALS['FILEPATH']."m_happy.jpg";
        $sToken = $this->getAccessToken();
        $oMedia = new Media();
        //调用上传接口
        $sReturn = $oMedia->uploadFile($sFielename,$sToken,"thumb");

        //$sReturn = $aUpload->downloadfile($sMedia_id,$sToken);
        //调用下载接口
        $this->display("weixin/index.phtml");
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new betchsend_index();
?>
