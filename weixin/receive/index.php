<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/4/1
 * Time: 下午11:22
 */
include_once("../baseWeixin.php");
class receive_index extends baseWeixin
{
    private static $sResponseTpe = 'txt';//回复类型
    public function indexAction()
    {
        $xMsg = $this->receiveMsg();
        print_r($xMsg);
        die;
    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new receive_index();