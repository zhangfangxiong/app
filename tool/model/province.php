<?php
class Model_Provice extends base
{
    private static $aProvinceList = array();

    /**
     * 获取省列表,以ID为Key
     */
    public static function getProvinceList()
    {
        if (empty(self::$aProvinceList)) {
            //$oMem = self::getMem();
            //$aData = $oMem->get("Tool_Province_List");
            //if (!$aData) {
                $sSql = "SELECT * FROM province";
                $oDB = self::getDB('tool');
                $aData = $oDB->get_all($sSql,"code");
                //$oMem->set("Tool_Province_List",$aData);
            //}
            self::$aProvinceList = $aData;
        }
        return self::$aProvinceList;
    }

    /**
     * 获取省列表，以省名为key
     */
    public static function getProvinceListName()
    {
        $aProList = self::getProvinceList();
        $aTmp = array();
        if (!empty($aProList)) {
            foreach ( $aProList as $key => $value ) {
                $aTmp[$value['name']] = $value;
            }
        }
        unset($aProList);
        return $aTmp;
    }



    /**
     * @param $sCode
     * @return mixed
     */
    public static function getProvinceByID($sCode)
    {
        $aProList = self::getProvinceList();
        return isset($aProList[$sCode]) ? $aProList[$sCode] : array();
    }

    /**
     * @param $sName
     */
    public static function getProvinceByName($sName)
    {
        $aProvince = self::getProvinceListName();
        return isset($aProvince[$sName]) ? $aProvince[$sName] : array();
    }
}
