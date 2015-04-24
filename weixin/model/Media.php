<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/28
 * Time: 下午7:56
 */
include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class Media extends ModelBase
{
    const NEWSUPLOADURL = "https://api.weixin.qq.com/cgi-bin/media/uploadnews";//临时图文素材接口
    const UPLOADURL = "http://file.api.weixin.qq.com/cgi-bin/media/upload";//上传临时素材接口
    const DOWNLOADURL = "http://file.api.weixin.qq.com/cgi-bin/media/get";//临时素材下载接口，视频文件不支持https下载，调用该接口需http协议
    const PERMANENTNEWS = "https://api.weixin.qq.com/cgi-bin/material/add_news";//新增永久图文素材接口
    const PERMANENTORTHER = "http://api.weixin.qq.com/cgi-bin/material/add_material";//新增其他类型永久素材
    const DOWNPERMANENT = "https://api.weixin.qq.com/cgi-bin/material/get_material";//获取永久素材
    const DELETEPERMANENT = "https://api.weixin.qq.com/cgi-bin/material/del_material";//删除永久素材
    const UPDATEPERMANENTNEWS = "https://api.weixin.qq.com/cgi-bin/material/update_news";//修改永久素材（这个暂时不弄，有需求再说）
    const PERMANENTNUM = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount";//永久素材总数
    const PERMANENTLIST = "https://api.weixin.qq.com/cgi-bin/material/batchget_material";//获取相应类型的永久素材列表
    private $aMediaList = array();

    /**
     * 上传临时图文素材
     * @param $sToken
     * @param $aData
     * @return mixed
     */
    public static function uploadNewsFile($sToken,$aData)
    {
        //data格式
        /**
        $news[] = array(
            "thumb_media_id" => $sMedia_id,
            "author" => "zfx1",
            "title" => "test1",
            "content_source_url" => "http://www.baidu.com1",
            "content" => "111111",
            "digest" => "232323",
            "show_cover_pic" => "1"

        );
         */
        $aFile = json_encode(array("articles" => $aData));
        $sUploadUrl = self::NEWSUPLOADURL . "?access_token=" . $sToken;
        $sReturn = self::curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 上传临时文件
     * @param $sFileName
     * @param $sToken
     * @param $type
     * @return mixed
     */
    public static function uploadFile($sFileName, $sToken, $type)
    {
        $aFile = array("media" => "@" . $sFileName);
        $sUploadUrl = self::UPLOADURL . "?access_token=" . $sToken . "&type=" . $type;
        $sReturn = self::curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 下载临时文件
     * @param $sMedia_id
     * @param $sToken
     * @return mixed
     */
    public static function downloadfile($sMedia_id, $sToken)
    {
        $sDownloadUrl = self::DOWNLOADURL . "?access_token=" . $sToken . "&media_id=" . $sMedia_id;
        return self::curl($sDownloadUrl);
    }

    /**
     * 获取某类媒体文件列表
     * @param $sType
     * @param int $offset
     * @param int $count
     * @return mixed
     */
    public static function getMediaByType($sToken,$sType,$offset=0,$count=20)
    {
        if (!isset(self::$aMediaList[$sType])) {
            $aData = array(
                "type" => $sType,
                "offset" => $offset,
                "count" => $count
            );
            $sPermanentUrl = self::PERMANENTLIST . "?access_token=" . $sToken ;
            $sReturn = self::curl($sPermanentUrl, true, $aData);
            $aData = json_decode($sReturn, true);
            self::$aMediaList[$sType] = $aData;
        }
        return self::$aMediaList[$sType];
    }

    /**
     * 新建新闻模版(暂时搞个最简单的生成，临时)
     * @param $aThumb_media_ids(根据传进来的媒体缩略图数目创建模版)
     */
    public static function initNewsTmp($aThumb_media_ids = array())
    {
        $news = array();
        if (empty($aThumb_media_ids)) {
            showError("模版创建失败",true);
        }
        foreach ($aThumb_media_ids as $key => $value) {
            $news[] = array(
                "thumb_media_id" => $value["media_id"],
                "author" => "zfx".$key,
                "title" => "test".$key,
                "content_source_url" => "http://www.baidu.com",
                "content" => "这里是内容，后期可自行编辑，或直接制作成html模版".$key,
                "digest" => "这里是描述".$key,
                "show_cover_pic" => "1"

            );
        }
        return $news;
    }

    /**
     * 新建永久新闻素材
     * @param $aData
     */
    public static function initPermanentNews($sToken,$aData)
    {
        /**data格式***/
        /**
        $aData = array(
            "articles" => array(
                array(
                    "title" => $title,
                    "thumb_media_id" => $thumb_media_id,//必须是永久mediaID
                    "author" => $author,
                    "digest" => $digest,//仅有单图文消息才有摘要，多图文此处为空
                    "show_cover_pic" => $show_cover_pic,//0不显示1显示
                    "content" => $content,
                    "content_source_url" => $content_source_url//即点击“阅读原文”后的URL
                ),
                array(
                    "title" => $title,
                    "thumb_media_id" => $thumb_media_id,
                    "author" => $author,
                    "digest" => $digest,
                    "show_cover_pic" => $show_cover_pic,//0不显示1显示
                    "content" => $content,
                    "content_source_url" => $content_source_url
                )
            )
        );
         */
        if (count($aData["articles"]) > 9) {
            showError("微信最多支持9个图文消息",true);
        }
        $aFile = json_encode($aData);
        $sUploadUrl = self::PERMANENTNEWS . "?access_token=" . $sToken;
        $sReturn = self::curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
    }

    /**
     * 上传永久文件
     * @param $sFileName
     * @param $sToken
     * @param $type
     * @return mixed
     */
    public static function uploadPermanentFile($sFileName, $sToken, $type)
    {
        $aFile = array("media" => "@" . $sFileName);
        $sUploadUrl = self::PERMANENTORTHER . "?access_token=" . $sToken . "&type=" . $type;
        $sReturn = self::curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 下载永久文件
     * @param $sMedia_id
     * @param $sToken
     * @return mixed
     */
    public static function downloadPermanentfile($sMedia_id, $sToken)
    {
        $sDownloadUrl = self::DOWNPERMANENT . "?access_token=" . $sToken . "&media_id=" . $sMedia_id;
        return self::curl($sDownloadUrl, true,array("media_id"=>$sMedia_id));
    }

    /**
     * 删除永久文件
     * @param $sMedia_id
     * @param $sToken
     * @return mixed
     */
    public static function deletePermanentfile($sMedia_id, $sToken)
    {
        $sDownloadUrl = self::DELETEPERMANENT . "?access_token=" . $sToken . "&media_id=" . $sMedia_id;
        return self::curl($sDownloadUrl, true,array("media_id"=>$sMedia_id));
    }

    /**
     * 永久素材总数
     * @param $sToken
     * @return mixed
     */
    public static function getPermanentNum($sToken)
    {
        $sDownloadUrl = self::PERMANENTNUM . "?access_token=" . $sToken;
        return self::curl($sDownloadUrl);
    }
}