<?php
include_once("baseWeixin.php");

class weixin extends baseWeixin
{
    private $tokenParamArr = array();
    private $AccessToken = "";//这个应该存到memcache里面

    public function __construct()
    {
        //if ($this->checkSignature()) {
            if (isset($_GET['echostr'])) {
                //第一次配置的基础验证
                echo $_GET['echostr'];
                die;
            }
            if (isset($_GET['action']) && $_GET['action']) {
                $action = $_GET['action'] . "Action";
            } else {
                $action = "receiveMsgAction";
            }
            $this->$action();
        //}
    }

    /**
     * @return bool
     * 验证配置信息是否正确
     */
    private function checkSignature()
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
     * 配置信息修改日志
     */
    private function visitLog($sData)
    {
        $myfile = fopen("weixinVisitLog.txt", "a+") or die("Unable to open file!");
        fwrite($myfile, $sData);
        fclose($myfile);
    }

    /**
     * 获取AccessToken
     */
    private function getAccessToken()
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
                $oMem->set('weixin_AccessToken',$data['access_token'],7200);
                $sAccesstoken = $data['access_token'];
            }
            $this->AccessToken = $sAccesstoken;
        }

        return $this->AccessToken;
    }

    /**
     * 获取微信服务器IP地址
     */
    public function getWeixinIpAction()
    {
        $sUrl = self::GETWEIXINIPURL;
        $sAccessToken = $this->getAccessToken();
        $sUrl .= "?access_token=" . $sAccessToken;
        $sData = $this->curl($sUrl);

        return $sData;
    }

    private function _getData() {
        //接收传送的数据
        $fileContent = file_get_contents("php://input");
/**
        $fileContent = "<xml><ToUserName><![CDATA[gh_204a5106b4bc]]></ToUserName>
<FromUserName><![CDATA[oKZ1es2lJSz0mzack9Qw8u9hJUR4]]></FromUserName>
<CreateTime>1426419849</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[天天]]></Content>
<MsgId>6126426602024085587</MsgId>
</xml>";
 */

        //转换为simplexml对象
        $xmlResult = simplexml_load_string($fileContent, null, LIBXML_NOCDATA);
        //foreach循环遍历
        $aNode = array();
        foreach ($xmlResult->children() as $childItem) {
            //输出xml节点名称和值
            $item_arr = (array)$childItem;
            $aNode[$childItem->getName()] = $item_arr[0];
        }
        return $aNode;
    }
    /**
     * 接收消息
     */
    public function receiveMsgAction()
    {
        $aNode = $this->_getData();
        if ($aNode) {
            if (isset($aNode["URL"])) {
                unset($aNode["URL"]);
            }
            //在这里就需要根据节点不同，做不同的处理了
            $this->responseMsg($aNode);
            /**
            exit;
            $db = new DB();
            //消息存入数据库
            $result = $db->insert("message", $aNode);
             */
        }
    }

    /**
     * 回复消息
     */
    public function responseMsg($aData)
    {
        if (!empty($aData)) {
            $xmlData = $this->getMsgXML($aData);
            $this->visitLog($xmlData);
            echo $xmlData;
        } else {
            echo "";
            exit;
        }
    }

    private function transferService($aData) {
        include_once("resformat.php");
        $res_msg_type = "transferServiceformat";
        $msgTpl = $res_msg_type();
        $aParam[] = $aData['ToUserName'];
        $aParam[] = $aData['FromUserName'];
        $aParam[] = time();
        $aParam[] = "transfer_customer_service";
        $resultStr = vsprintf($msgTpl,$aParam);
        return $resultStr;
    }

    private function responseText($aData) {
        include_once("resformat.php");
        $res_msg_type = "textFormat";
        $msgTpl = $res_msg_type();
        $aParam[] = $aData['ToUserName'];
        $aParam[] = $aData['FromUserName'];
        $aParam[] = time();
        $aParam[] = "text";
        $aParam[] = "dsdsdssd";
        $resultStr = vsprintf($msgTpl,$aParam);
        //$resultStr = sprintf($msgTpl,$aData['ToUserName'],$aData['FromUserName'],time(),'text','dsdsssd');
        return $resultStr;
    }

    /**
     * 生成返回的XML内容
     * @param $msgTpl 消息模板
     * @param $sData 消息数据
     */
    private function getMsgXML($aData)
    {
        $res_type = self::RESTYPE;
        switch ($res_type) {
            case "transferService":
                $resultStr = $this->transferService($aData);
                break;
            case "text":
                $resultStr = $this->responseText($aData);
               break;
            //default:
                //echo 11114;
        }
        return $resultStr;
    }

    /**
     * @param $url
     * 访问url
     */
    private function curl($sUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);

        return $content;
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
$index = new weixin();
/*
$content = simplexml_load_string('<content><![CDATA[Hello, world!]]></content>');
echo (string) $content;

$foo = simplexml_load_string('<foo><content><![CDATA[Hello, world!]]></content></foo>');
echo (string) $foo->content;

// 通过下面的方法自动过滤 CDATA 内部参数
$content = simplexml_load_string('<content><![CDATA[Hello, world!]]></content>', null, LIBXML_NOCDATA);
*/
?>
