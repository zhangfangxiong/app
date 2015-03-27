<?php
/**
 * Created by PhpStorm.
 * User: zhangfangxiong
 * Date: 15/3/8
 * Time: 下午3:20
 */

class base
{
    protected $oDb = null;
    protected $oMem = null;
    protected $tplVars = array();
    public function __construct ()
    {
        set_time_limit(0);
        if (isset($_GET['action']) && $_GET['action']) {
            $action = $_GET['action'] . "Action";
        } else {
            $action = "indexAction";
        }
        $this->$action();
    }

    protected function assign($sVar,$mData)
    {
        $this->tplVars[$sVar] = $mData;
    }

    protected function display($sFileName)
    {
        $sTemplate = $GLOBALS['VIEWPATH'].$sFileName;
        echo $this->_run($sTemplate,$this->tplVars);
    }

    protected function getDB()
    {
        if ($this->oDb == null) {
            $this->oDb = new DB();
        }
        return $this->oDb;
    }

    protected function getMem()
    {
        if ($this->oMem == null) {
            $this->oMem = new Lib_Memcache();
        }
        return $this->oMem;
    }

    protected function getCurrUser()
    {
        return "zfx";
    }

    protected function _run ($template, $vars, $useEval = false)
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
            include ($template);
        }
        $content = ob_get_clean();
        return $content;
    }
}