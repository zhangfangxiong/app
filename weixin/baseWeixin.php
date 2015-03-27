<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/8
 * Time: 下午3:20
 */
include_once("../lib/DB.php");
include_once("../lib/memcache.php");
include_once("../lib/function.php");
include_once("../lib/base.php");
include_once("../config/DBconf_weixin.php");
include_once("../config/globalConfig.php");

class baseWeixin extends base
{
    const APPID = "wx9b2a25e390a27d33";
    const APPSECRET = "84796fa0c79fd86b5087263cbbb27268";
    const TOKEN = "aaaabbbbccccdddd";
    const ACCESSTOKENURL = "https://api.weixin.qq.com/cgi-bin/token";
    const GETWEIXINIPURL = "https://api.weixin.qq.com/cgi-bin/getcallbackip";
    const RESTYPE = "text";//回复的类型
}