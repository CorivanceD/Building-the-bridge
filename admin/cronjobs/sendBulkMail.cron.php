<?php

set_time_limit(0);
ini_set('memory_limit', '-1');

// set document root
if (!defined('DOCUMENT_ROOT')) {
    define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
}
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

# get cronjobs
$aCronResults = CommunicationItemManager::getCronjobCommunicationItems(array('isSentToApi' => 0, 'isSendingData' => 0, 'sending' => 0));

# loop through found result(s)
foreach($aCronResults as $aCronResult) {

    $iCronCommunicationItemId = $aCronResult['communicationItemId'];
    $iCronCustomerId = $aCronResult['mailingCustomerId'];
    $iCronCustomerAccountId = $aCronResult['mailingCustomerAccountId'];

    # get communication item
    $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCronCommunicationItemId);
    if(empty($oCommunicationItem)) {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, 'Non existing communicationItemId called', CLIENT_NAME . ' - Non existing communicationItemId ('.$iCronCommunicationItemId.') called for bulk mailing');
    }

    CommunicationItemManager::updateCommunicationItemCronjobReadingStatus($oCommunicationItem, 1);

    # when communicationItem is not sended yet
    if ($oCommunicationItem->sending == 0) {

        ######################### GET UNIQUE RECEIVERS ############################
        # placeholder receivers
        $aReceivers = array();
        $aUniqueReceivers = array();

        # get all linked customerGroups
        $aCustomerGroups = $oCommunicationItem->getCustomerGroups();
        foreach ($aCustomerGroups as $oCustomerGroup) {
            if (empty($oCustomerGroup)) {
                continue;
            }
            # get linked online customers, which are not bounced
            $aCustomersOfGroup = $oCustomerGroup->getClients(true, true);
            foreach ($aCustomersOfGroup as $oCustomer) {
                if (empty($oCustomer)) {
                    continue;
                }
                $aReceivers[] = $oCustomer;
            }
        }

        # placeholders
        $aEmailaddressesReceivers = array();

        # get all emailaddresses
        foreach ($aReceivers as $oReceiver) {
            # filter properties
            if (!empty($oReceiver->email) && filter_var($oReceiver->email, FILTER_VALIDATE_EMAIL) && $oReceiver->contactByEmail && $oReceiver->didSignOffForEmail == 0) {
                $aEmailaddressesReceivers[$oReceiver->customerId] = $oReceiver->email;
            }
        }

        # fetch unique emailaddresses values
        $aUniqueEmailAddresses = array_unique($aEmailaddressesReceivers);

        # sort by email
        ksort($aUniqueReceivers);

        # placeholders
        $iPartCounterBreaker = 1;
        $iMaxCustomersPerApiCall = 250;
        $iMailingPartsCounter = 0;
        $aMailingParts = array();

        # get filtered array with unique values
        # break recipients into parts because of API call
        foreach ($aUniqueEmailAddresses as $iCustomerId => $sUniqueEmailAddress) {

            # get customer
            $oUniqueReceiver = CustomerManager::getCustomerById($iCustomerId);

            # set customer in parts array
            $aMailingParts[$iMailingPartsCounter][] = $oUniqueReceiver;

            # put in array with unique values
            $aUniqueReceivers[$oUniqueReceiver->email] = $oUniqueReceiver;

            if($iPartCounterBreaker == $iMaxCustomersPerApiCall) {
                # add +1 to parts counter
                $iMailingPartsCounter++;
                # reset counter for breaking into parts
                $iPartCounterBreaker = 1;
            } else {
                # add one for each unique mail
                $iPartCounterBreaker++;
            }

        }
        
        ######################### MAKE REDIRECTS ############################
        
        # get linked articles when having so
        $aArticles = $oCommunicationItem->getIncludedArticles();
        if (!empty($aArticles)) {
            $sHeader = '<table border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            <table border="0" width="100%" cellspacing="0" cellpadding="0" align="left" bgcolor="#FFFFFF">
                                                
                                                    <tr>
                                                        <td class="responsive-image" style="padding: 0 10px;" align="left" bgcolor="#e7e4db"><a href="http://buildingthebridge.eu" target="_blank"><img style="display: block;width:290px!important;" src="http://buildingthebridge.eu/files/communication/images/header-btb-small.jpg?v1" alt="" width="290"/></a></td>
                                                    </tr>

                                                    <tr>
                                                        <td style="font-size: 0; line-height: 0;">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-size: 0; line-height: 0; border-top: 3px solid #ffffff;" bgcolor="#383838" height="30">&nbsp;</td>
                                                    </tr>                                                    
                                                    <tr>
                                                        <td style="padding: 10px 10px 20px 10px;">
                                                            <table border="0" width="100%" cellspacing="0" cellpadding="0" align="left">
                                                                    <tr>
                                                                        <td>' . $oCommunicationItem->message . '</td>
                                                                    </tr>
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>                                                
                                            </table>
                                        </td>
                                    </tr>';    
            $sArticles = '';
            foreach ($aArticles AS $oArticle) {
                $oImage = $oArticle->getImages('first-online');
                $oImageFile = null;

                if (!empty($oImage)) {
                    $oImageFile = $oImage->getImageFileByReference('detail');
                }


                $sArticles .= '<tr><td><hr style="    
    display: block; 
    height: 0px !important;
    border: 0px; 
    background: transparent;
    border-bottom: 1px solid #eee;    
    color: #eee !important;
    margin: 0px;
    padding: 0; "></td></tr>';
                $sArticles .= '<tr><td style="padding: 30px 10px 20px 10px;">';
                $sArticles .= '         <table border="0" cellspacing="0" cellpadding="0" width="100%">';
                $sArticles .= '             <tr><td><h3>' . $oArticle->title . '</h3></td></tr>';  
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>';                
                $sArticles .= '             <tr><td class="responsive-image" align="left">' . (!empty($oImageFile) ? '<img src="' . CLIENT_HTTP_URL . $oImageFile->link . '" alt="' . $oArticle->title . '" width="290" />' : '') . '</td></tr>';
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>';
                $sArticles .= '             <tr><td><p>' . firstXCharacters($oArticle->intro, 181) . '...</td></tr>';
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>';                
                $sArticles .= '             <tr><td><table>'
                        . '                     <tr><td style="background: #208ed3; padding: 4px;">'
                        . '                         <div class="readmore">'
                        . '                             <a style="color: #ffffff; text-decoration: none;" href="' . CLIENT_HTTP_URL . $oArticle->getUrlPath() . '" target="_blank">READ MORE</a>'
                        . '                         </div></td></tr>'
                        . '                     </table></td></tr>'
                        . '             </table></td></tr>';                
            }
            $sFooter = '
                            <tr>
                                <td class="responsive-image">&nbsp;</td>
                           </tr>
                                <tr>
                                <td class="responsive-image" style="padding: 0 10px 0 10px; color: #ffffff;" align="left" bgcolor="#eeece3" height="30">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                <td style="color: #ffffff;">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0" class="responsive-image-footericons">
                                <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top"><strong><span style="font-family: Arial, Verdana, sans-serif; font-size: 15px; color: #444444;">Building the Bridge</span></strong></td>
                                <td>&nbsp;</td>
                                <td rowspan="3" align="right"><a style="text-decoration: none;" href="http://www.linkedin.com/company/building-the-bridge" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-linkedin.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-facebook.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="https://twitter.com/abraham9333" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-twitter.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="http://buildingthebridge.eu/rss" target="_blank"><img src="http://buildingthebridge.eu/files/communication/images/footer-icon-rss.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; </td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="top">&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">
                                <p style="font-size: 12px; font-family: Arial, Verdana, sans-serif; color: #ffffff;"><a style="color: #444444;" href="mailto:info@buildingthebridge.eu">info@buildingthebridge.eu</a></p>
                                </td>
                                <td align="left" valign="top">&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="middle">&nbsp;</td>
                                </tr>
                                </table>
                                </td>
                                </tr>
                                </table>
                                </td>
                                </tr>
                                </table>';
            $sMessageCombined = $sHeader . $sArticles . $sFooter;
            $oCommunicationItem->message = $sMessageCombined;
            # save message in database
            CommunicationItemManager::updateCommunicationItemMessage($oCommunicationItem);
        }
        
        
        # make new DOM document
        $oDom = new DOMDocument();
        # load communication message into DOM document
        $oDom->loadHTML($oCommunicationItem->message);

        //Evaluate Anchor tag in HTML
        $oXpath = new DOMXPath($oDom);
        $aHrefs = $oXpath->evaluate("//a[not(@class=\"no-redirect\")]");
        for ($i = 0; $i < $aHrefs->length; $i++) {

            # get link instance
            $oHref = $aHrefs->item($i);

            # loop through attributes of element
            foreach ($oHref->attributes as $oAttr) {
                # get actual link
                if($oAttr->name == 'href' && !is_null($oAttr->value)) {
                    # skip links with "mail:" & "tel:" in it.
                    if(strstr($oAttr->value, "mailto:") === false && strstr($oAttr->value, "tel:") === false) {
                        # make new redirect link
                        $oCommunicationItemLinkRedirect = new CommunicationItemLinkRedirect();
                        $oCommunicationItemLinkRedirect->communicationItemId = $oCommunicationItem->communicationItemId;
                        # get original link
                        if($oAttr->value != '') {
                            $oCommunicationItemLinkRedirect->originalUrl = $oAttr->value;
                        }
                        # create unique hash
                        $oCommunicationItemLinkRedirect->redirectHash = CommunicationItemLinkRedirect::generateLinkRedirectHashByCommunicationItemId($oCommunicationItem->communicationItemId);
                        # save it
                        CommunicationItemLinkRedirectManager::saveCommunicationItemLinkRedirect($oCommunicationItemLinkRedirect);

                        # remove attribute from HTML
                        $oHref->removeAttribute('href');

                        # set new redirect URL
                        $oHref->setAttribute("href", $oCommunicationItemLinkRedirect->returnFullLinkRedirect());
                        $oHref->setAttribute("class", 'redirect-link');

                    }
                }
            }

        }
        
        # update communicationItem message with new URL's. Use urldecode on $oDom->saveHTML() for keeping UTF-8 tags, no other option available at the moment
        $oCommunicationItem->messageWithRedirectUrls = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), urldecode($oDom->saveHTML())));
        CommunicationItemManager::updateCommunicationItemRedirectUrlVersion($oCommunicationItem);

        # save this version of message for customer replacing beneath
        $oCommunicationItemWithHashedUrls = $oCommunicationItem;

        ######################### MAKE REDIRECTS PER RECIPIENT ############################

        # get CSS
        $sCss = $oCommunicationItemWithHashedUrls->getCssHeader();
        
        # mark message as pending
        CommunicationItemManager::updateCommunicationItemPendingStatus($oCommunicationItem, 1);

        # placeholder
        $iCounterTotalEmails = 0;

        # keeps track of amount of parts generated
        $iMailingPartCounter = 0;

        # loop through parts
        foreach($aMailingParts as $aMailingPart) {

            # updates mailing part counter
            $iMailingPartCounter++;

            # placeholders
            $iLoopPartCounter = 0;
            $aReceiversData = array();

            # get mailingparts and send to API
            foreach($aMailingPart as $iPart => $oCustomer) {

                # get the base hashed version of the message
                $oCommunicationItem = clone $oCommunicationItemWithHashedUrls;

                # replace custom variables
                $oCommunicationItem->replaceVariables($oCustomer, array('[customer_fullName]', '[customer_email]'));

                # get message, hashed version
                $sMessage = $oCommunicationItem->getMessage(true, true, $oCustomer);
                
                # make new DOM document
                $oDom = new DOMDocument();
                # load communication message into DOM document
                $sHtmlFormat = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
                $sHtmlFormat .= '<html>';
                    $sHtmlFormat .= '<head>';
                        $sHtmlFormat .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                        $sHtmlFormat .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
                        $sHtmlFormat .= $sCss;
                    $sHtmlFormat .= '</head>';
                    $sHtmlFormat .= '<body>';
                        $sHtmlFormat .= $sMessage;
                        $sHtmlFormat .= $oCommunicationItem->addConversionPixel($oCommunicationItem, $oCustomer);
                    $sHtmlFormat .= '</body>';
                $sHtmlFormat .= '</html>';
                @$oDom->loadHTML($sHtmlFormat);

                //Evaluate Anchor tag in HTML
                $oXpath = new DOMXPath($oDom);
                $aHrefs = $oXpath->evaluate('//a[contains(concat(" ", normalize-space(@class), " "), " redirect-link ")]');
                for ($i = 0; $i < $aHrefs->length; $i++) {

                    # get link instance
                    $oHref = $aHrefs->item($i);

                    # loop through attributes of element
                    foreach ($oHref->attributes as $oAttr) {
                        # get actual link
                        if($oAttr->name == 'href' && !is_null($oAttr->value)) {
                            # skip links with "mail:" & "tel:" in it.
                            if(strstr($oAttr->value, "mailto:") === false && strstr($oAttr->value, "tel:") === false) {

                                # get hashed value
                                $sHashedUrl = $oAttr->value;

                                # remove attribute from HTML
                                $oHref->removeAttribute("href");
                                # set new redirect URL with customerId attachted
                                $oHref->setAttribute("href", $sHashedUrl . '/' .$oCustomer->customerId);

                            }
                        }
                    }

                }

                # update communicationItem message with new URL's
                $sPersonalisedMessage = $oDom->saveHTML();
                
                # collect data from user
                $aReceiversData[] = array(
                    'emailTo'    => $oCustomer->email,
                    'subject'    => $oCommunicationItem->subject,
                    'email'      => $sPersonalisedMessage,
                    'fromEmail'  => $oCommunicationItem->senderName . ' <' . $oCommunicationItem->senderEmail . '>',
                    'replyEmail' => $oCommunicationItem->senderName . ' <' . $oCommunicationItem->senderEmail . '>'
                 );

                # add one to counterEmail
                $iCounterTotalEmails++;

                # add one to partCounter
                $iLoopPartCounter++;

                # call API with data when max customers reached, or of the end of the list
                if($iLoopPartCounter == $iMaxCustomersPerApiCall || count($aUniqueReceivers) == $iCounterTotalEmails) {

                    # make JSON template and save customer data before sending
                    $aData = 
                    array(
                        'action' => 'savemails',
                        'communicationItemId' => $iCronCommunicationItemId,
                        'customerId' => $iCronCustomerId,
                        'splitBatchNumber' => $iMailingPartCounter,
                        'customerAccountId' => $iCronCustomerAccountId,
                        'token' => substr(hash('sha512', '22-bridgeBuilder!2015'), 0, 50), // hash from customerId + '-' sign + prefix from mailserver
                        'recipients' => $aReceiversData
                    );

                    $sJsonData = json_encode($aData);
                    if($sJsonData === false) {
                        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' has not sent JSON content while sending bulk mailing', CLIENT_NAME . ' has not sent JSON content while sending bulk mailing');
                    }

                    # set cUrl settings
                    $ch = curl_init(COMMUNICATION_SENDBULK_EMAIL_URL);                                                                      
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $sJsonData);                                                                  
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                        'Content-Type: application/json',
                        'Content-Length: ' . mb_strlen($sJsonData)
                        )
                    );

                    # execute cUrl call
                    $result = curl_exec($ch);

                    # error send wrong/incomplete data
                    if($result == 0) {
                        CommunicationItemManager::markCommunicationItemAsHavingBulkSendingError($oCommunicationItem);
                        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-errormessage : incorrect_data', 'bulk-errormessage : incorrect_data');
                    }
                    # error send wrong/incomplete token
                    elseif($result == 1) {
                        CommunicationItemManager::markCommunicationItemAsHavingBulkSendingError($oCommunicationItem);
                        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-errormessage : incorrect_token', 'bulk-errormessage : incorrect_token');
                    }
                    # error offline customer
                    elseif($result == 2) {
                        CommunicationItemManager::markCommunicationItemAsHavingBulkSendingError($oCommunicationItem);
                        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-errormessage : offline-customer', 'bulk-errormessage : offline-customer');
                    }
                    # error offline customer account
                    elseif($result == 3) {
                        CommunicationItemManager::markCommunicationItemAsHavingBulkSendingError($oCommunicationItem);
                        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-errormessage : offline-customeraccount', 'bulk-errormessage: offline-customeraccount');
                    }
                    # success
                    elseif($result == 4 && count($aUniqueReceivers) == $iCounterTotalEmails) {

                        # mark message as sending to API (doesnt matter it is doing it each time)
                        CommunicationItemManager::updateCommunicationItemApiSendingStatus($oCommunicationItem, 1);

                        # make JSON template and give command to sent mail
                        $aData = 
                        array(
                            'action' => 'sendmail',
                            'communicationItemId' => $iCronCommunicationItemId,
                            'customerId' => $iCronCustomerId,
                            'customerAccountId' => $iCronCustomerAccountId,
                            'mailHeaders' => array('X-COMMUNICATION-ID' => $oCommunicationItem->communicationItemId, 'X-COMMUNICATION-CLIENTNAME' => CLIENT_NAME),
                            'token' => substr(hash('sha512', '22-bridgeBuilder!2015'), 0, 50) // hash from customerId + '-' sign + prefix from mailserver
                        );

                        $sJsonData = json_encode($aData);
                        if($sJsonData === false) {
                            MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' has not sent JSON content while sending bulk mailing', CLIENT_NAME . ' has not sent JSON content while sending bulk mailing');
                        }

                        # set cUrl settings
                        $ch = curl_init(COMMUNICATION_SENDBULK_EMAIL_URL);                                                                      
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $sJsonData);                                                                  
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                            'Content-Type: application/json',
                            'Content-Length: ' . mb_strlen($sJsonData)
                            )
                        );

                        # mark message as sending (doesnt matter it is doing it each time)
                        CommunicationItemManager::markCommunicationItemAsSending($oCommunicationItem);

                        # execute cUrl call
                        $result = curl_exec($ch);

                        # when everything is sent
                        if($result == 1) {

                            MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' has sent a bulk message', CLIENT_NAME . ' has sent a bulk message');

                            # mark message as sended
                            CommunicationItemManager::markCommunicationItemAsSended($oCommunicationItem);

                            # remove cronjob from list
                            CommunicationItemManager::deleteCronjobFromCronjoblist($oCommunicationItem);

                            # save reference customer received mail
                            foreach($aUniqueReceivers as $oCustomer) {
                                CommunicationItemManager::saveCommunicationItemLog($oCustomer, $oCommunicationItem);
                            }

                        } else {
                            CommunicationItemManager::markCommunicationItemAsHavingBulkSendingError($oCommunicationItem);
                            MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' ERROR in sending bulk message', CLIENT_NAME . ' ERROR in sending bulk message');
                        }

                    } elseif($result == 4) {

                        # mark message as sending to API (doesnt matter it is doing it each time)
                        CommunicationItemManager::updateCommunicationItemApiSendingStatus($oCommunicationItem, 1);
                    }

                }

            }

        }

    }
    else {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME .  ' has got a error', CLIENT_NAME .  ' received: The mailing is sending, please wait');
        die('The mailing is sending, please wait');

    }

}
?>