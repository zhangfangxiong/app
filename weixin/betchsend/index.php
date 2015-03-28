<?php
include_once("../baseWeixin.php");

class betchsend_index extends baseWeixin
{
    public function indexAction()
    {

    }
}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
new betchsend_index();
?>
