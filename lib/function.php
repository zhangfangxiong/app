<?php
function showError($sMsg, $type = false)
{
    echo "<script language='JavaScript'>";
    echo "alert('$sMsg');";
    if ($type) {
        echo "history.back();";
    }
    echo "</script>";
    die;
}

//用于ajax请求
function showMsg($sMsg, $bool = true)
{
    $aData = array();
    $aData['status'] = $bool ? 1 : 0;
    $aData['data'] = $sMsg;
    echo json_encode($aData);
    die;
}

function showOk($sMsg, $sHref = "")
{
    echo "<script language='JavaScript'>";
    echo "alert('$sMsg');";
    if ($sHref) {
        echo "location.href='$sHref'";
    }
    echo "</script>";
    die;
}

function xml_parser($str)
{
    $xml_parser = xml_parser_create();
    if (!xml_parse($xml_parser, $str, true)) {
        xml_parser_free($xml_parser);
        return false;
    } else {
        return (json_decode(json_encode(simplexml_load_string($str)), true));
    }
}


