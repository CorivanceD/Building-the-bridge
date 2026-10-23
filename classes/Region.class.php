<?php
class Region extends Model {
    const REGION_ID_UN_NY = 12;
    const REGION_ID_UN_GE= 14;
    public $regionId;
    public $name;
    private $aCountries;
    
    public function validate() {
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }
    }
    
    /**
     * Get the countries in the region
     * 
     * @param boolean $bForceUpdate
     * @return array Country
     */
    public function getCountries($bForceUpdate = false) {
        if (!empty($this->regionId) && ($bForceUpdate || empty($this->aCountries))) {
            $this->aCountries = CountryManager::getCountriesByRegionId($this->regionId);
        }
        
        return $this->aCountries;
    }
}
?>