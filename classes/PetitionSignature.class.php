<?php

include_once 'Model.class.php';

class PetitionSignature extends Model {

    public $petitionId;
    public $customerId;
    public $comment;
    public $date;

    public function validate() {
        if (!is_numeric($this->petitionId)) {
            $this->setPropInvalid('petitionId');
        }

        if (!is_numeric($this->customerId)) {
            $this->setPropInvalid('customerId');
        }
    }

    /**
     * 
     * @return Customer
     */
    public function getCustomer() {
        return CustomerManager::getCustomerById($this->customerId);
    }

    /**
     * 
     * @return Petition
     */
    public function getPetition() {
        return PetitionManager::getPetitionById($this->petitionId);
    }

}

?>