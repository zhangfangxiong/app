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
    const NEWSURL = 'http://www.qidishu.com/article.php?id=';//主站文章地址

    /**
     * 文章列表
     * @param $sToken
     * @return mixed
     */
    public static function getArticleList()
    {
        $oDB = self::getDB(self::DBNAME);
        $aData = $oDB->get_all('SELECT * FROM ' . self::TABLENAME . ' WHERE is_weixin>0');
        return $aData;
    }

    /**
     * 根据ID获取文章
     * @param array $aIDs
     * @param bool $file_url 为true只获取有缩略图的文章
     * @return array
     */
    public static function getArticleByID($aIDs = array(), $file_url = false,$author = false)
    {
        $oDB = self::getDB(self::DBNAME);
        if (empty($aIDs) || !is_array($aIDs)) {
            return array();
        }
        $sSql = 'SELECT * FROM ' . self::TABLENAME . ' WHERE is_weixin > 0 ';
        if ($file_url) {
            $sSql .= 'AND LENGTH(file_url) > 0 ';
        }
        if ($author) {
            $sSql .= 'AND LENGTH(author) > 0 ';
        }
        $sSql .= 'AND article_id ';
        if (count($aIDs) == 1) {
            $sSql .= '=' . $aIDs[0];
        } else {
            $sSql .= 'IN (' . implode(',', $_POST['webnewid']) . ')';
        }
        $aData = $oDB->get_all($sSql);
        return $aData;
    }

    /**
     * 修改文章信息
     * @param $newsID
     * @param $param 要修改的信息用数组整理
     * @return bool
     */
    public static function updateArticle($newsID,$param)
    {
        $oDB = self::getDB(self::DBNAME);
        return $oDB->update(self::TABLENAME, $param, $condition = "article_id=".$newsID);
    }
}