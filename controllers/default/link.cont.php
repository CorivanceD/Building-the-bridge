<?php

require(INC_FOLDER . '/recaptcha/autoload.php');
/*
 * Controller to handle normal content pages
 */
$oPageLayout = new PageLayout();

$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

if (empty($oPage)) {
    $oPage = PageManager::getPageByUrlPath('/' . http_get('controller'));
}

if (empty($oPage) || !$oPage->online) {
    showHttpError('404');
}

if (http_post("action") == 'ajax-getSubCategories') {
    $bAjax = http_post("ajax", false); //controller requested by ajax
    $iCategoryId = http_post("linkCategoryId");
    $oResObj = new stdClass(); //standard class for json feedback
    $oResObj->success = false;

    if (is_numeric($iCategoryId)) {
        $oCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById($iCategoryId);

        if (!empty($oCategory)) {
            $oResObj->success = true;
            $oResObj->subCategories = $oCategory->getSubCategories();
        }
    }

    die(json_encode($oResObj));
}

$oPageLayout->sWindowTitle = $oPage->getWindowTitle();
$oPageLayout->sMetaDescription = $oPage->getMetaDescription();
$oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
$oPageLayout->sOGType = 'website';
$oPageLayout->sOGTitle = $oPage->getWindowTitle();
$oPageLayout->sOGDescription = $oPage->getMetaDescription();
$oPageLayout->sOGUrl = getCurrentUrl();

if (http_get('param1') == 'share-link') {

    if (http_post('action') == 'sendLink') {

        $aErrors = array();
        $bLogError = true;
        $oParentCategory = null;
        $aCategories = array();        
        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET);
        if(http_post('g-recaptcha-response')) {
            $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        }
        
        if (!empty($resp) && $resp->isSuccess()) {
            if (!http_post('sender')) {
                $aErrors['sender'] = 'You must enter your name';
            }
            if (!http_post('email')) {
                $aErrors['email'] = 'You must enter your email address';
            } elseif (!filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
                $aErrors['email'] = 'You\'ve entered an invalid email address';
            }
            if (!http_post('url')) {
                $aErrors['url'] = 'You must enter a valid url';
            } elseif (!filter_var(http_post('url'), FILTER_VALIDATE_URL)) {
                $aErrors['url'] = 'You\'ve entered an invalid url';
            }
            if (!http_post('returnUrl')) {
                $aErrors['returnUrl'] = 'You must enter a valid return url';
            } elseif (!filter_var(http_post('returnUrl'), FILTER_VALIDATE_URL)) {
                $aErrors['returnUrl'] = 'You\'ve entered an invalid return url';
            }
            if (!http_post('linkCategoryId')) {
                $aErrors['linkCategoryId'] = 'You must choose a category';
            }
            else {
                $aCategories[] = http_post('linkCategoryId');
                $oParentCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById(http_post('linkCategoryId'));
            }
            
            if (!empty($oParentCategory) && $oParentCategory->getSubCategories() != null && !http_post('linkSubCategoryId')) {
                $aErrors['linkSubCategoryId'] = 'You must choose a sub category';
            }
            elseif (http_post('linkSubCategoryId')) {
                $aCategories[] = http_post('linkSubCategoryId');
            }
            
            if (!http_post('message')) {
                $aErrors['message'] = 'You must enter a message';
            }

            // do SPAM checks
            if (hasLinks(http_post('message'))) {
                $aErrors['message'] = 'Your message contains one or more links, this is not allowed. Remove them and try again.';
                $bLogError = false;
            }
            // end SPAM checks

            $oCustomerLink = new CustomerLink();
            $oCustomerLink->sender = http_post('sender');
            $oCustomerLink->email = http_post('email');
            $oCustomerLink->name = http_post('name');
            $oCustomerLink->url = http_post('url');
            $oCustomerLink->returnUrl = http_post('returnUrl');
            $oCustomerLink->message = _e(http_post('message'));                        
            $oCustomerLink->setCategories($aCategories);


            if (empty($aErrors) && $oCustomerLink->isValid()) {
                
                CustomerLinkManager::saveCustomerLink($oCustomerLink);
                $sSubject = 'New Link Shared';
                $sMail = '  <span style="font: 14px/22px Arial, sans-serif">
                            ' . _e(http_post('name')) . ' has send a new link to share. Below you can read the received info.<br />
                            <br />
                            <table style="font: 14px/22px Arial, sans-serif">
                                <tr>
                                    <td>Name:</td>
                                    <td>' . _e(http_post('sender')) . '</td>
                                </tr>
                                <tr>
                                    <td>E-mail:</td>
                                    <td>' . _e(http_post('email')) . '</td>
                                </tr>
                                <tr>
                                    <td>Title webpage:</td>
                                    <td>' . _e(http_post('name')) . '</td>
                                </tr>
                                <tr>
                                    <td>URL:</td>
                                    <td>' . _e(http_post('url')) . '</td>
                                </tr> 
                                <tr>
                                    <td>Return URL:</td>
                                    <td>' . _e(http_post('returnUrl')) . '</td>
                                </tr> 
                                <tr>
                                    <td>Category:</td>
                                    <td>' . CustomerLinkCategoryManager::getCustomerLinkCategoryById(http_post('customerLinkCategoryId'))->name . '</td>
                                </tr>                                  
                                <tr>
                                    <td>Message:</td>
                                    <td>' . nl2br(_e(http_post('message'))) . '</td>
                                </tr>
                              </table>
                        </span>
                        ';

                MailManager::sendMail(CLIENT_DEFAULT_EMAIL_TO, $sSubject, $sMail, http_post('email'), http_post('email'));
                http_redirect('/'.http_get('controller') . '/thanks?conversion=1');
            } else {
                if ($bLogError)
                    Debug::logError("", "Link form on frontend validate error", __FILE__, __LINE__, "Tried to post the Link form with wrong values despite javascript check (or rumpelstiltkin values are incorrect)." . print_r($_POST, 1), Debug::LOG_IN_EMAIL);
            }
        } else {
            //$errors = $resp->getErrorCodes();                            
        }
    }

    $oPageLayout->sPagePath = PAGES_FOLDER . '/links/link_form.inc.php';
} else {
    
    if (is_numeric(http_get('param1'))) {
        $oParentCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById(http_get('param1'));
        
        if (empty($oParentCategory)) {
            http_redirect('/links');
        }
    }
    $oPageLayout->sPagePath = PAGES_FOLDER . '/links/link_overview.inc.php';
}

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>