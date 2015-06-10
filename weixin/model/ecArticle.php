<?php
/**
 * echshop中文章列表
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/6/10
 * Time: 上午12:24
 */

include_once(dirname(dirname(dirname(__FILE__))) . "/lib/ModelBase.php");

class ecArticle extends ModelBase
{
    const DBNAME = 'qidishucom';
    const TABLENAME = 'ecs_article';
    /**
     * 文章列表
     * @param $sToken
     * @return mixed
     */
    public static function getArticleList()
    {
        $oDB = self::getDB(self::DBNAME);
        $aData = $oDB->get_all('SELECT * FROM '.self::TABLENAME.' WHERE is_weixin>0');
        return $aData;
    }
}