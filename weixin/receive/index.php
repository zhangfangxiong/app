<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/4/1
 * Time: 下午11:22
 */
include_once('../baseWeixin.php');
include_once('../model/response.php');
class receive_index extends baseWeixin
{
    private static $sResponseTpe = 'text';//回复类型

    //接收消息以及相关的操作
    public function indexAction()
    {
        //接收消息的结构大致为
        $aMsg = $this->receiveMsg();//接收的信息
/**
        $aMsg = Array
        (
            'URL' => 'http://wx.maimaimiao.com/zfx/test/weixin/receive/',
    'ToUserName' => 'fdsfds',
    'FromUserName' => 'fdsfsd',
    'CreateTime' => '1111',
    'MsgType' => 'text',
    'Content' => '11111',
    'MsgId' => '1111'
);
 */
        //微信没有消息列表接口，需要自己存入数据库，不然无法调用客服接口
        //调用回复接口
        $sToken = $this->getAccessToken();
        $sResponse = response::initResponse(self::$sResponseTpe, $aMsg,$sToken);
        echo $sResponse;
        die;
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new receive_index();