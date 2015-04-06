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
     * @param $nickname
     * @param $password
     * @param $sToken
     * @return mixed
     */
    public static function uploadHeadImg($kf_account, $nickname, $password,$sToken)
    {
        $aFile = array('media' => '@test.jpg');
        $addKFUrl = self::UPLOADHEADINGIMG . "?access_token=" . $sToken.'&kf_account='.$kf_account;
        $sReturn = self::curl($addKFUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 设置账号头像
     * @param $kf_account
     * @param $nickname
     * @param $password
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
}