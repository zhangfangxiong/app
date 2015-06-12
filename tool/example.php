<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once '../lib/excelReader.php';
$data = new Spreadsheet_Excel_Reader("../file/nn2003.xls");

//print_r($data->sheets[0]['cells']);
//判断当前编码
//$encode = mb_detect_encoding($data->sheets[0]['cells'][2][2], array('ASCII','UTF-8','GB2312','GBK','BIG5'));
//更改当前编码
header("Content-Type:text/html;charset=utf-8");
mysql_query("SET NAMES utf8");
print_r($data->sheets[0]['cells']);
die;
?>
<html>
<head>
<style>
table.excel {
	border-style:ridge;
	border-width:1;
	border-collapse:collapse;
	font-family:sans-serif;
	font-size:12px;
}
table.excel thead th, table.excel tbody th {
	background:#CCCCCC;
	border-style:ridge;
	border-width:1;
	text-align: center;
	vertical-align:bottom;
}
table.excel tbody th {
	text-align:center;
	width:20px;
}
table.excel tbody td {
	vertical-align:bottom;
}
table.excel tbody td {
    padding: 0 3px;
	border: 1px solid #EEEEEE;
}
</style>
</head>

<body>
<?php echo $data->dump(true,true); ?>
</body>
</html>
