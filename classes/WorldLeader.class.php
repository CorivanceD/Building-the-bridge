<?php
class WorldLeader extends Model {
    public $worldLeaderId;
    public $prefix;
    public $name;
    public $email;
    public $position;
    public $company;
    public $department;
    public $created;
    public $modified;
    private $aAddresses; //connection with WorldLeaderAddress
    
    public function validate() {
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }                               
    }
    
    /**
     * Get the addresses of the world leader
     * @param int $iRegionId
     * @param int $iCountryId
     * @param boolean $bForceUpdate
     * @return array WordLeaderAddress
     */
    public function getAddresses($iRegionId = null, $iCountryId = null, $bForceUpdate = true) {
        if (!empty($this->worldLeaderId) && (empty($this->aAddresses) || $bForceUpdate)) {
            $this->aAddresses = WorldLeaderAddressManager::getWorldLeaderAddressesByLeaderId($this->worldLeaderId, $iRegionId, $iCountryId);
        }
        
        return $this->aAddresses;
    }
    
    /**
     * Check if the record is deletable
     * 
     * @return boolean
     */
    public function isDeletable() {
        if (empty($this->worldLeaderId)) {
            return true;
        }
        else {
            return count(WorldLeaderLetterManager::getWorldLeaderLettersByLeaderId($this->worldLeaderId)) === 0;
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getCompleteName() {
        $sCompleteName = '';
        
        if (strpos($this->position, $this->prefix) === false) {
            $sCompleteName .= $this->prefix . ' ';
        }
        
        $sCompleteName .= $this->name . ', '. $this->position ;
        
        return $sCompleteName;
    }
}
?>