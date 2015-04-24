<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/30
 * Time: 上午12:24
 */

include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class User extends ModelBase
{
    const GETUSERLIST = "https://api.weixin.qq.com/cgi-bin/user/get";//获取当前关注着列表
    const USERINFO = "https://api.weixin.qq.com/cgi-bin/user/info";

    /**
     * 关注者列表
     * @param $sToken
     * @return mixed
     */
    public static function getUserList($sToken)
    {
        $sGetUserList = self::GETUSERLIST . "?access_token=" . $sToken;
        $sReturn = self::curl($sGetUserList);
        return json_decode($sReturn,true);
    }

    public static function getUserInfo($sToken,$sOpenID,$lang="zh_CN")
    {
        $sGetUserInfo = self::USERINFO . "?access_token=" . $sToken."&openid=".$sOpenID."&lang=".$lang;
        $sReturn = self::curl($sGetUserInfo);
        return json_decode($sReturn,true);
    }
}