<?php

/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/8
 * Time: 下午3:20
 */
class base
{
    protected static $oDb = null;
    protected static $oMem = null;
    protected $tplVars = array();
    protected static $memKey = '';

    public function __construct()
    {
        $GLOBALS['memkey'];
        $this->enviSet();
        set_time_limit(0);
        if ($this->getCurrSapi()) {
            $action = (isset($_GET['action']) && $_GET['action']) ? $_GET['action'] . 'Action' : 'indexAction';

        } else {
            $argc = $_SERVER['argv'];
            if (isset($argc[1]) && $argc[1]) {
                $action = ($argc[1] && $argc[1]) ? $argc[1] . 'Action' : 'indexAction';
            }
        }
        self::$memKey = $GLOBALS['memkey'];
        $this->$action();
    }

    /**
     * 判断当前运行环境
     */
    protected function getCurrSapi()
    {
        return php_sapi_name() == 'cli' ? 0 : 1;
    }

    /**
     * 特殊环境的处理
     */
    protected function enviSet()
    {
        if (!$this->getCurrSapi()) {
            //如果是cli环境
            //ini_set('');
        }
    }

    /**
     * 赋值
     * @param $sVar
     * @param $mData
     */
    protected function assign($sVar, $mData)
    {
        $this->tplVars[$sVar] = $mData;
    }

    /**
     * 加载模版
     * @param $sFileName
     */
    protected function display($sFileName)
    {
        $sTemplate = $GLOBALS['VIEWPATH'] . $sFileName;
        echo $this->_run($sTemplate, $this->tplVars);
    }

    /**
     * 获取数据库连接
     * @return DB|null
     */
    protected function getDB($DataBase)
    {
        if (self::$oDb == null) {
            self::$oDb = new DB($DataBase);
        } elseif ($DataBase != self::$oDb->DataBase) {
            self::$oDb = new DB($DataBase);
        }
        return self::$oDb;
    }

    /**
     * 获取memcache连接
     * @return Lib_Memcache|null
     */
    protected function getMem()
    {
        if (self::$oMem == null) {
            self::$oMem = new Lib_Memcache();
        }
        return self::$oMem;
    }

    /**
     * 获取当前用户
     * @return string
     */
    protected function getCurrUser()
    {
        return "zfx";
    }

    /**
     * 渲染模版
     * @param $template
     * @param $vars
     * @param bool $useEval
     * @return string
     */
    protected function _run($template, $vars, $useEval = false)
    {
        if ($vars == null && count($this->tplVars) > 0) {
            $vars = $this->tplVars;
        } else {
            $vars = array_merge($vars, $this->tplVars);
        }
        if ($vars != null) {
            extract($vars);
        }
        ob_start();
        if ($useEval == true) {
            eval('?>' . $template . '<?');
        } else {
            include($template);
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * 远程访问地址
     * @param $url
     * 访问url
     */
    protected function curl($sUrl, $bPost = false, $aData = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($bPost) {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($bPost && !empty($aData)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $aData);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 处理XML格式数据，返回数组
     * @param $xXml
     * @return array
     */
    protected function dealxml($xXml)
    {
        //转换为simplexml对象
        $xmlResult = simplexml_load_string($xXml, null, LIBXML_NOCDATA);
        //foreach循环遍历
        $aNode = array();
        foreach ($xmlResult->children() as $childItem) {
            //输出xml节点名称和值
            $item_arr = (array)$childItem;
            $aNode[$childItem->getName()] = $item_arr[0];
        }
        return $aNode;
    }

    protected function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
        }
        return false;
    }

    /**
     * 下载远程文件到本地
     * @param $file_url
     * @param $save_to
     */
    protected function download_remote_file_with_curl($file_url, $save_to)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);

        $downloaded_file = fopen($save_to, 'w');
        $result = fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);
        return $result;
    }


    /*
$content = simplexml_load_string('<content><![CDATA[Hello, world!]]></content>');
echo (string) $content;

$foo = simplexml_load_string('<foo><content><![CDATA[Hello, world!]]></content></foo>');
echo (string) $foo->content;

// 通过下面的方法自动过滤 CDATA 内部参数
$content = simplexml_load_string('<content><![CDATA[Hello, world!]]></content>', null, LIBXML_NOCDATA);
*/
}