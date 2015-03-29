<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/29
 * Time: 下午9:19
 */
include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class Group extends ModelBase
{
    const CREATEGROUPURL = "https://api.weixin.qq.com/cgi-bin/groups/create";//创建分组接口
    const GROUPLIST = "https://api.weixin.qq.com/cgi-bin/groups/get";//分组列表接口
    const USERINGROUP = "https://api.weixin.qq.com/cgi-bin/groups/getid";//用户所在组接口
    const UPDATEGROUPNAME = "https://api.weixin.qq.com/cgi-bin/groups/update";//修改组名接口
    const UPDATEUSERGROUP = "https://api.weixin.qq.com/cgi-bin/groups/members/update";//修改用户分组

    /**
     * 创建分组
     * @param $sToken
     * @param $sName
     * @return mixed
     */
    public function createGroup($sToken,$sName)
    {
        $aFile = json_encode(array("group" => array("name"=>$sName)));
        $sCreateGroupUrl = self::CREATEGROUPURL . "?access_token=" . $sToken;
        $sReturn = $this->curl($sCreateGroupUrl, true, $aFile);
        $aData = json_decode($sReturn, true);
        return $aData;
    }

    /**
     * 获取组列表
     * @param $sToken
     * @return mixed
     */
    public function getGroupList($sToken)
    {
        $sGetGroupList = self::GROUPLIST . "?access_token=" . $sToken;
        $sReturn = $this->curl($sGetGroupList);
        return json_decode($sReturn,true);
    }

    /**
     * 根据用户ID找到所在组
     * @param $sToken
     * @param $sOpenID
     * @return mixed
     */
    public function getGroupByUserID($sToken,$sOpenID)
    {
        $sGetGroupList = self::USERINGROUP . "?access_token=" . $sToken;
        $sReturn = $this->curl($sGetGroupList,true,json_encode(array("openid"=>$sOpenID)));
        return json_decode($sReturn,true);
    }

    /**
     * 修改用户所在组
     * @param $sToken
     * @param $sOpenID
     * @return mixed
     */
    public function updateUserGroup($sToken,$sOpenID,$sGroupID)
    {
        $sGetGroupList = self::UPDATEUSERGROUP . "?access_token=" . $sToken;
        $sReturn = $this->curl($sGetGroupList,true,json_encode(array("openid"=>$sOpenID,"to_groupid"=>$sGroupID)));
        return json_decode($sReturn,true);
    }

    /**
     * 修改组名
     * @param $sToken
     * @param $aData
     * @return mixed
     */
    public function updateGroupName($sToken,$aData)
    {
        //格式
        //$aData = array("group"=>array("id"=>$iGroupID,"name"=>"test6666666"));

        $sGetGroupList = self::UPDATEGROUPNAME . "?access_token=" . $sToken;
        $sReturn = $this->curl($sGetGroupList,true,json_encode($aData));
        return json_decode($sReturn,true);
    }
}