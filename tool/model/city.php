<?php
class Model_city extends base
{
    private $aCityList = array();//所有城市列表
    private $aProvinceCitys = array();//省下城市列表


    public function __construct ()
    {

    }

    /**
     * 获取市列表,以ID为Key
     */
    public function getCityList()
    {
        if (empty($this->aCityList)) {
            $oMem = $this->getMem();
            $aData = $oMem->get("Tool_City_List");
            if (!$aData) {
                $sSql = "SELECT * FROM city";
                $oDB = $this->getDB();
                $aData = $oDB->get_all($sSql,"code");
                $oMem->set("Tool_City_List",$aData);
            }
            $this->aCityList = $aData;
        }
        return $this->aCityList;
    }

    /**
     * 获取市列表，以市名为key
     */
    public function getCityListName()
    {
        $aCityList = $this->getCityList();
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
    public function getCitysByProvince($sCode)
    {
        if (empty($this->aProvinceCitys)) {
            $oMem = $this->getMem();
            $aData = $oMem->get("Tool_City_List_".$sCode);
            if (!$aData) {
                $sSql = "SELECT * FROM city WHERE provincecode=".$sCode;
                $oDB = $this->getDB();
                $aData = $oDB->get_all($sSql,"code");
                $oMem->set("Tool_City_List_".$sCode,$aData);
            }
            $this->aProvinceCitys = $aData;
        }
        return $this->aProvinceCitys;
    }



    /**
     * @param $sCode
     * @return mixed
     */
    public function getCityByID($sCode)
    {
        $aCityList = $this->getCityList();
        return isset($aCityList[$sCode]) ? $aCityList[$sCode] : array();
    }

    /**
     * @param $sName
     */
    public function getCityByName($sName)
    {
        $aCity = $this->getCityListName();
        return isset($aCity[$sName]) ? $aCity[$sName] : array();
    }
}
