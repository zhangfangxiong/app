<?php
include_once(dirname(dirname(__FILE__)) . "/baseWeixin.php");
include_once(dirname(dirname(__FILE__)) . "/model/Media.php");
include_once(dirname(dirname(__FILE__)) . "/model/Group.php");
include_once(dirname(dirname(__FILE__)) . "/model/User.php");
include_once(dirname(dirname(__FILE__)) . "/model/Batchsend.php");


class batchsend_index extends baseWeixin
{
    public function takeUserToGroupAction()
    {
        if (!isset($_GET['token']) || !$_GET['token']) {
            showError('无token数据', false);
        }
        if (!isset($_GET['year']) || !$_GET['year'] || !isset($_GET['month']) || !$_GET['month'] ) {
            showError('年龄信息有误', false);
        }
        $sGroupName = intval($_GET['year']).sprintf("%02d", intval($_GET['month']));//生成4位数，不足前面补0;
        $sToken = $this->getAccessToken();
        $aGroupList = Group::getGroupList($sToken);
        foreach ($aGroupList['groups'] as $key => $value) {
            //重构数据结构
            $aTmp[$value['name']] = $value;
        }
        $aGroupList = $aTmp;
        if (!isset($aGroupList[$sGroupName])) {
            //showError('没有指定分组')
            //默认就是无分组，不做操作
        } else {
            $sOpenID = $_GET['token'];//用户token,即用户ID
            Group::updateUserGroup($sToken,$sOpenID,$aGroupList[$sGroupName]['id']);
        }
        showOk('个人信息填写成功');
    }
}

header("Content-Type:text/html;charset=utf-8");
new batchsend_index();
?>
