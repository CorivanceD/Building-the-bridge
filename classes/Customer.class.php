<?php

// Models and managers used by this class
require_once 'Model.class.php';

class Customer extends Model {
    
    const IMAGES_PATH = '/images/customer'; 
    const AGE_UNDER_12 = 1;
    const AGE_12_17 = 2;
    const AGE_18_24 = 3;
    const AGE_25_34 = 4;
    const AGE_35_44 = 5;
    const AGE_45_54 = 6;
    const AGE_55_64 = 7;
    const AGE_65_74 = 8;
    const AGE_75_OLDER = 9;
    const AGE_UNKNOWN = -1;
    public $customerId = null;
    public $companyName = null;
    public $companyAddress = null;
    public $companyPostalCode = null;
    public $companyCity = null;
    public $companyEmail = null;
    public $companyPhone = null;
    public $companyWebsite = null;
    public $gender = 'U';
    public $email = null;
    public $name = null;
    public $age = 0;
    public $countryId = null;
    public $birthDate = null;
    public $city = null;
    public $mobilePhone = null;
    public $phone = null;
    public $password;
    public $contactBySms = 1;
    public $contactByEmail = 1;
    public $didSignOffForEmail = 0;
    public $confirmCode = null;
    public $bounceRuleId; // only used when connected to bounce sync
    public $bounceRuleTitle; // only used when connected to bounce sync
    public $bounceRuleDescription; // only used when connected to bounce sync
    public $imageId = null;
    public $online = 0;
    public $receiveCommunicationVerification = 0;
    public $lastLogin = null;
    public $created = null;
    public $modified = null;
    private $aCustomerGroups = null;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
        if (empty($this->age))
            $this->setPropInvalid('age');
        if (empty($this->countryId))
            $this->setPropInvalid('countryId');
        if (empty($this->gender)) {
            $this->setPropInvalid('gender');
        }
        if (empty($this->city))
            $this->setPropInvalid('city');
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
            $this->setPropInvalid('email');
        if (CustomerManager::emailExists($this->email, $this->customerId))
            $this->setPropInvalid('emailExists');
        if (!empty($this->password) && strlen($this->password) < 8)
            $this->setPropInvalid('password');
        if (!is_numeric($this->online))
            $this->setPropInvalid('online');
        if (!is_numeric($this->contactBySms))
            $this->setPropInvalid('contactBySms');
        if (!is_numeric($this->contactByEmail))
            $this->setPropInvalid('contactByEmail');
    }

    /**
     * mask pasword for session use
     */
    function maskPass() {
        $this->password = 'XXX';
    }  
    
    /**
     * 
     * @return Image
     */
    public function getImage() {
        return ImageManager::getImageById($this->imageId);
    }     
    
    /**
     * 
     * @return string
     */
    public function getAge() {
        switch ($this->age) {
            case Customer::AGE_UNKNOWN:
                return 'NA';
            case Customer::AGE_UNDER_12:
                return '< 12';
            case Customer::AGE_12_17:
                return '12-17';
            case Customer::AGE_18_24:
                return '18-24';
            case Customer::AGE_25_34:
                return '25-34';
            case Customer::AGE_35_44:
                return'35-44';
            case Customer::AGE_45_54:
                return '45-54';
            case Customer::AGE_55_64:
                return '55-64';
            case Customer::AGE_65_74:
                return '65-74';
            case Customer::AGE_75_OLDER:
                return '> 74';
        }
    }
    
    /**
     * 
     * @return Country
     */
    public function getCountry() {
        return CountryManager::getCountry($this->countryId);
    }
    
    /**
     * get full name of client
     * @return type string
     */
    function getFullName() {
        return _e($this->name);
    }
    
    /**
     * get all CustomGroup objects related to this Customer
     * @return array CustomGroup $this->aCustomerGroups
     */
    public function getCustomerGroups() {
        if($this->aCustomerGroups === null) {
            $this->aCustomerGroups = CustomerGroupManager::getCustomerGroupsByCustomerId($this->customerId);
        }
        return $this->aCustomerGroups;
    }
    
    /**
     * set customer CustomGroups
     * @param array of CustomGroup objects
     */
    public function setCustomerGroups(array $aCustomerGroups) {
        $this->aCustomerGroups = $aCustomerGroups;
    }
    
    /**
     * check whether customer is linked to the CustomerGroup
     * @param int $iCustomerGroupId
     * @return boolean
     */
    public function isLinkedToCustomerGroup($iCustomerGroupId) {
        # return by default false
        $bResult = false;
        
        # when not yet set, get linked CustomerGroups
        if(empty($this->aCustomerGroups)) {
            $this->getCustomerGroups();
        }
        
        # loop through linked CustomerGroups
        foreach($this->aCustomerGroups as $oCustomerGroup) {
            # check whether user is linked to customGroup
            if($oCustomerGroup->customerGroupId == $iCustomerGroupId) {
                $bResult = true;
            }
        }
        # return result
        return $bResult;
    }
    
    /**
     * set default required data for csv import
     */
    public function setDefaultCsvImportValues() {
        # set required data for database
        $this->contactBySms = 1;
        $this->contactByEmail = 1;
        $this->didSignOffForEmail = 0;
        $this->receiveCommunicationVerification = 0;
        $this->city = 'Unknown';
        $this->age = self::AGE_UNKNOWN;
        $this->online = 1;
        $this->password = hashPasswordForDb(md5((string) $this->email));
        $this->countryId = -1;
    }
    
}
?>