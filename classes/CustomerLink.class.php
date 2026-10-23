<?php

class CustomerLink extends Model {

    public $customerLinkId;
    public $online = 0;
    public $sender;
    public $name;
    public $url;
    public $returnUrl;    
    public $message;
    public $created;
    public $modified;
    private $aCategories;

    public function validate() {
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }        

        if (empty($this->url) || !filter_var($this->url, FILTER_VALIDATE_URL)) {
            $this->setPropInvalid('url');
        }
        
        if (empty($this->returnUrl) || !filter_var($this->url, FILTER_VALIDATE_URL)) {
            $this->setPropInvalid('returnUrl');
        } 
        
        if (empty($this->message)) {
            $this->setPropInvalid('message');
        }        
        
        if (empty($this->aCategories)) {
            $this->setPropInvalid('aCategories');
        }
    }

    /**
     * Get the categories of the link
     * 
     * @param boolean $bForceUpdate
     * @return array CustomerLinkCategory
     */
    public function getCategories($bForceUpdate = true) {
        if (!empty($this->customerLinkId) && ($bForceUpdate || empty($this->aCategories))) {
            $this->aCategories = CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('customerLinkId' => $this->customerLinkId));
        }

        return $this->aCategories;
    }
    
    /**
     * 
     * @return CustomerLinkCategory
     */
    public function getFirstCategory() {
        $aCategories = $this->getCategories(false);
        
        if (!empty($aCategories)) {
            return $aCategories[0];
        }
        
        return null;
    }

    /**
     * Set the categories of the link
     * 
     * @param array $aCategoriesIds
     */
    public function setCategories($aCategoriesIds) {
        $this->aCategories = array();

        foreach ($aCategoriesIds as $iCategoryId) {

            if (is_numeric($iCategoryId)) {
                $oCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById($iCategoryId);

                if (!empty($oCategory)) {
                    $this->aCategories[] = $oCategory;
                }
            }
        }
    }
    
    /**
     * 
     * @param boolean $bFlag
     * @return boolean
     */
    public function setOnline($bFlag = true) {
        if (!empty($this->customerLinkId)) {
            $this->online = $bFlag ? 1: 0;
            return CustomerLinkManager::updateLinkOnlineStatus($this);
        }
        
        return false;
    }

}

?>