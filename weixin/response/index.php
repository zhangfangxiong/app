<?php
include_once("baseWeixin.php");

class response_index extends baseWeixin
{
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


}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new response_index();
?>
