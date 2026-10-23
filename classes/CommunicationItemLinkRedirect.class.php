<?php

// Models and managers used by this class
require_once 'Model.class.php';
require_once 'CommunicationItemLinkRedirectManager.class.php';

class CommunicationItemLinkRedirect extends Model {
    
    public $communicationItemLinkRedirectId = null;
    public $communicationItemId = null;
    public $originalUrl = null;
    public $redirectHash = null;
    public $created = null;
    
    private $aCustomerLinkClicked = null;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->communicationItemId))
            $this->setPropInvalid('communicationItemId');
        if (empty($this->originalUrl))
            $this->setPropInvalid('originalUrl');
        if (empty($this->redirectHash))
            $this->setPropInvalid('redirectHash');
    }
    
    /**
     * generates hash for link redirect
     */
    public static function generateLinkRedirectHashByCommunicationItemId($iCommunicationItemId) {
        return substr(hash('sha512', $iCommunicationItemId . mt_rand(0, 1000000)), 0, 10).'-'.$iCommunicationItemId;
    }
    
    /**
     * generates hash for link redirect
     */
    public function returnFullLinkRedirect() {
        return CLIENT_HTTP_URL . '/newsletter-redirect/' . $this->redirectHash;
    }
    
    
    # get customers by LinkRedirect
    public function getCustomersByLinkRedirect() {
        if(is_null($this->aCustomerLinkClicked)) {
            $this->aCustomerLinkClicked = CommunicationItemLinkRedirectStatManager::getCustomersOfClickedRedirectLink($this->communicationItemId, $this->communicationItemLinkRedirectId);
        }
        return $this->aCustomerLinkClicked;
    }

    /**
     * check if news item is deletable
     * @return Boolean
     */
    public function isDeletable() {
        return true;
    }
    
}
