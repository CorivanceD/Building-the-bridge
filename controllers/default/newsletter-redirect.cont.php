<?php

# this controller is used for redirecting communicationItem redirect links
if(http_get('param1') != '') {
    $aExplodedLink = explode('-', http_get('param1'));
    if(isset($aExplodedLink[0]) && isset($aExplodedLink[1])) {
        $sHash = $aExplodedLink[0] . '-' . $aExplodedLink[1];
        $iCommunicationItemId = $aExplodedLink[1];
        $iCustomerId = http_get('param2');
        # get redirect link
        if(is_numeric($iCommunicationItemId) && is_numeric($iCustomerId)) {
            $oRedirectLink = CommunicationItemLinkRedirectManager::getCommunicationItemLinkRedirectByHashAndCommunicationItemId($sHash, $iCommunicationItemId);
        }
    }
        
    if(!empty($oRedirectLink)) {
        
        $oCustomer = CustomerManager::getCustomerById($iCustomerId);
        if(empty($oCustomer)) {
            die('We are sorry, you are no longer in our mailinglist. Therefore, you will not be able to visit this link.');
        }
        
        $oCommunicationItemLinkRedirectStat = new CommunicationItemLinkRedirectStat();
        $oCommunicationItemLinkRedirectStat->communicationItemId = $iCommunicationItemId;
        $oCommunicationItemLinkRedirectStat->customerId = $oCustomer->customerId;
        $oCommunicationItemLinkRedirectStat->name = $oCustomer->getFullName();
        $oCommunicationItemLinkRedirectStat->email = $oCustomer->email;
        $oCommunicationItemLinkRedirectStat->communicationItemLinkRedirectId = $oRedirectLink->communicationItemLinkRedirectId;
        
        # save statistics for a customer
        CommunicationItemLinkRedirectStatManager::saveCommunicationItemLinkRedirectStat($oCommunicationItemLinkRedirectStat);
        
        # redirect customer
        http_redirect($oRedirectLink->originalUrl);
        
    } else {
        if(isset($_SERVER['REMOTE_ADDR'])) {
            $sExtra = ' from ' . $_SERVER['REMOTE_ADDR'] . '<br>' . _d($_GET, true, true) . '<br>' . _d($_SERVER, true, true);
        } else {
            $sExtra = '';
        }
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, 'Broken stats link', 'Check ' . CLIENT_HTTP_URL . ' redirects' . $sExtra);
        //die('We are sorry, an error occurred due a non-existing link.');
        http_redirect(CLIENT_HTTPS_URL);
        
    }
}

?>