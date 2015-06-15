<?php
class Model_city extends base
{
    private static $aCityList = array();//所有城市列表
    private static $aProvinceCitys = array();//省下城市列表

    /**
     * 获取市列表,以ID为Key
     */
    public static function getCityList()
    {
        if (empty(self::$aCityList)) {
            $oMem = self::getMem();
            $aData = $oMem->get(self::$memKey."_Tool_City_List");
            if (!$aData) {
                $sSql = "SELECT * FROM city";
                $oDB = self::getDB('tool');
                $aData = $oDB->get_all($sSql,"code");
                $oMem->set(self::$memKey."_Tool_City_List",$aData);
            }
            self::$aCityList = $aData;
        }
        return self::$aCityList;
    }

    /**
     * 获取市列表，以市名为key
     */
    public static function getCityListName()
    {
        $aCityList = self::getCityList();
        $aTmp = array();
        if (!empty($aCityList)) {
            foreach ( $aCityList as $key => $value ) {
                $aTmp[$value['name']] = $value;
            }
        }
        unset($aCityList);
        return $aTmp;
    }

    /**
     * 获取省下所有城市
     */
    public static function getCitysByProvince($sCode)
    {
        if (empty(self::$aProvinceCitys)) {
            $oMem = self::getMem();
            $aData = $oMem->get(self::$memKey."_Tool_City_List_".$sCode);
            if (!$aData) {
                $sSql = "SELECT * FROM city WHERE provincecode=".$sCode;
                $oDB = self::getDB('tool');
                $aData = $oDB->get_all($sSql,"code");
                $oMem->set(self::$memKey."_Tool_City_List_".$sCode,$aData);
            }
            self::$aProvinceCitys = $aData;
        }
        return self::$aProvinceCitys;
    }



    /**
     * @param $sCode
     * @return mixed
     */
    public static function getCityByID($sCode)
    {
        $aCityList = self::getCityList();
        return isset($aCityList[$sCode]) ? $aCityList[$sCode] : array();
    }

    /**
     * @param $sName
     */
    public static function getCityByName($sName)
    {
        $aCity = self::getCityListName();
        return isset($aCity[$sName]) ? $aCity[$sName] : array();
    }
}
