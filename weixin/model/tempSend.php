<?php
/**
 * 模板消息类
 * Created by zfx.
 * User: zhangfangxiong
 * Date: 15/6/8
 * Time: 下午7:56
 */
include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class Media extends ModelBase
{
    const SETINDUSTRY = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry';//设置所属行业
    const GETTEMPID = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template';//获取模板ID
    const SENDTEMP = 'https://api.weixin.qq.com/cgi-bin/message/template/send';//发送模板消息

    /**
     * 设置所属行业
     * @param array $apram array('industry_id1'=>,'industry_id2'=>)格式，多个行业ID数组
     * @param $sToken
     */
    public static function setIndustry($apram = array(),$sToken)
    {
        $addKFUrl = self::SETINDUSTRY . "?access_token=" . $sToken;
    }
}