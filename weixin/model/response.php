<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/4/2
 * Time: 上午12:14
 */
include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class response extends ModelBase
{
    private $aResponseTemp = array();

    /**
     * 获取所有回复模版数组
     * @return array|mixed
     */
    private static function getReponseTemp()
    {
        if (!self::$aResponseTemp) {
            self::$aResponseTemp = include('../config/responseTemp.php');
        }
        return self::$aResponseTemp;
    }

    /**
     * 根据种类获取回复模版
     * @param $sType
     * @return null
     */
    private static function getReponseTempByType($sType)
    {
        $aResponsetemp = self::getReponseTemp();
        if (isset($aResponsetemp[$sType])) {
            return $aResponsetemp[$sType];
        }
        return null;
    }


    /**
     * 创建text数据
     * @param $aReponseData要回复的数据
     * @param $aReceiveData接收的数据
     */
    private static function initDataTxt(&$aReponseData,$aReceiveData)
    {
        //还差content数据
        //可以根据接收的内容或者不同的事件回复不同的信息
        if (isset($aReceiveData['Event'])) {//事件类型
            if ($aReceiveData['Event']=='subscribe') {//订阅
                $aReponseData['Content'] = '欢迎订阅';
            } elseif ($aReceiveData['Event']=='subscribe') {//取消订阅
                $aReponseData['Content'] = '取消订阅成功，谢谢使用';
            } elseif ($aReceiveData['Event']=='SCAN') { //关注后的扫描
                $aReponseData['Content'] = '扫描成功，谢谢使用';
            } elseif ($aReceiveData['Event']=='LOCATION') { //用户上报地理位置推送
                $aReponseData['Content'] = '您当前纬度：'.$aReceiveData['Latitude'].'\n\r';
                $aReponseData['Content'] .= '您当前经度：'.$aReceiveData['Longitude'].'\n\r';
                $aReponseData['Content'] .= '您当前精度：'.$aReceiveData['Precision'].'\n\r';
            } elseif ($aReceiveData['Event']=='CLICK') { //点击菜单拉取消息时的事件推送
                $aReponseData['Content'] = '您点击了第'.$aReceiveData['EventKey'].'个菜单';
            } elseif ($aReceiveData['Event']=='VIEW') { //点击菜单跳转链接时的事件推送
                $aReponseData['Content'] = '您将要跳转到的链接地址是'.$aReceiveData['EventKey'];
            }
        } elseif ($aReceiveData['MsgType'] == 'text') {//文本类型
            $aReponseData['Content'] = '你发送的内容是'.$aReceiveData['Content'];
        } elseif ($aReceiveData['MsgType'] == 'image') {//图片类型
            $aReponseData['Content'] = '你发送的图片ID是'.$aReceiveData[' MediaId'];
        } elseif ($aReceiveData['MsgType'] == 'voice') {//语音类型
            $aReponseData['Content'] = '你发送的语音ID是'.$aReceiveData[' MediaId'];
        } elseif ($aReceiveData['MsgType'] == 'video') {//视频类型
            $aReponseData['Content'] = '你发送的视频媒体ID是'.$aReceiveData[' MediaId'].'\n\r';
            $aReponseData['Content'] .= '缩略图的媒体ID是'.$aReceiveData[' ThumbMediaId'];
        } elseif ($aReceiveData['MsgType'] == 'shortvideo') {//小视频类型
            $aReponseData['Content'] = '你发送的小视频媒体ID是'.$aReceiveData[' MediaId'].'\n\r';
            $aReponseData['Content'] .= '缩略图的媒体ID是'.$aReceiveData[' ThumbMediaId'];
        } elseif ($aReceiveData['MsgType'] == 'location') { //当前位置信息
            $aReponseData['Content'] = '您当前纬度：'.$aReceiveData['Location_X'].'\n\r';
            $aReponseData['Content'] .= '您当前经度：'.$aReceiveData['Location_Y'].'\n\r';
            $aReponseData['Content'] .= '您当前精度：'.$aReceiveData['Scale'].'\n\r';
            $aReponseData['Content'] .= '位置信息：'.$aReceiveData['Label'].'\n\r';
        } elseif ($aReceiveData['MsgType'] == 'link') { //链接类型
            $aReponseData['Content'] = '您发送的链接标题：'.$aReceiveData['Title'].'\n\r';
            $aReponseData['Content'] .= '您发送的链接描述：'.$aReceiveData['Description'].'\n\r';
            $aReponseData['Content'] .= '您发送的链接URL：'.$aReceiveData['Url'].'\n\r';
        }

    }

    /**
     * 根据种类获取回复需要的数据
     * @param $sType
     * @param $aData (基础数据，接收微信时候的用户信息，接收的时候传进来的)
     */
    private static function getResponseDataByType($sType, $aData)
    {
        if (!isset($aData['ToUserName']) || !isset($aData['FromUserName'])) {
            return null;
        }
        $aReponseData['ToUserName'] = $aData['ToUserName'];
        $aReponseData['FromUserName'] = $aData['FromUserName'];
        $aReponseData['CreateTime'] = time();
        $aReponseData['MsgType'] = $sType;
        //以上四个数据是肯定有的，其他数据根据类型不同，需求不一样,根据type不同，解析到不同的方法获取数据
        $sAction = 'initData'.$sType;
        self::$sAction($aReponseData);
        return $aReponseData;
    }

    /**
     * 创建回复信息
     * @param $sType
     * @param $aData(接收的所有信息)
     */
    public static function initResponse($sType, $aData)
    {
        $xResponsetemp = self::getReponseTempByType($sType);//获取对应类型XML格式的回复模版
        $aReponseData = self::getResponseDataByType($sType, $aData);//获取回复的数据
    }
}