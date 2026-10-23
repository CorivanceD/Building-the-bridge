<?php
include_once 'Model.class.php';

class ChatMessage extends Model {
    public $chatMessageId;
    public $chatId;
    public $customerId;
    public $userId;
    public $message;
    public $timestamp;
    
    public function validate() {
        if (!is_numeric($this->chatId)) {
            $this->setPropInvalid('chatId');
        }
        
        if (!is_numeric($this->customerId) && !is_numeric($this->userId)) {
            $this->setPropInvalid('authorId');
        }
        
        if (empty($this->message)) {
            $this->setPropInvalid('message');
        }                
    }
    
    /**
     * 
     * @return User or Customer
     */
    public function getAuthor() {
        if (!empty($this->userId)) {
            return UserManager::getUserById($this->userId);
        }
        
        if (!empty($this->customerId)) {
            return CustomerManager::getCustomerById($this->customerId);
        }
        
        return null;
    }
}
?>