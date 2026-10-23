<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# reset crop settings
unset($_SESSION['aCropSettings']);

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('Communicatie beheer');
$oPageLayout->sModuleName = sysTranslations::get('Communicatie beheer');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# get corresponding items for template type
if (http_get("param1") == 'ajax-getTemplatesByType') {

    $sTemplateType = http_get("param2");
    switch ($sTemplateType) {
        case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
            $iTemplateGroupId = CommunicationItem::COMMUNICATION_TEMPLATEGROUPID_EMAIL;
            break;
        case CommunicationItem::COMMUNICATION_TYPE_SMS:
            $iTemplateGroupId = CommunicationItem::COMMUNICATION_TEMPLATEGROUPID_SMS;
            break;
    }
    $aTemplates = TemplateManager::getTemplatesByFilter(array('type' => $sTemplateType, 'templateGroupId' => $iTemplateGroupId));
    $sTemplateOptions = '';

    //standard class for json feedback
    $oResObj = new stdClass();
    $oResObj->hasOptions = false;

    $sTemplateOptions .= '<option value="">-- ' . sysTranslations::get('communication_no_template') . ' --</option>';
    foreach ($aTemplates as $oTemplate) {
        $sTemplateOptions .= '<option value="' . $oTemplate->templateId . '" data-templategroupid="' . $oTemplate->templateGroupId . '">' . _e($oTemplate->name) . '</option>';
        $oResObj->hasOptions = true;
    }

    # get options
    $oResObj->htmlOptions = $sTemplateOptions;

    print json_encode($oResObj);
    die;
}

# get corresponding data by template
if (http_get("param1") == 'ajax-getTemplateData') {

    //standard class for json feedback
    $oResObj = new stdClass();

    $iTemplateId = http_get("param2");
    $oTemplate = TemplateManager::getTemplateById($iTemplateId);

    # check whether template is found
    if (!empty($oTemplate)) {
        $oResObj->success = true;
        $oResObj->title = $oTemplate->name;
        $oResObj->subject = $oTemplate->subject;
        $oResObj->senderName = CLIENT_NAME;
        $oResObj->senderEmail = COMMUNICATION_CLIENT_EMAIL_FROM;
        $oResObj->message = $oTemplate->template;
    } else {
        $oResObj->success = false;
    }
    print json_encode($oResObj);
    die;
}

# get sending progress
if (http_get("param1") == 'ajax-getSendingProgress' && is_numeric(http_get("param2"))) {

    //standard class for json feedback
    $oResObj = new stdClass();

    $iCommunicationItemId = http_get("param2");
    $oResObj->receiverAmount = CommunicationItemManager::getTotalAmountOfCustomersFromSendlistByCommunicationId($iCommunicationItemId);
    $oResObj->sendedReceiversAmount = CommunicationItemManager::getTotalAmountOfCustomersFromSendlistByCommunicationId($iCommunicationItemId, true);

    print json_encode($oResObj);
    die;
}

# gets message for preview
if (http_get("param1") == 'ajax-getMessage') {

    $iCommunicationItemId = http_get('communicationItemId');
    if (!empty($iCommunicationItemId)) {
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);
        if (!empty($oCommunicationItem)) {
            $sMessage = $oCommunicationItem->getCssHeader();
            if(!$oCommunicationItem->sended) {
                $oCommunicationItem->replaceVariables(null);
            }
            $sMessage .= $oCommunicationItem->getMessage();
            echo $sMessage;
            die;
        }
    }
}

# SEND MESSAGE
if (http_get("param1") == 'ajax-sendMessage') {

    # used for json feedback
    $oResObj = new stdClass();
    $oResObj->success = true;
    $oResObj->errorMsg = '';

    # placeholders
    $bNeedReplacement = false;
    $bHasSendError = false;

    # get communication item
    $iCommunicationItemId = http_post('iCommunicationItemId');
    $iCustomerId = http_post('iCustomerId');

    # sending verification?
    $bSendVerification = http_post('bIsVerification');

    if (!is_numeric($iCustomerId)) {
        $oResObj->success = false;
        $oResObj->errorMsg = sysTranslations::get('communication_invalid_user');
        $bHasSendError = true;
    }
    if (!is_numeric($iCommunicationItemId)) {
        $oResObj->success = false;
        $oResObj->errorMsg = sysTranslations::get('communication_invalid_message');
        $bHasSendError = true;
    }

    # mark message as send
    if ($bHasSendError === false) {

        # fetch original message
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);

        # get customer
        $oCustomer = CustomerManager::getCustomerById($iCustomerId);

        # default value for replacement
        $bNeedReplacement = false;

        # placeholder
        $aCssStyles = array();
        
        # check whether replacement of values is needed
        if (!empty($oCommunicationItem->templateId) && is_numeric($oCommunicationItem->templateId)) {
            $oTemplate = TemplateManager::getTemplateById($oCommunicationItem->templateId);
            if (!empty($oTemplate)) {
                $oTemplateGroup = TemplateManager::getTemplateGroupById($oTemplate->templateGroupId);
                # overwrite default value
                $bNeedReplacement = !empty($oTemplateGroup) ? true : false;
            }
        }

        # SEND EMAIL
        if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {

            # fetch original message for replace tags
            $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);

            # when linked to template, replace vars are available
            if ($bNeedReplacement) {
                $oCommunicationItem->replaceVariables($oCustomer, array('[customer_fullName]', '[customer_email]'));
            }

            $sMessage = '';

            # add stats logging
            if (!$bSendVerification) {

                # get message, hashed version
                $sMessage .= $oCommunicationItem->getMessage(true, true, $oCustomer);
                
                # make new DOM document
                $oDom = new DOMDocument();
                # load communication message into DOM document
                $sHtmlFormat = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
                $sHtmlFormat .= '<html>';
                    $sHtmlFormat .= '<head>';
                        $sHtmlFormat .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                        $sHtmlFormat .= $oCommunicationItem->getCssHeader();
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
                $sMessage = $oDom->saveHTML();
                
                # parameter for MailManager, do NOT add html tags because of $sHtmlFormat defined which has HTML tags
                $bAddHtmlTags = false;
                
            } else {
                
                # get message, unhashed version
                $sMessage .= $oCommunicationItem->getMessage(false, true, $oCustomer);
                
                # parameter for MailManager, add html tags
                $bAddHtmlTags = true;
                
                # add CSS to mailheaders
                $aCssStyles[] = $oCommunicationItem->getCssHeader();
                
            }
            
            # set subject encoding
            mb_internal_encoding('UTF-8');
            $sSubject = mb_encode_mimeheader($oCommunicationItem->subject, "UTF-8", "B");

            # send mail
            $bResult = MailManager::sendMail($oCustomer->email, ($bSendVerification ? sysTranslations::get('communication_subject_verification') . ' - ' : '') . $sSubject, $sMessage, $oCommunicationItem->senderName . ' <' . $oCommunicationItem->senderEmail . '>', $oCommunicationItem->senderName . ' <' . $oCommunicationItem->senderEmail . '>', null, null, null, null, array(), true, array('X-COMMUNICATION-ID' => $iCommunicationItemId, 'X-COMMUNICATION-CLIENTNAME' => CLIENT_NAME), $bAddHtmlTags, $aCssStyles);

            # log communication/customer link
            if ($bResult === true) {
                # mark the included articles in the communication item as sent
                $oCommunicationItem->setIncludedArticlesAsSent();

                # when not verification, log data
                if (!$bSendVerification) {

                    CommunicationItemManager::saveCommunicationItemLog($oCustomer, $oCommunicationItem);
                    CommunicationItemManager::markCustomerAsHasReceivedMessage($oCommunicationItem, $oCustomer);

                    # wait for half a second
                    usleep(CommunicationItem::MS_SLEEP_SEND_EMAIL);
                }
            } else {
                $oResObj->success = false;
                $oResObj->errorMsg = sysTranslations::get('communication_email_not_sent');
            }
        }

        # SEND SMS
        elseif ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {

            # fetch original message for replace tags
            $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);

            # when linked to template, replace vars are available
            if ($bNeedReplacement) {
                # replace template vars + define keys which will not be encoded
                $oCommunicationItem->replaceVariables($oCustomer, array('[customer_fullName]'));
                $sMessage = $oCommunicationItem->message;
            }
            # send raw message
            else {
                $sMessage = $oCommunicationItem->message;
            }

            # send amount of messages
            $oCommunicationItem->setSmsMessagePartsAmount();

            # set SMS properties
            $oSmsManager = new MessageBird(COMMUNICATION_API_USERNAME, COMMUNICATION_API_PASSWORD);
            $oSmsManager->setSender($oCommunicationItem->senderName);
            $oSmsManager->setResponseType(COMMUNICATION_API_RESPONSETYPE);
            $oSmsManager->addDestination($oCustomer->mobilePhone);
            # set boolean to true when not to subtract credits when testing
            $oSmsManager->setTest(false);
            $oSmsManager->sendSms($sMessage);

            # check if sms is send
            $bResult = $oSmsManager->getResponseCode() == '01' ? true : false;

            # log communication/customer link
            if ($bResult === true) {
                CommunicationItemManager::saveCommunicationItemLog($oCustomer, $oCommunicationItem);
                CommunicationItemManager::markCustomerAsHasReceivedMessage($oCommunicationItem, $oCustomer);
            } else {
                $oResObj->success = false;
                $oResObj->errorMsg = sysTranslations::get('communication_sms_not_sent') . ' "' . $oSmsManager->getResponseMessage() . '"';
            }
        } else {
            die(sysTranslations::get('communication_unknown_message_type'));
        }
    }

    # get customerId
    $oResObj->customerId = $iCustomerId;

    # return feedback
    print json_encode($oResObj);
    die;
}

// filter articles in communication item form
if (http_get('param1') === 'ajax-filterArticles') {

    $aArticleFilter = http_post('newsletterItemFilter');
    $aArticleFilter['status'] = Article::ARTICLE_STATUS_PUBLISHED;
    $_SESSION['newsletterItemFilter'] = $aArticleFilter;
    $iFoundRows = 0;
    $iPerPage = http_session('newsletterItemsPerPage', 10);
    $iCurrPage = 1;
    $iStart = 0;
    
    if (isset($aArticleFilter['onlyLetters'])) {
        $aArticleFilter['articleCategoryId'] = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
    }
    elseif ($aArticleFilter['articleCategoryId'] === ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY) { 
        $aArticleFilter['articleCategoryId'] = '';
    }
        
    $aArticles = ArticleManager::getArticlesByFilter($aArticleFilter, $iPerPage, $iStart, $iFoundRows, array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC'));
    $sArticleTable = '';
    $bOdd = true;

    foreach ($aArticles AS $oArticle) {
        $sArticleTable .= '<tr class="' . ($bOdd ? 'odd' : 'even') . '"><td>'
                . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td><td>'
                . ($oArticle->getWorldLeaderLetter() == null ? 'Art.' : 'Let.') . '</td><td>'
                . $oArticle->title . '</td><td>'
                . $oArticle->getAuthor()->name . '</td><td style="text-align:center">'
                . '<input disabled type="checkbox" ' . (!$oArticle->wasAlreadySent() ? '' : 'checked') . '></td><td style="text-align:center">'
                . '<input type="checkbox" value="' . $oArticle->articleId . '" class="includeArticle" ' . ($oArticle->isIncludedInNewsletter($aArticleFilter['communicationItemId']) ? 'checked' : '') . '></td><td>'
                . '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a></td></tr>';
        $bOdd != $bOdd;
    }

    if (empty($sArticleTable)) {
        $sArticleTable = '<tr><td colspan="6"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
    }

    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oResObj = new stdClass();
    $oResObj->articleTable = $sArticleTable;
    $oResObj->pagination = generatePaginationHTML($iPageCount, 1);
    die(json_encode($oResObj));
}

if (http_get('param1') === 'ajax-resetArticleFilter' && is_numeric(http_post('communicationItemId'))) {
    unset($_SESSION['newsletterItemFilter']);
    $aArticleFilter = array();
    $aArticleFilter['q'] = '';
    $aArticleFilter['articleCategoryId'] = '';
    unset($aArticleFilter['onlyLetters']);
    $aArticleFilter['authorId'] = '';
    $aArticleFilter['usedBefore'] = '';
    $aArticleFilter['communicationItemId'] = http_post('communicationItemId');
    $aArticleFilter['status'] = Article::ARTICLE_STATUS_PUBLISHED;
    $_SESSION['newsletterItemFilter'] = $aArticleFilter;
    $iFoundRows = 0;
    $iPerPage = http_session('newsletterItemPerPage', 10);
    $iCurrPage = 1;
    $iStart = 0;
    $aArticles = ArticleManager::getArticlesByFilter($aArticleFilter, $iPerPage, $iStart, $iFoundRows, array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC'));
    $sArticleTable = '';
    $bOdd = true;

    foreach ($aArticles AS $oArticle) {
        $sArticleTable .= '<tr class="' . ($bOdd ? 'odd' : 'even') . '"><td>'
                . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td><td>'
                . ($oArticle->getWorldLeaderLetter() == null ? 'Art.' : 'Let.') . '</td><td>'
                . $oArticle->title . '</td><td>'
                . $oArticle->getAuthor()->name . '</td><td style="text-align:center">'
                . '<input disabled type="checkbox" ' . (!$oArticle->wasAlreadySent() ? '' : 'checked') . '></td><td style="text-align:center">'
                . '<input type="checkbox" value="' . $oArticle->articleId . '" class="includeArticle" ' . ($oArticle->isIncludedInNewsletter($aArticleFilter['communicationItemId']) ? 'checked' : '') . '></td><td>'
                . '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a></td></tr>';
        $bOdd != $bOdd;
    }

    if (empty($sArticleTable)) {
        $sArticleTable = '<tr><td colspan="6"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
    }

    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oResObj = new stdClass();
    $oResObj->articleTable = $sArticleTable;
    $oResObj->pagination = generatePaginationHTML($iPageCount, 1);
    die(json_encode($oResObj));
}



# set articles per page

if (http_get('param1') == 'ajax-setArticlesPerPage') {
    // handle perPage
    $_SESSION['newsletterItemsPerPage'] = http_post('perPage', 0);
    $iFoundRows = 0;
    $iPerPage = http_session('newsletterItemsPerPage', 10);
    $iCurrPage = 1;
    $iStart = 0;
    $aArticleFilter = http_session('newsletterItemFilter');
    $aArticles = ArticleManager::getArticlesByFilter(http_session('newsletterItemFilter'), $iPerPage, $iStart, $iFoundRows, array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC'));
    $sArticleTable = '';
    $bOdd = true;

    foreach ($aArticles AS $oArticle) {
        $sArticleTable .= '<tr class="' . ($bOdd ? 'odd' : 'even') . '"><td>'
                . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td><td>'
                . ($oArticle->getWorldLeaderLetter() == null ? 'Art.' : 'Let.') . '</td><td>'                
                . $oArticle->title . '</td><td>'
                . $oArticle->getAuthor()->name . '</td><td style="text-align:center">'
                . '<input disabled type="checkbox" ' . (!$oArticle->wasAlreadySent() ? '' : 'checked') . '></td><td style="text-align:center">'
                . '<input type="checkbox" value="' . $oArticle->articleId . '" class="includeArticle" ' . ($oArticle->isIncludedInNewsletter(http_post('communicationItemId')) ? 'checked' : '') . '></td><td>'
                . '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a></td></tr>';
        $bOdd != $bOdd;
    }

    if (empty($sArticleTable)) {
        $sArticleTable = '<tr><td colspan="6"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
    }

    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oResObj = new stdClass();
    $oResObj->articleTable = $sArticleTable;
    $oResObj->pagination = generatePaginationHTML($iPageCount, 1);
    die(json_encode($oResObj));
}

# set articles page

if (http_get('param1') == 'ajax-setArticlesPage' && is_numeric(http_post('page'))) {
    // handle perPage

    $iFoundRows = 0;
    $iPerPage = http_session('newsletterItemsPerPage', 10);
    $iCurrPage = http_post('page');
    $_SESSION['page'] = $iCurrPage;
    $iStart = (($iCurrPage - 1) * $iPerPage);
    $aArticleFilter = http_session('newsletterItemFilter');
    $aArticles = ArticleManager::getArticlesByFilter(http_session('newsletterItemFilter'), $iPerPage, $iStart, $iFoundRows, array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC'));
    $sArticleTable = '';
    $bOdd = true;

    foreach ($aArticles AS $oArticle) {
        $sArticleTable .= '<tr class="' . ($bOdd ? 'odd' : 'even') . '"><td>'
                . Date::strToDate($oArticle->pubDate)->format('%d-%m-%Y') . '</td><td>'
                . ($oArticle->getWorldLeaderLetter() == null ? 'Art.' : 'Let.') . '</td><td>'                
                . $oArticle->title . '</td><td>'
                . $oArticle->getAuthor()->name . '</td><td style="text-align:center">'
                . '<input disabled type="checkbox" ' . (!$oArticle->wasAlreadySent() ? '' : 'checked') . '></td><td style="text-align:center">'
                . '<input type="checkbox" value="' . $oArticle->articleId . '" class="includeArticle" ' . ($oArticle->isIncludedInNewsletter(http_post('communicationItemId')) ? 'checked' : '') . '></td><td>'
                . '<a class="action_icon magnifying_glass_icon" title="' . sysTranslations::get('articles_review') . '" href="' . $oArticle->getUrlPath() . '" target="_blank"></a></td></tr>';
        $bOdd != $bOdd;
    }

    if (empty($sArticleTable)) {
        $sArticleTable = '<tr><td colspan="6"><i>' . sysTranslations::get('articles_no_articles') . '</i></td></tr>';
    }

    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oResObj = new stdClass();
    $oResObj->articleTable = $sArticleTable;
    $oResObj->pagination = generatePaginationHTML($iPageCount, $iCurrPage);
    die(json_encode($oResObj));
}

# add/remove article to communication item

if (http_get('param1') == 'ajax-includeArticle' && is_numeric(http_post('articleId')) && is_numeric(http_post('communicationItemId'))) {
    $oResObj = new stdClass();

    if (CommunicationItemManager::addArticleToCommunicationItem(http_post('articleId'), http_post('communicationItemId'), intval(http_post('include', 0)))) {
        $oResObj->statusUpdate = intval(http_post('include', 0)) ? 'Article added to the item' : 'Article removed from the item';
    } else {
        $oResObj->statusUpdate = 'Article couldn\'t be added/removed';
    }

    die(json_encode($oResObj));
}

if(http_post('action') == 'sendTrusted') {
    
    # get communication item
    $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get('param2'));
    
    if(empty($oCommunicationItem)) {
        $_SESSION['statusUpdate'] = 'Bericht is niet verstuurbaar';
        http_redirect(getCurrentUrlPath());
    }

    # when communicationItem is not sended yet
    if ($oCommunicationItem->sending == 0) {
        
        $bResult = CommunicationItemManager::setCommunicationItemInCron($oCommunicationItem, COMMUNICATION_SENDBULK_CUSTOMER_ID, COMMUNICATION_SENDBULK_CUSTOMERACCOUNT_ID, 0);
        if($bResult !== true) {
            Debug::logError(0, 'Could not set communicationItem in cronlist', __FILE__, __LINE__, CLIENT_NAME, Debug::LOG_IN_EMAIL);
        }
        
        # set communication item in cron table
        CommunicationItemManager::updateCommunicationItemPendingStatus($oCommunicationItem, 1);
        
        # send back to overview
        $_SESSION['statusUpdate'] = 'Bericht wordt verstuurd naar mailserver'; //save status update into session
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        
    }
    else {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME .  ' has got a error', CLIENT_NAME .  ' received: The mailing is sending, please wait');
        # send back to overview
        $_SESSION['statusUpdate'] = 'De mail wordt al verzonden'; //save status update into session
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        
    }
}

# mark message as "send"
if (http_get("param1") == 'ajax-markMessageAsComplete') {

    # used for json feedback
    $oResObj = new stdClass();
    $oResObj->success = true;
    $oResObj->errorMsg = '';

    $bHasError = false;

    # get communication item
    $iCommunicationItemId = http_post('iCommunicationItemId');
    if (!is_numeric($iCommunicationItemId)) {
        $bHasError = true;
    }

    # mark message as send
    if ($bHasError === true) {
        $oResObj->success = false;
    } else {
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);
        CommunicationItemManager::markCommunicationItemAsSended($oCommunicationItem);
        MailManager::sendMail('rick@a-side.nl', CLIENT_NAME . ' heeft via de communicatiemodule een bericht verstuurd', CLIENT_NAME . ' heeft via de communicatiemodule een bericht verstuurd');
    }

    # return feedback
    print json_encode($oResObj);
    die;
}

# mark message as "sending" & collect unique customers
if (http_get("param1") == 'ajax-markMessageAsSending') {

    # used for json feedback
    $oResObj = new stdClass();
    $oResObj->success = true;
    $oResObj->errorMsg = '';

    $bHasError = false;

    # get communication item
    $iCommunicationItemId = http_post('iCommunicationItemId');
    if (!is_numeric($iCommunicationItemId)) {
        $bHasError = true;
    }

    # mark message as send
    if ($bHasError === true) {
        $oResObj->success = false;
    } else {
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById($iCommunicationItemId);
        # when communicationItem is not sended yet
        if ($oCommunicationItem->sending == 0) {

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

            # get emailaddresses and send e-mail
            if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {

                # placeholders
                $aEmailaddressesReceivers = array();
                $aUniqueEmailAddresses = array();

                # get all emailaddresses
                foreach ($aReceivers as $oReceiver) {
                    # filter properties
                    if (!empty($oReceiver->email) && filter_var($oReceiver->email, FILTER_VALIDATE_EMAIL) && $oReceiver->contactByEmail) {
                        $aEmailaddressesReceivers[$oReceiver->customerId] = $oReceiver->email;
                    }
                }

                # fetch unique emailaddresses values
                $aUniqueEmailAddresses = array_unique($aEmailaddressesReceivers);

                # get object and put in array with unique values
                foreach ($aUniqueEmailAddresses as $iCustomerId => $sUniqueEmailAddress) {
                    # get customer
                    $oCustomer = CustomerManager::getCustomerById($iCustomerId);
                    # put in array with unique values
                    $aUniqueReceivers[$oCustomer->email] = $oCustomer;
                }

                # sort by email
                ksort($aUniqueReceivers);
                
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
                
            }
            # get mobile phonenumbers and send SMS
            elseif ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {

                # placeholders
                $aMobilePhonenumbersReceivers = array();
                $aUniqueMobilePhonenumbers = array();

                # get all emailaddresses
                foreach ($aReceivers as $oReceiver) {
                    # filter properties
                    if (!empty($oReceiver->mobilePhone) && $oReceiver->contactBySms) {
                        $aMobilePhonenumbersReceivers[$oReceiver->customerId] = $oReceiver->mobilePhone;
                    }
                }

                # fetch unique emailaddresses values
                $aUniqueMobilePhonenumbers = array_unique($aMobilePhonenumbersReceivers);

                # get object and put in array with unique values
                foreach ($aUniqueMobilePhonenumbers as $iCustomerId => $sUniqueMobilePhoneNumber) {
                    # get customer
                    $oCustomer = CustomerManager::getCustomerById($iCustomerId);
                    # put in array with unique values
                    $aUniqueReceivers[$oCustomer->mobilePhone] = $oCustomer;
                }

                # sort by phonenumber
                ksort($aUniqueReceivers);
            }

            # set receivers
            CommunicationItemManager::saveCommunicationItemReceivers($oCommunicationItem, $aUniqueReceivers);
        }

        # mark message as sending
        CommunicationItemManager::markCommunicationItemAsSending($oCommunicationItem);
    }

    # return feedback
    print json_encode($oResObj);
    die;
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    # set crop referrer for pages module
    $_SESSION['cropReferrer'] = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . http_get("param2");

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {

        # get communication item
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get("param2"));
        if (empty($oCommunicationItem)) {
            http_redirect(ADMIN_FOLDER . "/");
        }

        # get customer marked with `receiveCommunicationVerification` property
        $aVerificationCustomers = CustomerManager::getCustomersByFilter(array('receiveCommunicationVerification' => true, 'online' => true, 'contactByEmail' => true));

        if (is_numeric($oCommunicationItem->templateId) && empty($oCommunicationItem->message)) {
            $oTemplate = TemplateManager::getTemplateById($oCommunicationItem->templateId);
            if (!empty($oTemplate)) {
                $oCommunicationItem->message = $oTemplate->template;
            }
        }
    } else {
        $oCommunicationItem = new CommunicationItem();
    }

    # action = save
    if (http_post("action") == 'save') {

        $aCustomerGroups = array();
        foreach (http_post('customerGroupIds', array()) AS $iCustomerGroupId) {
            $oCustomerGroup = CustomerGroupManager::getCustomerGroupById($iCustomerGroupId);
            if (!$oCustomerGroup) {
                continue;
            }
            $aCustomerGroups[] = $oCustomerGroup;
        }
        $oCommunicationItem->setCustomerGroups($aCustomerGroups);

        # load data in object
        $oCommunicationItem->_load($_POST);

        # set message
        $oCommunicationItem->message = http_post('message');

        # when being SMS, limit name of sender when longer then specified length
        if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
            $oCommunicationItem->setSmsSenderName();
        }

        # if object is valid, save
        if ($oCommunicationItem->isValid()) {
            CommunicationItemManager::saveCommunicationItem($oCommunicationItem); //save object
            $_SESSION['statusUpdate'] = sysTranslations::get('communication_message_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCommunicationItem->communicationItemId);
        } else {
            Debug::logError("", "CommunicationItem module php validate error", __FILE__, __LINE__, "Tried to save CommunicationItem with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $_SESSION['statusUpdate'] = sysTranslations::get('communication_message_not_saved');
        }
    }

    # action saveImage
    if (http_post("action") == 'saveImage') {

        $bCheckMime = true;

        # upload file or return error
        $oUpload = new Upload($_FILES['image'], CommunicationItem::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'jpeg', 'png', 'gif'), $bCheckMime);

        # save image to database on success
        if ($oUpload->bSuccess === true) {

            // resize original to acceptable size (1500*1500 max)
            if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg)) {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_resize') . ' ' . $sErrorMsg; //error resizing image
            }

            # make Image object and save
            $oImage = new Image();
            $oImageFile = new ImageFile();
            $oImageFile->link = $oUpload->sNewFilePath;
            $oImageFile->title = http_post('title', '');
            $oImageFile->name = $oUpload->sNewFileBaseName;
            $oImageFile->mimeType = $oUpload->sMimeType;
            $oImageFile->size = $oUpload->iSize;
            $oImageFile->reference = 'original';

            $aImageFiles[] = $oImageFile;

            # always make detail image (resized of original)
            if (ImageManager::resizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . CommunicationItem::IMAGES_PATH . '/detail/' . $oImageFile->name, COMMUNICATION_IMAGES_W, COMMUNICATION_IMAGES_H, $sErrorMsg)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = CommunicationItem::IMAGES_PATH . '/detail/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'detail';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_image_detail') . ' ' . $sErrorMsg; //error cropping image
            }

            # make crop_small
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . CommunicationItem::IMAGES_PATH . '/crop_small/' . $oImageFile->name, COMMUNICATION_IMAGES_W, COMMUNICATION_IMAGES_H, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = CommunicationItem::IMAGES_PATH . '/crop_small/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'crop_small';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_automatic_cutout');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_automatic_cutout') . ' ' . $sErrorMsg; //error cropping image
            }

            # make cms_thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . CommunicationItem::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 48, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = CommunicationItem::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'cms_thumb';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_automatic_cutout');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_automatic_cutout') . ' ' . $sErrorMsg; //error cropping image
            }

            $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
            # save image
            ImageManager::saveImage($oImage);

            # save news item image relation
            CommunicationItemManager::saveCommunicationItemImageRelation($oCommunicationItem->communicationItemId, $oImage->imageId);

            # direct crop
            if (http_post("cropAfterUpload") == 1) {
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image/?imageId=' . $oImage->imageId);
            } else {
                # back to edit
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCommunicationItem->communicationItemId);
            }
        } else {
            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . ' ' . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCommunicationItem->communicationItemId);
        }
    }

    # set settings for image management
    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->aImages = $oCommunicationItem->getImages('all');
    $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCommunicationItem->communicationItemId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb', 'detail', 'crop_small');
    $oImageManagerHTML->bCropAfterUploadChecked = true;
    $oImageManagerHTML->sortable = false;
    $oImageManagerHTML->onlineChangeable = false;
    $oImageManagerHTML->bShowCropAfterUploadOption = true;

    if (!empty($oCommunicationItem->communicationItemId) 
            && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL 
            && $oCommunicationItem->getTemplate() != null
            && $oCommunicationItem->getTemplate()->templateId == Template::NEWSLETTER_TEMPLATE_ID) {
        // handle filter
        $aArticleFilter = http_session('newsletterItemFilter');

        if (empty($aArticleFilter)) {
            unset($_SESSION['newsletterItemFilter']);
            $aArticleFilter = array();
            $aArticleFilter['q'] = '';
            $aArticleFilter['articleCategoryId'] = '';
            $aArticleFilter['authorId'] = '';
            $aArticleFilter['usedBefore'] = '';
            $aArticleFilter['status'] = Article::ARTICLE_STATUS_PUBLISHED;
            $_SESSION['newsletterItemFilter'] = $aArticleFilter;
        }

        $iFoundRows = 0;
        $sIncludedArticles = '';
        $iPerPage = http_session('articlesPerPage', 10);
        $iCurrPage = http_session('page', 1);
        $iStart = (($iCurrPage - 1) * $iPerPage);


//        # get communication item
//        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get("param2"));
//        if (empty($oCommunicationItem)) {
//            http_redirect(ADMIN_FOLDER . "/");
//        }

        $aArticleFilter['communicationItemId'] = $oCommunicationItem->communicationItemId;
        $aArticles = ArticleManager::getArticlesByFilter($aArticleFilter, $iPerPage, $iStart, $iFoundRows, array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC'));
        $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    }


    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/communicationItem/communicationItem_form.inc.php';
} elseif (http_get("param1") == 'crop-image' && is_numeric(http_get("imageId"))) {

    $oImage = ImageManager::getImageById(http_get("imageId"));

    if (!$oImage || !$oImage->getImageFileByReference("original")) {
        $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_available'); //error getting image
        http_redirect(http_session('cropReferrer'));
    }

    # set crop settings
    $oCropSettings = new CropSettings();
    $oCropSettings->iImageId = $oImage->imageId;
    $oCropSettings->sReferenceName = 'original';

    $oCropSettings->setAspectRatio(COMMUNICATION_IMAGES_W, COMMUNICATION_IMAGES_H);

    $oCropSettings->bShowPreview = true;
    $oCropSettings->iMaxPreviewWidth = COMMUNICATION_IMAGES_W;

    $oCropSettings->sReferrer = http_session('cropReferrer');
    $oCropSettings->sReferrerTekst = sysTranslations::get('communication_referrertext_edit');

    # create cropand show on crop form page
    $oCropSettings->addCrop(COMMUNICATION_IMAGES_W, COMMUNICATION_IMAGES_H, CommunicationItem::IMAGES_PATH . '/crop_small/' . $oImage->getImageFileByReference('original')->name, 'crop_small', true, true);

    # create crop but do not show on crop form page
    $oCropSettings->addCrop(100, 48, $oImage->getImageFileByReference('cms_thumb')->link, 'cms_thumb', false, true);

    # add setting to session in an array
    $_SESSION['aCropSettings'][] = clone $oCropSettings;

    http_redirect(ADMIN_FOLDER . '/crop');

# see active users which has received message
} elseif (http_get("param1") == 'gebruikers' && is_numeric(http_get("param2"))) {
    $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get("param2"));
    $aCustomers = $oCommunicationItem->getSendCustomers();
    $aCustomersOpenedMail = $oCommunicationItem->getConversionStats();
    $aBlacklistedCustomers = $oCommunicationItem->getBlacklistItems();
    $aBouncedCustomers = $oCommunicationItem->getBouncedItems();
    $aRedirectLinks = $oCommunicationItem->getAmountOfRedirectLinks();

    $iPercentageOpened = round(count($aCustomersOpenedMail) / count($aCustomers) * 100, 2);
    $iPercentageBlacklisted = round(count($aBlacklistedCustomers) / count($aCustomers) * 100, 2);
    $iPercentageBounced = round(count($aBouncedCustomers) / count($aCustomers) * 100, 2);

    if (http_post('action') == 'export_xls') {
        $iCommunicationItemId = $oCommunicationItem->communicationItemId;

        $sCommunicationType = $oCommunicationItem->type;

        $sFileName = 'Export_' . $iCommunicationItemId . '-' . date('Y-m-d H-i-s');

        /* headers for generating Excel */
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-disposition: " . $sFileName . ".xls");
        header("Content-Disposition: attachment; filename=" . $sFileName . ".xls");
        header('Expires: 0');
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Pragma: no-cache');

        $sXls = '';
        $sXls .= '<table border="0" cellspacing="2" cellpadding="2" style="font: 12px Arial">' . "\n";
        $sXls .= '<tr><td colspan="2">'.sysTranslations::get('communication_statistics_of').' <b>' . _e($oCommunicationItem->title) . '</b></td></tr>' . "\n";
        $sXls .= '<tr><td colspan="2">&nbsp;</td></tr>' . "\n";
        $sXls .= '<tr><td colspan="2"><b>'.sysTranslations::get('global_summery').'</b></td></tr>' . "\n";
        $sXls .= '<tr><td>'.sysTranslations::get('communication_stats_amount_send_customers').'</td><td>' . count($aCustomers) . '</td></tr>' . "\n";
        if ($sCommunicationType == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
            $sXls .= '<tr><td>'.sysTranslations::get('communication_stats_unique_trackingpixel').'</td><td>' . count($aCustomersOpenedMail) . '</td></tr>' . "\n";
            $sXls .= '<tr><td>'.sysTranslations::get('communication_blacklisted').'</td><td>' . count($oCommunicationItem->getBlacklistItems()) . '</td></tr>' . "\n";
            $sXls .= '<tr><td>'.sysTranslations::get('communication_bounced').'</td><td>' . count($oCommunicationItem->getBouncedItems()) . '</td></tr>' . "\n";
        }
        $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
        $sXls .= '<tr><td colspan="2"><b>'.sysTranslations::get('communication_stats_send_customers').' (' . (count($aCustomers)) . '):</b></td></tr>' . "\n";
        foreach ($aCustomers as $oCustomer) {
            # if property exists, it's a current customer
            if (!empty($oCustomer->clientCreated)) {
                $sXls .= '<tr><td colspan="2">' . $oCustomer->getFullName() . ' | ' . _e($oCustomer->email) . '</td></tr>' . "\n";
            }
            # if not existing customer, get data from logs
            else {
                $sXls .= '<tr><td colspan="2">' . _e($oCustomer->name) . ' | ' . _e($oCustomer->receivedDestination) . '</td></tr>' . "\n";
            }
        }
        if ($sCommunicationType == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
            $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
            $sXls .= '<tr><td colspan="2"><b>'.sysTranslations::get('communication_stats_amount_send_customers').' (' . (count($aCustomersOpenedMail)) . '):</b></td></tr>' . "\n";
            foreach ($aCustomersOpenedMail as $oCustomer) {
                # if property exists, it's a current customer
                if (!empty($oCustomer->clientCreated)) {
                    $sXls .= '<tr><td colspan="2">' . $oCustomer->getFullName() . ' | ' . _e($oCustomer->clientEmail) . '</td></tr>' . "\n";
                }
                # if not existing customer, get data from logs
                else {
                    $sXls .= '<tr><td colspan="2">' . _e($oCustomer->name) . ' | ' . _e($oCustomer->email) . '</td></tr>' . "\n";
                }
            }
            $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
            $sXls .= '<tr><td colspan="2"><b>'.sysTranslations::get('communication_customers_unsubscribe_this_message').' (' . (count($aBlacklistedCustomers)) . '):</b></td></tr>' . "\n";
            foreach ($aBlacklistedCustomers as $oCustomer) {
                # if property exists, it's a current customer
                if (!empty($oCustomer->created)) {
                    $sXls .= '<tr><td colspan="2">' . $oCustomer->getFullName() . ' | ' . _e($oCustomer->email) . '</td></tr>' . "\n";
                }
                # if not existing customer, get data from logs
                else {
                    $sXls .= '<tr><td colspan="2">' . _e($oCustomer->name) . ' | ' . _e($oCustomer->email) . '</td></tr>' . "\n";
                }
            }
            $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
            $sXls .= '<tr><td colspan="2"><b>'.sysTranslations::get('communication_email_bounced_this_message') . ' (' . (count($aBouncedCustomers)) . '):</b></td></tr>' . "\n";
            foreach ($aBouncedCustomers as $oCustomer) {
                $sXls .= '<tr><td>' . _e($oCustomer->email) . '</td><td>' . _e($oCustomer->bounceRuleTitle . ' - ' . $oCustomer->bounceRuleDescription) . '</td></tr>' . "\n";
            }
            
            # write out amount of clicks per link
            $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
            $sXls .= '<tr><td colspan="2"><b>Aantal unieke clicks per link (' . (count($aRedirectLinks)) . '):</b></td></tr>' . "\n";
            foreach ($aRedirectLinks as $oRedirectLink) {
                # write out link
                $sXls .= '<tr><td>' . $oRedirectLink->originalUrl . '</td><td>' . CommunicationItemLinkRedirectStatManager::getUniqueAmountOfClicksOfRedirectStat($oCommunicationItem->communicationItemId, $oRedirectLink->communicationItemLinkRedirectId)->amount . '</td></tr>' . "\n";
        }
            
            # write out amount of clicks per link
            $sXls .= '<tr><td colspan="2"><hr></td></tr>' . "\n";
            $sXls .= '<tr><td colspan="2"><b>Aantal unieke clicks per link:</b></td></tr>' . "\n";
            foreach ($aRedirectLinks as $oRedirectLink) {
                # write out link
                $sXls .= '<tr><td colspan="2"><strong>' . $oRedirectLink->originalUrl . '</strong></td></tr>' . "\n";
                $aCustomersClickedOnRedirectLink = $oRedirectLink->getCustomersByLinkRedirect();
                if(!empty($aCustomersClickedOnRedirectLink)) {
                    foreach($aCustomersClickedOnRedirectLink as $oRedirectLinkCustomer) {
                        
                        # if property exists, it's a current customer
                        if (!empty($oRedirectLinkCustomer->created)) {
                            $sXls .= '<tr><td>' . $oRedirectLinkCustomer->getFullName() . '</td><td>' . _e($oRedirectLinkCustomer->email) . '</td></tr>' . "\n";
                        }
                        # if not existing customer, get data from logs
                        else {
                            $sXls .= '<tr><td>' . _e($oRedirectLinkCustomer->name) . '</td><td>' . _e($oRedirectLinkCustomer->email) . '</td></tr>' . "\n";
                        }
                    }
                    $sXls .= '<tr><td colspan="2"></td></tr>' . "\n";
                } else {
                    $sXls .= '<tr><td colspan="2"></td></tr>' . "\n";
                }
                
            }
            
        }
        $sXls .= '</table>' . "\n";
        echo $sXls;
        die;
    }

    # add PIE css
    $oPageLayout->addStylesheet('<link rel="stylesheet" type="text/css" href="' . CSS_FOLDER . '/jquery.easy-pie-chart.css">');

    # add PIE JS
    $sJsFolder = JS_FOLDER;
    $sJavascriptBottom = <<<EOT
    <script type="text/javascript" src="{$sJsFolder}/jquery.easypiechart.min.js"></script>
    <script>
    $(function() {
        $('.chart').easyPieChart({
            barColor: '#88ba01',
            trackColor: '#e4e4e4',
            easing: 'easeOutBounce',
            onStep: function(from, to, percent) {
                $(this.el).find('.percent').text(Math.round(percent));
            }
        });
        var chart = window.chart = $('.chart').data('easyPieChart');
        $('.js_update').on('click', function() {
            chart.update(Math.random()*200-100);
        });
        $(document).ready(function() {
            setTimeout("location.reload(true);",1000*30);
        });
    });
    </script>
EOT;
    $oPageLayout->addJavascriptBottom($sJavascriptBottom);
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/communicationItem/communicationItem_sendedUsers.inc.php';
}

# send message
elseif (http_get("param1") == 'verzenden' && is_numeric(http_get('param2'))) {

    # fetch original message
    $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get('param2'));
    if (empty($oCommunicationItem) || (!empty($oCommunicationItem) && $oCommunicationItem->sended)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('communication_message_not_sendable');
        http_redirect(ADMIN_FOLDER . '/communicatie');
    }

    # placeholder receivers
    $aReceivers = array();
    $aUniqueReceivers = array();

    # get all linked customerGroups
    $aCustomerGroups = $oCommunicationItem->getCustomerGroups();
    foreach ($aCustomerGroups as $oCustomerGroup) {
        if (empty($oCustomerGroup)) {
            continue;
        }
        # get linked online customers
        $aCustomersOfGroup = $oCustomerGroup->getClients(true, true);
        foreach ($aCustomersOfGroup as $oCustomer) {
            if (empty($oCustomer)) {
                continue;
            }
            $aReceivers[] = $oCustomer;
        }
    }

    # when communicationItem is not sended yet
    if ($oCommunicationItem->sending == 0) {
        # get emailaddresses and send e-mail
        if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {

            # placeholders
            $aEmailaddressesReceivers = array();
            $aUniqueEmailAddresses = array();

            # get all emailaddresses
            foreach ($aReceivers as $oReceiver) {
                # filter properties
                if (!empty($oReceiver->email) && filter_var($oReceiver->email, FILTER_VALIDATE_EMAIL) && $oReceiver->contactByEmail) {
                    $aEmailaddressesReceivers[$oReceiver->customerId] = $oReceiver->email;
                }
            }

            # fetch unique emailaddresses values
            $aUniqueEmailAddresses = array_unique($aEmailaddressesReceivers);

            # get object and put in array with unique values
            foreach ($aUniqueEmailAddresses as $iCustomerId => $sUniqueEmailAddress) {
                # get customer
                $oCustomer = CustomerManager::getCustomerById($iCustomerId);
                # put in array with unique values
                $aUniqueReceivers[$oCustomer->email] = $oCustomer;
            }

            # sort by email
            ksort($aUniqueReceivers);
        }
        # get mobile phonenumbers and send SMS
        elseif ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {

            # placeholders
            $aMobilePhonenumbersReceivers = array();
            $aUniqueMobilePhonenumbers = array();

            # get all emailaddresses
            foreach ($aReceivers as $oReceiver) {
                # filter properties
                if (!empty($oReceiver->mobilePhone) && $oReceiver->contactBySms) {
                    $aMobilePhonenumbersReceivers[$oReceiver->customerId] = $oReceiver->mobilePhone;
                }
            }

            # fetch unique emailaddresses values
            $aUniqueMobilePhonenumbers = array_unique($aMobilePhonenumbersReceivers);

            # get object and put in array with unique values
            foreach ($aUniqueMobilePhonenumbers as $iCustomerId => $sUniqueMobilePhoneNumber) {
                # get customer
                $oCustomer = CustomerManager::getCustomerById($iCustomerId);
                # put in array with unique values
                $aUniqueReceivers[$oCustomer->mobilePhone] = $oCustomer;
            }

            # sort by phonenumber
            ksort($aUniqueReceivers);
        }
    }
    # WHEN HAVING ERROR, collect address which are not notified yet
    elseif ($oCommunicationItem->sending == 1) {
        $aUniqueReceivers = CommunicationItemManager::getUnsendedCustomersFromSendlistByCommunicationId($oCommunicationItem->communicationItemId);
    }

    # view
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/communicationItem/communicationItem_sendMessage.inc.php';
}

# delete object
elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2"))) {
        $oCommunicationItem = CommunicationItemManager::getCommunicationItemById(http_get("param2"));
    }

    if (!empty($oCommunicationItem) && CommunicationItemManager::deleteCommunicationItem($oCommunicationItem)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('communication_message_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('communication_message_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} elseif (http_get('param1') == 'image-list') {
    $oCommunicationItemId = CommunicationItemManager::getCommunicationItemById(http_get('param2'));

    if (!$oCommunicationItemId)
        die;

    $aImages = $oCommunicationItemId->getImages('all');
    $aImageFiles = array();
    $aLinks = array();
    foreach ($aImages AS $oImage) {
        $oImageFile = $oImage->getImageFileByReference('crop_small');
        if (!$oImageFile)
            continue;
        $oLink = new stdClass();
        $oLink->title = $oImageFile->title ? $oImageFile->title : $oImageFile->name;
        $oLink->value = CLIENT_HTTPS_URL . $oImageFile->link;
        $aLinks[] = $oLink;
    }

    die(json_encode($aLinks));
} elseif (http_get('param1') == 'link-list') {
    $aPages = PageManager::getPagesByFilter(array('showAll' => 1, 'level' => 1, 'online' => 1)); // all online level 1 pages

    $aLinks = array();

    function makeListTree($aPages, &$aLinks) {
        foreach ($aPages AS $oPage) {
            $sLeadingChars = '';
            for ($iC = $oPage->level; $iC > 1; $iC--) {
                $sLeadingChars .= '--';
            }
            $oLink = new stdClass();
            $oLink->title = $sLeadingChars . $oPage->getShortTitle();
            $oLink->value = CLIENT_HTTPS_URL . $oPage->getUrlPath();
            $aLinks[] = $oLink;
            makeListTree($oPage->getSubPages('online-all'), $aLinks); //call function recursive
        }
    }

    makeListTree($aPages, $aLinks);

    die(json_encode($aLinks));
}

# display overview
else {
    $aCommunicationItemsNotSended = CommunicationItemManager::getAllCommunicationItems(array('sended' => 0));
    $aCommunicationItemsSended = CommunicationItemManager::getAllCommunicationItems(array('sended' => 1));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/communicationItem/communicationItem_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>