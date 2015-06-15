<?php
/**
 * 这是正式环境微信基类，不能用测试环境基类直接覆盖
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/8
 * Time: 下午3:20
 */

include_once(dirname(dirname(__FILE__))."/lib/DB.php");
include_once(dirname(dirname(__FILE__))."/lib/memcache.php");
include_once(dirname(dirname(__FILE__))."/lib/function.php");
include_once(dirname(dirname(__FILE__))."/lib/base.php");
include_once(dirname(dirname(__FILE__))."/config/globalConfig.php");

class baseWeixin extends base
{
    protected $tokenParamArr = array();
    protected $AccessToken = "";//这个应该存到memcache里面

    const APPID = "wx10af5b99e80b760d";
    const APPSECRET = "f75479d51b17f8305a31abc11276fbeb";
    const TOKEN = "qidishu";
    const ACCESSTOKENURL = "https://api.weixin.qq.com/cgi-bin/token";//获取TOKEN接口
    const GETWEIXINIPURL = "https://api.weixin.qq.com/cgi-bin/getcallbackip";//获取IP接口
    const RESTYPE = "text";//回复的类型
    const ACCESSTOKENKEY = 'qidishu';//用户区分测试环境和正式环境存在memcache的accesstoken

    public function __construct()
    {
        //if ($this->checkSignature()) {
            if (isset($_GET['echostr'])) {
                //第一次配置的基础验证
                echo $_GET['echostr'];
                die;
            }
            parent::__construct();
        //}
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
            $sTokenKey = self::ACCESSTOKENKEY."_weixin_AccessToken";
            $oMem = $this->getMem();
            $sAccesstoken = $oMem->get($sTokenKey);
            if (!$sAccesstoken) {
                $tokenParamArr = $this->tokenParamArr;
                $tokenUrl = self::ACCESSTOKENURL;
                $tokenParamArr['appid'] = self::APPID;
                $tokenParamArr['secret'] = self::APPSECRET;
                $tokenParamArr['grant_type'] = 'client_credential';
                $tokenUrl .= "?" . http_build_query($tokenParamArr);
                $data = $this->curl($tokenUrl,false);
                $data = json_decode($data, true);
                $oMem->set($sTokenKey, $data['access_token'], 7200);
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
        $sData = $this->curl($sUrl,false);

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