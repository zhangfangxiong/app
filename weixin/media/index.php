<?php
include_once("../baseWeixin.php");
include_once("../model/Media.php");
include_once("../model/Group.php");
include_once("../model/User.php");
include_once("../model/Batchsend.php");


class batchsend_index extends baseWeixin
{
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
    }

    /**
     * 修改新闻素材
     */
    public function updateNewsAction()
    {
        if ($this->isPost()) {
            if (!isset($_POST['media_id']) || !isset($_GET['mediaID']) || !isset($_POST['index']) || !isset($_GET['index'])) {
                showError('非法操作1',true);
            }
            if ($_POST['media_id']!= $_GET['mediaID'] || $_GET['index'] !== $_POST['index']) {
                showError('非法操作',true);
            }
            //整理成需要的数据结构
            $aData = array(
                'media_id' => $_POST['media_id'],
                'index' => $_POST['index'],
                'articles' => array(
                    'title' => urlencode($_POST['title']),//中文加码
                    'thumb_media_id' => $_POST['thumb_media_id'],
                    'author' => urlencode($_POST['author']),
                    'digest' => urlencode($_POST['digest']),
                    'show_cover_pic' => $_POST['show_cover_pic'],
                    'content' => urlencode($_POST['editorValue']),
                    'content_source_url' => $_POST['content_source_url'],
                )
            );
            $sToken = $this->getAccessToken();
            $aData = Media::updateNewsFile($sToken,$aData);
            if ($aData['errcode'] == 0) {
                showOk('修改成功','?action=newsList');
            }
        } else {
            if (empty($_GET['mediaID']) || !isset($_GET['index']) ) {
                showError("非法操作",true);
            }
            $mediaID = $_GET['mediaID'];
            $index = intval($_GET['index']);
            $sToken = $this->getAccessToken();
            $aMediaList = Media::getMediaByType($sToken,"news");//新闻媒体列表
            if (empty($aMediaList['item'])) {
                showError('媒体ID有误',true);
            }
            $aData = array();
            foreach ($aMediaList['item'] as $key => $value) {
                if (($value['media_id'] == $mediaID ) && isset($value['content']['news_item'][$index])) {
                    $aData['media_id'] = $mediaID;
                    $aData['index'] = $index;
                    $aData['content'] = $value['content']['news_item'][$index];
                    break;
                }
            }
            if (empty($aData)) {
                showError('要修改的媒体ID不存在',true);
            }
            $this->assign('aData',$aData);
            $this->display("/weixin/updatenews.phtml");
        }
    }

    /**
     * 新闻素材列表
     */
    public function newsListAction()
    {
        $sToken = $this->getAccessToken();
        $aMediaList = Media::getMediaByType($sToken,"news");//新闻媒体列表
        $this->assign('aMediaList',$aMediaList);
        $this->display("/weixin/newslist.phtml");
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new batchsend_index();
?>
