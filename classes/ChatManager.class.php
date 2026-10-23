<?php

class ChatManager {

    /**
     * 
     * @param int $iChatId
     * @return Chat
     */
    public static function getChatById($iChatId) {
        $sQuery = 'SELECT * FROM `chats` WHERE `chatId` = ' . db_int($iChatId) . ';';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Chat');
    }

    /**
     * 
     * @return Chat
     */
    public static function getChats() {
        $sQuery = 'SELECT * FROM `chats` ORDER BY lastOpened DESC;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Chat');
    }

    /**
     * 
     * @return Chat
     */
    public static function getActiveChat() {
        $sQuery = 'SELECT * FROM `chats` ORDER BY `chatId` DESC LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Chat');
    }

    /**
     * 
     * @param int $iChatId
     * @param int $iLastMessages
     * @return array ChatMessage
     */
    public static function getChatMessages($iChatId, $iLastMessages = null) {
        $sQuery = 'SELECT * FROM `chatMessages` WHERE `chatId` = ' . db_int($iChatId) . ' ORDER BY `timestamp` ASC';

        if (!empty($iLastMessages)) {
            $sQuery .= ' LIMIT ' . db_int($iLastMessages);
        }

        $sQuery .= ';';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'ChatMessage');
    }

    /**
     * 
     * @param Chat $oChat
     */
    public static function saveChat($oChat) {
        $sQuery = ' INSERT INTO `chats` (
                        `chatId`,
                        `ownerId`,
                        `topic`,
                        `opened`,
                        `closedMessage`,
                        `lastOpened`,
                        `lastClosed`)
                    VALUES ('
                . db_int($oChat->chatId) . ', '
                . db_int($oChat->ownerId) . ', '
                . db_str($oChat->topic) . ', '
                . db_int($oChat->opened) . ', '
                . db_str($oChat->closedMessage) . ',                
                NOW(), '
                . db_date($oChat->lastClosed) . ')
                    ON DUPLICATE KEY UPDATE
                        `ownerId`=VALUES(`ownerId`),
                        `topic`=VALUES(`topic`),
                        `opened`=VALUES(`opened`),
                        `closedMessage`=VALUES(`closedMessage`),
                        `lastOpened`=VALUES(`lastOpened`),
                        `lastClosed`=VALUES(`lastClosed`);';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if (empty($oChat->chatId)) {
            $oChat->chatId = $oDb->insert_id;
        }
    }    
    
    /**
     * 
     * @param Chat $oChat
     */
    public static function closeChat($oChat) {
        $sQuery = 'UPDATE `chats` SET `opened` = 0, `lastClosed` = NOW() WHERE `chatId` = '.db_int($oChat->chatId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);        
    }
    
    /**
     * 
     * @param Chat $oChat
     */
    public static function openChat($oChat) {
        $sQuery = 'UPDATE `chats` SET `opened` = 1, `lastOpened` = NOW() WHERE `chatId` = '.db_int($oChat->chatId);        
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);   
    }

    /**
     * 
     * @param ChatMessage $oChatMessage
     */
    public static function saveChatMessage($oChatMessage) {
        $sQuery = ' INSERT INTO `chatMessages` (      
                        `chatId`,
                        `customerId`,
                        `userId`,
                        `message`)
                    VALUES ('
                . db_int($oChatMessage->chatId) . ', '
                . db_int($oChatMessage->customerId) . ', '
                . db_int($oChatMessage->userId) . ', '
                . db_str($oChatMessage->message) . ');';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        $oChatMessage->chatMessageId = $oDb->insert_id;
    }
}
?>