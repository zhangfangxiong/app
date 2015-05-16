<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/30
 * Time: 下午8:57
 */


include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class Batchsend extends ModelBase
{
    const GROUPSEND = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall";//分组群发接口
    const OPENIDSEND = "https://api.weixin.qq.com/cgi-bin/message/mass/send";//OpenID列表群发接口
    const PRIVIEW = "https://api.weixin.qq.com/cgi-bin/message/mass/preview";//预览接口
    private static $batchSendList = array();//群发列表，群发表中取得

    /**
     * 按分组群发
     * @param $sToken
     * @param $sData 生成群发模版
     * @return mixed
     */
    public static function batchSendByGroup($sToken,$sData)
    {
        $sGroupSendUrl = self::GROUPSEND . "?access_token=" . $sToken;
        $sReturn = self::curl($sGroupSendUrl,true,$sData);
        return json_decode($sReturn,true);
    }

    /**
     * 按openID群发
     * @param $sToken
     * @param $sData 生成群发模版
     * @return mixed
     */
    public static function batchSendByOpenIDs($sToken,$sData)
    {
        $sGroupSendUrl = self::OPENIDSEND . "?access_token=" . $sToken;
        $sReturn = self::curl($sGroupSendUrl,true,$sData);
        return $sReturn;
    }

    /**
     * 群发预览
     * @param $sToken
     * @param $sData 生成群发模版
     * @return mixed
     */
    public static function batchSendPreview($sToken,$sOpenID,$media_id,$sType)
    {
        $sAction = $sType."Preview";
        $sData = self::$sAction($sOpenID,$media_id);
        $sGroupSendUrl = self::OPENIDSEND . "?access_token=" . $sToken;
        $sReturn = self::curl($sGroupSendUrl,true,$sData);
        return json_decode($sReturn,true);
    }

    /**
     * 群发列表
     * @iTime 发送时间
     * @return array
     */
    public static function batchSendList($iTime)
    {
        if (empty(self::$batchSendList)) {
            //设定一个时间区间，以防延迟
            $iStartTime = $iTime - 3600;
            $iEndTime = $iTime + 3600;
            $sSql = "SELECT * FROM batchsend WHERE has_send =0 AND send_time >=".$iStartTime." AND send_time <=".$iEndTime;
            $oDB = self::getDB();
            $aData = $oDB->get_all($sSql);
            self::$batchSendList = $aData;
        }
        return self::$batchSendList;
    }

    /**
     * 新闻群发模版
     * @param $group_id
     * @param $media_id
     * @param bool $is_to_all
     * @return string
     */
    public static function newsTemp($group_id,$media_id,$is_to_all=false)
    {
        $aData =  array(
            "filter" => array(
                "is_to_all" =>$is_to_all,
                "group_id" => $group_id
            ),
            "mpnews" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "mpnews"
        );
        return json_encode($aData);
    }

    /**
     * 文本群发模版
     * @param $group_id
     * @param $sContent
     * @param bool $is_to_all
     * @return string
     */
    public static function textTemp($group_id,$sContent,$is_to_all=false)
    {
        $aData =  array(
            "filter" => array(
                "is_to_all" =>$is_to_all,
                "group_id" => $group_id
            ),
            "text" => array(
                "content" => $sContent
            ),
            "msgtype" => "text"
        );
        return json_encode($aData);
    }

    /**
     * 语音群发模版
     * @param $group_id
     * @param $media_id
     * @param bool $is_to_all
     * @return string
     */
    public static function voiceTemp($group_id,$media_id,$is_to_all=false)
    {
        $aData =  array(
            "filter" => array(
                "is_to_all" =>$is_to_all,
                "group_id" => $group_id
            ),
            "voice" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "voice"
        );
        return json_encode($aData);
    }

    /**
     * 图片群发模版
     * @param $group_id
     * @param $media_id
     * @param bool $is_to_all
     * @return string
     */
    public static function imageTemp($group_id,$media_id,$is_to_all=false)
    {
        $aData =  array(
            "filter" => array(
                "is_to_all" =>$is_to_all,
                "group_id" => $group_id
            ),
            "image" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "image"
        );
        return json_encode($aData);
    }

    /**
     * 视频群发模版
     * @param $group_id
     * @param $media_id
     * @param bool $is_to_all
     * @return string
     */
    public static function mpvideoTemp($group_id,$media_id,$is_to_all=false)
    {
        $aData =  array(
            "filter" => array(
                "is_to_all" =>$is_to_all,
                "group_id" => $group_id
            ),
            "mpvideo" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "mpvideo"
        );
        return json_encode($aData);
    }

    /**
     * 图文消息预览
     * @param $sOpenID
     * @param $media_id
     */
    public static function newsPreview($sOpenID,$media_id)
    {
        $aData = array(
            "touser" => $sOpenID,
            "mpnews" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "mpnews"
        );
        return json_encode($aData);
    }

    /**
     * 图文消息预览
     * @param $sOpenID
     * @param $sContent
     */
    public static function textPreview($sOpenID,$sContent)
    {
        $aData = array(
            "touser" => $sOpenID,
            "text" => array(
                "content" => $sContent
            ),
            "msgtype" => "text"
        );
        return json_encode($aData);
    }

    /**
     * 音频消息预览
     * @param $sOpenID
     * @param $media_id
     */
    public static function voicePreview($sOpenID,$media_id)
    {
        $aData = array(
            "touser" => $sOpenID,
            "voice" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "voice"
        );
        return json_encode($aData);
    }

    /**
     * 图片消息预览
     * @param $sOpenID
     * @param $media_id
     */
    public static function imagePreview($sOpenID,$media_id)
    {
        $aData = array(
            "touser" => $sOpenID,
            "image" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "image"
        );
        return json_encode($aData);
    }

    /**
     * 图片消息预览
     * @param $sOpenID
     * @param $media_id
     */
    public static function mpvideoPreview($sOpenID,$media_id)
    {
        $aData = array(
            "touser" => $sOpenID,
            "mpvideo" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "mpvideo"
        );
        return json_encode($aData);
    }

    /**
     * OpenID列表群发图文
     * @param $aOpenIDs
     * @param $media_id
     */
    public static function newsSendByOpenID($aOpenIDs,$media_id)
    {
        $aData = array(
            "touser" => $aOpenIDs,
            "mpnews" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "mpnews"
        );
        return json_encode($aData);
    }

    /**
     * OpenID列表群发text
     * @param $aOpenIDs
     * @param $media_id
     */
    public static function textSendByOpenID($aOpenIDs,$content)
    {
        $aData = array(
            "touser" => $aOpenIDs,
            "text" => array(
                "content" => $content
            ),
            "msgtype" => "text"
        );
        return json_encode($aData);
    }

    /**
     * OpenID列表群发语音
     * @param $aOpenIDs
     * @param $media_id
     */
    public static function voiceSendByOpenID($aOpenIDs,$media_id)
    {
        $aData = array(
            "touser" => $aOpenIDs,
            "voice" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "voice"
        );
        return json_encode($aData);
    }

    /**
     * OpenID列表群发图片
     * @param $aOpenIDs
     * @param $media_id
     */
    public static function imageSendByOpenID($aOpenIDs,$media_id)
    {
        $aData = array(
            "touser" => $aOpenIDs,
            "image" => array(
                "media_id" => $media_id
            ),
            "msgtype" => "image"
        );
        return json_encode($aData);
    }

    /**
     * OpenID列表群发视频
     * @param $aOpenIDs
     * @param $media_id
     * @param $title
     * @param $description
     * @return string
     */
    public static function videoSendByOpenID($aOpenIDs,$media_id,$title,$description)
    {
        $aData = array(
            "touser" => $aOpenIDs,
            "video" => array(
                "media_id" => $media_id,
                "title" => $title,
                "description" => $description
            ),
            "msgtype" => "video"
        );
        return json_encode($aData);
    }
}