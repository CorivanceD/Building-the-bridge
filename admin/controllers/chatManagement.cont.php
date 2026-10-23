<?php
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_chat_mgmt');
$oPageLayout->sModuleName = sysTranslations::get('global_chat_mgmt');
$oPageLayout->sPagePath = ADMIN_PAGES_FOLDER .'/chat/chat.inc.php';

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove status update, always show once

$sAction = http_post('action', null);
$oActiveChat = ChatManager::getActiveChat();

if (!http_session('userColors', false)) {
    $_SESSION['userColors'] = array();
}


$oChat = new Chat();

switch ($sAction) {
    case 'startNew':        
        $sChatTopic = http_post('chatTopic', null);
        $sClosedMessage = http_post('closedMessage', 'Our live chat is currently closed, please come back later.');
        $oResObj = new stdClass();
        
        if (empty($sChatTopic)) {
            $_SESSION['statusUpdate'] = sysTranslations::get('chat_enter_topic');
        }
        elseif (empty($sClosedMessage)) {
            $_SESSION['statusUpdate'] = sysTranslations::get('chat_enter_closed_message');
        }
        else {            
            
            if (!empty($oActiveChat)) {
                ChatManager::closeChat($oActiveChat);
            }
            
            $oChat = new Chat();
            $oChat->opened = 1;
            $oChat->ownerId = $oCurrentUser->userId;
            $oChat->topic = $sChatTopic;
            $oChat->closedMessage = $sClosedMessage;
            ChatManager::saveChat($oChat);     
            $oActiveChat  = $oChat;
            $_SESSION['statusUpdate'] = sysTranslations::get('chat_new_started');
        }
        
        break; 
    case 'ajax-sendMessage':
        $sMessage = http_post('message', null);
        $oResObj = new stdClass();
        
        if (!empty($sMessage)) {
            $oChatMessage= new ChatMessage();
            $oChatMessage->chatId = $oActiveChat->chatId;
            $oChatMessage->userId = $oCurrentUser->userId;
            $oChatMessage->message = $sMessage;
            ChatManager::saveChatMessage($oChatMessage);
            $oResObj->success = true;
        }
        else {
            $oResObj->success = false;
        }
        
        die(json_encode($oResObj));
        break;
    case 'openChat':
        if (!empty($oActiveChat)) {
            ChatManager::openChat($oActiveChat);
            $oActiveChat = ChatManager::getActiveChat();
        }
        break;        
    case 'closeChat':
        if (!empty($oActiveChat)) {
            ChatManager::closeChat($oActiveChat);
            $oActiveChat = ChatManager::getActiveChat();
        }
        break;        
    case 'ajax-update':
        $oResObj = new stdClass();
        $aLastMessages = ChatManager::getChatMessages($oActiveChat->chatId);      
        
        if (!empty($aLastMessages)) {
            $oLastMessage = $aLastMessages[count($aLastMessages) - 1];
            $_SESSION['lastMessageId'] = $oLastMessage->chatMessageId;
        }
        
        $oResObj->lastMessages = '';
        $aUserColors = http_session('userColors');
        
        foreach($aLastMessages AS $oChatMessage) {
            $oAuthor = $oChatMessage->getAuthor();
            $sUserName = '';
            
            if ($oAuthor instanceof User) {
                $sUserName = $oAuthor->username;
            }
            else {
                $sUserName = $oAuthor->name;
            }   
            
            if (!isset($aUserColors[$sUserName])) {
                $aUserColors[$sUserName] = dechex(rand(0x000000, 0xFFFFFF));
                $_SESSION['userColors'] = $aUserColors;
            }
                     
            $oResObj->lastMessages .= '<span style="color:#'.$aUserColors[$sUserName].'"><b>'.$sUserName.':</b></span>&nbsp;' . $oChatMessage->message . '<br>';
        }

        die(json_encode($oResObj));
        break;
}

include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>