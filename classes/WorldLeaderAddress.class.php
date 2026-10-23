<?php
class WorldLeaderAddress extends Model {
    public $worldLeaderAddressId;
    public $worldLeaderId;
    public $name;
    public $address;
    public $postalCode;
    public $city;
    public $addressCountryId;
    public $regionId;
    public $countryId;
    public $order = 9999;
    public $created;
    public $modified;
    private $oAddressCountry;
    private $oWorldLeader;
    private $oCountry;
    private $oRegion;
    
    public function validate() {
        if (!is_numeric($this->worldLeaderId)) {
            $this->setPropInvalid('worldLeaderId');
        }
        
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }
        
        if (empty($this->address)) {
            $this->setPropInvalid('address');
        }
                
        if (empty($this->city)) {
            $this->setPropInvalid('city');
        }    
        
        if (!is_numeric($this->countryId)) {
            $this->setPropInvalid('countryId');
        }        
                        
        if (!is_numeric($this->addressCountryId)) {
            $this->setPropInvalid('addressCountryId');
        }
        
        if (!is_numeric($this->regionId)) {
            $this->setPropInvalid('regionId');
        }    
        
        if (!is_numeric($this->order)) {
            $this->setPropInvalid('order');
        }          
    }
    
    /**
     * Get the WorldLeader object linked to the address
     * @param boolean $bForceUpdate
     * @return WorldLeader
     */
    public function getWorldLeader($bForceUpdate = true) {
        if (!empty($this->worldLeaderId) && ($bForceUpdate || empty($this->oWorldLeader))) {
            $this->oWorldLeader = WorldLeaderManager::getWorldLeaderById($this->worldLeaderId);
        }
        
        return $this->oWorldLeader;
    }
    
    /**
     * Get the Country object linked to the address
     * @param boolean $bForceUpdate
     * @return Country
     */
    public function getAddressCountry($bForceUpdate = true) {
        if (!empty($this->addressCountryId) && ($bForceUpdate || empty($this->oAddressCountry))) {
            $this->oAddressCountry = CountryManager::getCountry($this->addressCountryId);
        }
        
        return $this->oAddressCountry;
    }      
    
    /**
     * Get the Country object linked to the region
     * @param boolean $bForceUpdate
     * @return Country
     */
    public function getCountry($bForceUpdate = true) {
        if (!empty($this->countryId) && ($bForceUpdate || empty($this->oCountry))) {
            $this->oCountry = CountryManager::getCountry($this->countryId);
        }
        
        return $this->oCountry;
    }  
    
    /**
     * Get the Region object linked to the address
     * @param boolean $bForceUpdate
     * @return Region
     */
    public function getRegion($bForceUpdate = true) {
        if (!empty($this->regionId) && ($bForceUpdate || empty($this->oRegion))) {
            $this->oRegion = CountryManager::getRegionById($this->regionId);
        }
        
        return $this->oRegion;
    }    
    
    /**
     * Check if the address could be deleted or not
     * @return boolean
     */
    public function isDeletable() {
        if (empty($this->worldLeaderAddressId)) {
            return true;
        }
        else {
            return count(WorldLeaderLetterManager::getWorldLeaderLettersByAddressId($this->worldLeaderAddressId)) === 0;
        }
    }
}
?>