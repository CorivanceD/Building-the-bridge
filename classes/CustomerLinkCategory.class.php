<?php

class CustomerLinkCategory extends Model {

    public $customerLinkCategoryId;
    public $name;
    public $parentId;
    public $created;
    public $modified;
    private $oParent;
    private $aSubCategories;
    private $aCustomerLinks;

    public function validate() {
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }
    }

    /**
     * Get the parent category
     * 
     * @param boolean $bForceUpdate
     * @return CustomerLinkCategory
     */
    public function getParent($bForceUpdate = true) {

        if (!empty($this->parentId) && ($bForceUpdate || empty($this->oParent))) {
            $this->oParent = CustomerLinkCategoryManager::getCustomerLinkCategoryById($this->parentId);
        }

        return $this->oParent;
    }

    /**
     * Get the links inside this category
     * 
     * @param boolean $bForceUpdate
     * @param boolean $bSubCategories
     * @return array CustomerLink
     */
    public function getCustomerLinks($bForceUpdate = true, $bSubCategories = false) {

        if (!empty($this->customerLinkCategoryId) && ($bForceUpdate || empty($this->aCustomerLinks))) {
            $this->aCustomerLinks = CustomerLinkManager::getCustomerLinksByFilter(array('categoryIds' => array($this->customerLinkCategoryId)));
            
            if ($bSubCategories) {
                $aCustomerLinks = empty($this->aCustomerLinks) ? array() : $this->aCustomerLinks;
                
                foreach ($this->getSubCategories() as $oSubCategory) {
                    $aLinks = $oSubCategory->getCustomerLinks(true, false);
                    
                    if (!empty($aLinks)) {
                        $aCustomerLinks = array_merge($aCustomerLinks, $aLinks);
                    }
                }
                
                return $aCustomerLinks;
            }
        }

        return $this->aCustomerLinks;
    }

    /**
     * 
     * @param boolean $bLookSubCategories
     * @return boolean
     */
    public function hasLinks($bLookSubCategories = true) {
        $iLinkCount = 0;

        if (!empty($this->customerLinkCategoryId)) {
            $aCustomerLinks = CustomerLinkManager::getCustomerLinksByFilter(array('categoryIds' => array($this->customerLinkCategoryId)));
            $iLinkCount += empty($aCustomerLinks) ? 0 : count($aCustomerLinks);

            if ($bLookSubCategories) {
                foreach ($this->getSubCategories() as $oSubCategory) {
                    $aCustomerLinks = CustomerLinkManager::getCustomerLinksByFilter(array('categoryIds' => array($oSubCategory->customerLinkCategoryId)));
                    $iLinkCount += empty($aCustomerLinks) ? 0 : count($aCustomerLinks);
                }
            }
        }
        
        return $iLinkCount;
    }

    /**
     * Get the subcategories inside this category
     * 
     * @param boolean $bForceUpdate
     * @return array CustomerLinkCategory
     */
    public function getSubCategories($bForceUpdate = true) {

        if (!empty($this->customerLinkCategoryId) && ($bForceUpdate || empty($this->$aSubCategories))) {
            $this->aSubCategories = CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('parentId' => $this->customerLinkCategoryId));
        }

        return $this->aSubCategories;
    }

}

?>