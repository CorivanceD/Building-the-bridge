<?php

// Models and managers used by this class
require_once 'Model.class.php';
require_once 'CommunicationItemLinkRedirectManager.class.php';

class CommunicationItemLinkRedirectStat extends Model {
    
    public $customerId = null;
    public $communicationItemId = null;
    public $name = null;
    public $email = null;
    public $communicationItemLinkRedirectId = null;
    public $created = null;
    
    /**
     * validate object
     */
    public function validate() {

    }
    
}
