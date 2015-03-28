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

    protected $tokenParamArr = array();
    protected $AccessToken = "";//这个应该存到memcache里面

    const APPID = "wx9b2a25e390a27d33";
    const APPSECRET = "84796fa0c79fd86b5087263cbbb27268";
    const TOKEN = "aaaabbbbccccdddd";
    const ACCESSTOKENURL = "https://api.weixin.qq.com/cgi-bin/token";
    const GETWEIXINIPURL = "https://api.weixin.qq.com/cgi-bin/getcallbackip";
    const RESTYPE = "text";//回复的类型

    public function __construct()
    {
        if ($this->checkSignature()) {
            if (isset($_GET['echostr'])) {
                //第一次配置的基础验证
                echo $_GET['echostr'];
                die;
            }
            parent::__construct();
        }
    }

    /**
     * @return bool
     * 验证配置信息是否正确
     */
    protected function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = self::TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取AccessToken
     * @return bool|string
     */
    protected function getAccessToken()
    {
        if (!$this->AccessToken) {
            $oMem = $this->getMem();
            $sAccesstoken = $oMem->get("weixin_AccessToken");
            if (!$sAccesstoken) {
                $tokenParamArr = $this->tokenParamArr;
                $tokenUrl = self::ACCESSTOKENURL;
                $tokenParamArr['appid'] = self::APPID;
                $tokenParamArr['secret'] = self::APPSECRET;
                $tokenParamArr['grant_type'] = 'client_credential';
                $tokenUrl .= "?" . http_build_query($tokenParamArr);
                $data = $this->curl($tokenUrl);
                $data = json_decode($data, true);
                $oMem->set('weixin_AccessToken', $data['access_token'], 7200);
                $sAccesstoken = $data['access_token'];
            }
            $this->AccessToken = $sAccesstoken;
        }

        return $this->AccessToken;
    }

    /**
     * 配置信息修改日志
     * @param $sData
     */
    protected function visitLog($sData)
    {
        $myfile = fopen("weixinVisitLog.txt", "a+") or die("Unable to open file!");
        fwrite($myfile, $sData);
        fclose($myfile);
    }

    /**
     * 获取微信服务器IP地址
     * @return mixed
     */
    protected function getWeixinIp()
    {
        $sUrl = self::GETWEIXINIPURL;
        $sAccessToken = $this->getAccessToken();
        $sUrl .= "?access_token=" . $sAccessToken;
        $sData = $this->curl($sUrl);

        return $sData;
    }

    /**
     * 接收微信消息
     * @return array
     */
    protected function receiveMsg()
    {
        //接收传送的数据
        $fileContent = file_get_contents("php://input");
        return $this->dealxml($fileContent);
    }
}