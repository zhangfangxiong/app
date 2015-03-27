<?php
class Model_Provice extends base
{
    private $aProvinceList = array();


    public function __construct ()
    {

    }

    /**
     * 获取省列表,以ID为Key
     */
    public function getProvinceList()
    {
        if (empty($this->aProvinceList)) {
            $oMem = $this->getMem();
            $aData = $oMem->get("Tool_Province_List");
            if (!$aData) {
                $sSql = "SELECT * FROM province";
                $oDB = $this->getDB();
                $aData = $oDB->get_all($sSql,"code");
                $oMem->set("Tool_Province_List",$aData);
            }
            $this->aProvinceList = $aData;
        }
        return $this->aProvinceList;
    }

    /**
     * 获取省列表，以省名为key
     */
    public function getProvinceListName()
    {
        $aProList = $this->getProvinceList();
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
    public function getProvinceByID($sCode)
    {
        $aProList = $this->getProvinceList();
        return isset($aProList[$sCode]) ? $aProList[$sCode] : array();
    }

    /**
     * @param $sName
     */
    public function getProvinceByName($sName)
    {
        $aProvince = $this->getProvinceListName();
        return isset($aProvince[$sName]) ? $aProvince[$sName] : array();
    }
}
