<?php
include_once("baseWeixin.php");

class betchsend_index extends baseWeixin
{

}

header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
$index = new betchsend_index();
?>
