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
    const NEWSUPLOADURL = "https://api.weixin.qq.com/cgi-bin/media/uploadnews";//图文素材接口
    const UPLOADURL = "http://file.api.weixin.qq.com/cgi-bin/media/upload";//上传素材接口
    const DOWNLOADURL = "http://file.api.weixin.qq.com/cgi-bin/media/get";//素材下载接口
    private $aMediaList = array();

    /**
     * 上传图文素材
     * @param $sToken
     * @param $aData
     * @return mixed
     */
    public function uploadNewsFile($sToken,$aData)
    {
        $aFile = json_encode(array("articles" => $aData));
        $sUploadUrl = self::NEWSUPLOADURL . "?access_token=" . $sToken;
        $sReturn = $this->curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        $sMedia_id = $aData["media_id"];
        //将数据存入数据库中
        $oDB = $this->getDB();
        $oDB->insert("media", $aData);
        return $sMedia_id;
    }

    /**
     * 上传文件
     * @param $sFileName
     * @param $sToken
     * @param $type
     * @return mixed
     */
    public function uploadFile($sFileName, $sToken, $type)
    {
        $aFile = array("media" => "@" . $sFileName);
        $sUploadUrl = self::UPLOADURL . "?access_token=" . $sToken . "&type=" . $type;
        $sReturn = $this->curl($sUploadUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        //更改所需media_id的key
        if ($type == "thumb") {
            $aData["media_id"] = $aData["thumb_media_id"];
            unset($aData["thumb_media_id"]);
        }
        $sMedia_id = $aData["media_id"];
        //将数据存入数据库中
        $oDB = $this->getDB();
        $oDB->insert("media", $aData);
        return $sMedia_id;
    }

    /**
     * 下载文件
     * @param $sMedia_id
     * @param $sToken
     * @return mixed
     */
    public function downloadfile($sMedia_id, $sToken)
    {
        $sDownloadUrl = self::DOWNLOADURL . "?access_token=" . $sToken . "&media_id=" . $sMedia_id;
        return $this->curl($sDownloadUrl, false);
    }

    /**
     * 获取媒体文件列表,以TYPE为Key
     */
    public function getMediaList()
    {
        if (empty($this->aMediaList)) {
            $created_at = time() - 3600 * 24 * 3;//三天过期时间
            $sSql = "SELECT * FROM media WHERE created_at >" . $created_at;
            $oDB = $this->getDB();
            $aData = $oDB->get_all($sSql);
            $tmp = array();
            foreach ($aData as $key => $value) {
                $tmp[$value["type"]][] = $value;
            }
            $this->aMediaList = $tmp;
        }
        return $this->aMediaList;
    }

    /**
     * 获取某类媒体文件列表
     * @param $sType
     * @return mixed
     */
    public function getMediaByType($sType)
    {
        if (!isset($this->aMediaList[$sType])) {
            $created_at = time() - 3600 * 24 * 3;//三天过期时间
            $sSql = "SELECT * FROM media WHERE created_at >" . $created_at . " AND type = '" . $sType . "'";
            $oDB = $this->getDB();
            $aData = $oDB->get_all($sSql);
            $this->aMediaList[$sType] = $aData;
        }
        return $this->aMediaList[$sType];
    }
}