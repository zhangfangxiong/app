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
    const ADDKFACCOUT = 'https://api.weixin.qq.com/customservice/kfaccount/add';//添加客服账号
    const UPDATEKFACCOUNT = 'https://api.weixin.qq.com/customservice/kfaccount/update';//修改客服账号
    const DELETEKFACCOUNT = 'https://api.weixin.qq.com/customservice/kfaccount/del';//删除客服账号
    const UPLOADHEADINGIMG = 'http://api.weixin.qq.com/customservice/kfaccount/uploadheadimg';//设置账号头像
    const ACCOUNTLIST = 'https://api.weixin.qq.com/cgi-bin/customservice/getkflist';//客服列表
    const SENDMESSAGE = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';//发送消息

    /**
     * 添加客服账号
     * @param $kf_account
     * @param $nickname
     * @param $password
     * @param $sToken
     * @return mixed
     */
    public static function addKFAccount($kf_account, $nickname, $password,$sToken)
    {
        $aFile = array('kf_account' => $kf_account,'nickname' =>$nickname,'password'=>$password);
        $addKFUrl = self::ADDKFACCOUT . "?access_token=" . $sToken;
        $sReturn = self::curl($addKFUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 修改客服账号
     * @param $kf_account
     * @param $nickname
     * @param $password
     * @param $sToken
     * @return mixed
     */
    public static function updateKFAccount($kf_account, $nickname, $password,$sToken)
    {
        $aFile = array('kf_account' => $kf_account,'nickname' =>$nickname,'password'=>$password);
        $addKFUrl = self::UPDATEKFACCOUNT . "?access_token=" . $sToken;
        $sReturn = self::curl($addKFUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 删除客服账号
     * @param $kf_account
     * @param $nickname
     * @param $password
     * @param $sToken
     * @return mixed
     */
    public static function deleteKFAccount($kf_account, $nickname, $password,$sToken)
    {
        $aFile = array('kf_account' => $kf_account,'nickname' =>$nickname,'password'=>$password);
        $addKFUrl = self::DELETEKFACCOUNT . "?access_token=" . $sToken;
        $sReturn = self::curl($addKFUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 设置账号头像
     * @param $kf_account
     * @param $sToken
     * @param $sImageName
     * @return mixed
     */
    public static function uploadHeadImg($kf_account, $sToken,$sImageName)
    {
        $aFile = array('media' => '@'.$sImageName);
        $addKFUrl = self::UPLOADHEADINGIMG . "?access_token=" . $sToken.'&kf_account='.$kf_account;
        $sReturn = self::curl($addKFUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 获取账号列表
     * @param $sToken
     * @return mixed
     */
    public static function getAccountList($sToken)
    {
        $addKFUrl = self::ACCOUNTLIST . "?access_token=" . $sToken;
        $sReturn = self::curl($addKFUrl);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 发送消息
     */
    public static function sendMessage($aData,$sType)
    {

    }

    /**
     * 根据种类获取回复需要的数据
     * @param $sType 回复信息种类
     * @param $aData (基础数据，接收微信时候的用户信息，接收的时候传进来的)
     * @param $sToken
     */
    private static function getResponseDataByType($sType, $aData,$sToken)
    {
        if (!isset($aData['ToUserName']) || !isset($aData['FromUserName'])) {
            return null;
        }
        $aReponseData['ToUserName'] = $aData['FromUserName'];
        $aReponseData['FromUserName'] = $aData['ToUserName'];
        $aReponseData['CreateTime'] = time();
        $aReponseData['MsgType'] = $sType;
        //以上四个数据是肯定有的，其他数据根据类型不同，需求不一样,根据type不同，解析到不同的方法获取数据
        $sAction = 'initData'.$sType;
        self::$sAction($aReponseData,$aData,$sToken);
        return $aReponseData;
    }
}