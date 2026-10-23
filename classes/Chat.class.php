<?php

include_once "Model.class.php";

class Chat extends Model {
    public $chatId;
    public $ownerId;
    public $topic;
    public $opened = 1;
    public $closedMessage;
    public $lastOpened;
    public $lastClosed;
    
    public function validate() {
        if (empty($this->ownerId)) {
            $this->setPropInvalid('ownerId');
        }
        
        if (empty($this->topic)) {
            $this->setPropInvalid('topic');
        }                
    }
    
    /**
     * 
     * @return User
     */
    public function getOwner() {
        return UserManager::getUserById($this->ownerId);
    }
    
    /**
     * 
     * @param int $iLastMessages
     * @return array ChatMessage
     */
    public function getMessages($iLastMessages = null) {
        return ChatManager::getChatMessages($this->chatId, $iLastMessages);
    }
    
    /**
     * 
     * @param int $iLastMessageId
     * @return array ChatMessage
     */
    public function getMessagesFromId($iLastMessageId) {
        return ChatManager::getChatMessagesFromId($this->chatId, $iLastMessageId);
    }
}
?>
