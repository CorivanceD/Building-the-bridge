<?php


$sAction = http_post('action', 'ajax-update');
$oActiveChat = ChatManager::getActiveChat();

if (empty($oActiveChat)) {
    $oActiveChat = new Chat();
}

if (!http_session('userColors', false)) {
    $_SESSION['userColors'] = array();
}


$oChat = new Chat();

switch ($sAction) {      
    case 'loginFirst':
        $_SESSION['accountStatusUpdate'] = 'You must login before using the chatroom';
        http_redirect('/account');
        break;
    case 'sendMessage':
        $sMessage = http_post('message', null);
        $oResObj = new stdClass();
        
        if (!empty($sMessage)) {
            $oChatMessage= new ChatMessage();
            $oChatMessage->chatId = $oActiveChat->chatId;
            $oChatMessage->customerId = $oCurrentCustomer->customerId;
            $oChatMessage->message = $sMessage;
            ChatManager::saveChatMessage($oChatMessage);
            $oResObj->success = true;
        }
        else {
            $oResObj->success = false;
        }
        
        if (!http_post('ajax')) {
            http_redirect(getCurrentUrl());
        }
        die(json_encode($oResObj));
        break;
    case 'ajax-update':
        $oResObj = new stdClass();
        $oResObj->closed = false;
        
        if (empty($oActiveChat)) {
            $oResObj->closed = true;
            die(json_encode($oResObj));
        }
        $aLastMessages = ChatManager::getChatMessages($oActiveChat->chatId);      
        
        if (!empty($aLastMessages)) {
            $oLastMessage = $aLastMessages[count($aLastMessages) - 1];
            $_SESSION['lastMessageId'] = $oLastMessage->chatMessageId;
        }
        
        $oResObj->topic = $oActiveChat->topic;
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
?>