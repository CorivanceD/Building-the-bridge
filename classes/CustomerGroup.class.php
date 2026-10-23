<?php

// Models and managers used by this class
require_once 'Model.class.php';
require_once 'CustomerGroupManager.class.php';

class CustomerGroup extends Model {

    const CUSTOMERGROUP_NEWSLETTER = 6;
    
    public $customerGroupId = null;
    public $title;
    public $created = null;
    public $modified = null;
    
    private $aClients;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->title))
            $this->setPropInvalid('title');
    }
    
    /**
     * check if item is deletable. Main category is never deletable
     * @return Boolean
     */
    public function isDeletable() {
        return $this->customerGroupId != self::CUSTOMERGROUP_NEWSLETTER ? true : false;
    }
    
    /**
     * get all customer of this group
     * @param $bFilterOnline
     * @param $bExcludeBounced
     * @param $bFilterBounced
     * @return array Clients $this->aClients
     */
    public function getClients($bFilterOnline = false, $bExcludeBounced = false, $bFilterBounced = false) {
        $this->aClients = CustomerManager::getCustomersByCustomerGroupId($this->customerGroupId, $bFilterOnline, $bExcludeBounced, $bFilterBounced);
        return $this->aClients;
    }
    
    /**
     * return count result of customers of this group
     * @param $bFilterOnline
     * @return int $iResult
     */
    public function getAmountOfClients($bFilterOnline = false) {
        return CustomerManager::getAmountOfCustomersByCustomerGroupId($this->customerGroupId, $bFilterOnline);
    }
    
    /**
     * return count result of bounced customers of this group
     * @param $bFilterOnline
     * @param $bFilterBounced
     * @return int $iResult
     */
    public function getAmountOfBouncedClients($bFilterOnline = false, $bFilterBounced = true) {
        return CustomerManager::getAmountOfCustomersByCustomerGroupId($this->customerGroupId, $bFilterOnline, $bFilterBounced);
    }

}

?>
