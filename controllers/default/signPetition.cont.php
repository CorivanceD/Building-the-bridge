<?php

require(INC_FOLDER . '/recaptcha/autoload.php');
/*
 * Controller to handle normal content pages
 */
$oPageLayout = new PageLayout();


unset($_SESSION['signatureStatusUpdate']);

if (is_numeric(http_post('petitionId'))) {

    $oPetition = PetitionManager::getPetitionById(http_post('petitionId'));

    if (empty($oPetition)) {
        http_redirect('/');
    }

    $oPageLayout->sPagePath = PAGES_FOLDER . '/petitions/sign_petition.inc.php';
    $oPageLayout->sWindowTitle = 'Sign the Petition: ' . $oPetition->title;
    $oPageLayout->sMetaDescription = 'Sign the Petition: ' . $oPetition->title;
    $oPageLayout->sMetaKeywords = implode(',', explode(' ', $oPetition->title));
    $oPageLayout->sOGType = 'website';
    $oPageLayout->sOGTitle = 'Sign the Petition: ' . $oPetition->title;
    $oPageLayout->sOGDescription = 'Sign the Petition: ' . $oPetition->title;
    $oPageLayout->sOGUrl = getCurrentUrl();

    if (http_post('action', null) === 'sign') {
        $aErrors = array();
        
        if (empty($oCurrentCustomer)) {
            $oCustomer = CustomerManager::getCustomerByEmail(http_post('email'));

            if (!empty($oCustomer) && $oCustomer->password !== 'XXXXXXXX') {
                $aErrors['email'] = 'There already was a registered user with that e-mail address. Please log in or use another e-mail address';
            } elseif ((!empty($oCustomer) && $oCustomer->password === 'XXXXXXXX')) {
                $oSignature = PetitionManager::getPetitionSignatureById(http_post('petitionId'), $oCustomer->customerId);

                if (!empty($oSignature)) {
                    $aErrors['email'] = 'There was already a signature with that e-mail address';
                }
            }

            if (http_post('alias', null) === null) {
                $aErrors['alias'] = 'You must enter an alias';
            }

            if (http_post('email', '') !== '' && !filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
                $aErrors['email'] = 'You must enter a valid e-mail address';
            }

            if (http_post('city', null) === null) {
                $aErrors['city'] = 'You must enter your city';
            }

            if (http_post('gender', null) === null) {
                $aErrors['gender'] = 'You must enter your gender';
            }

            if (http_post('countryId', null) === null) {
                $aErrors['countryId'] = 'You must enter your country';
            }

            if (http_post('age', null) === null) {
                $aErrors['age'] = 'You must enter your age';
            }

            if (http_post('comment', null) === null) {
                $aErrors['comment'] = 'You must enter a comment';
            }

            // do SPAM checks
            if (hasLinks(http_post('comment'))) {
                $aErrors['comment'] = 'Your comment contains one or more links, this is not allowed. Remove them and try again.';
            }

            if (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) {
                $aErrors['S-P-A-M'] = 'There was a problem submitting your signature, try again later. Sorry for the inconvenience';
            }

            $oFakeCustomer = new Customer();
            $oFakeCustomer->name = _e(http_post('alias'));
            $oFakeCustomer->email = http_post('email') === '' ? 'none@none.non' : http_post('email');
            $oFakeCustomer->city = _e(http_post('city'));
            $oFakeCustomer->contactByEmail = 0;
            $oFakeCustomer->contactBySms = 0;
            $oFakeCustomer->gender = http_post('gender');
            $oFakeCustomer->countryId = http_post('countryId');
            $oFakeCustomer->online = 0;
            $oFakeCustomer->age = http_post('age');

            $sPassword = randomPassword(10);
            $oFakeCustomer->password = hashPasswordForDb($sPassword);

            if (empty($aErrors) && $oFakeCustomer->isValid()) {                
                
                $oFakeCustomer->confirmCode = substr(hash('sha512', uniqid(microtime() . $oFakeCustomer->email, 1)), 15, 50);
                CustomerManager::saveCustomer($oFakeCustomer);
                
                # send confirmation mail for new account
                $oTemplate = TemplateManager::getTemplateByName('petition_signup');

                // check if template exists
                if (empty($oTemplate)) {
                    Debug::logError('', 'Template doesn\'t exist: `petition_signup`', __FILE__, __LINE__, '', Debug::LOG_IN_EMAIL);
                } else {
                    $oTemplate->replaceVariables($oFakeCustomer);
                    $sTo = $oFakeCustomer->email;
                    $sSubject = $oTemplate->getSubject();
                    $sMailBody = $oTemplate->getTemplate();
                    $sMailBody = str_replace('[PASSWORD]', $sPassword, $sMailBody);
                    
                    MailManager::sendMail($sTo, $sSubject, $sMailBody);
                }

                $oSignature = new PetitionSignature();
                $oSignature->petitionId = intval(http_post('petitionId'));
                $oSignature->customerId = $oFakeCustomer->customerId;
                $oSignature->comment = _e(http_post('comment', ''));
 
                if ($oSignature->isValid()) {
                    PetitionManager::savePetitionSignature($oSignature);
                    $_SESSION['signatureStatusUpdate'] = 'Thanks for your collaboration, your signature has been submitted succesfully';
                } else {
                    CustomerManager::deleteCustomer($oFakeCustomer);
                    $_SESSION['signatureStatusUpdate'] = 'There was a problem submitting your signature, try again later. Sorry for the inconvenience';
                }
            } else {
                $_SESSION['signatureStatusUpdate'] = 'There was a problem submitting your signature, try again later. Sorry for the inconvenience';
            }
        } else {

            $oSignature = PetitionManager::getPetitionSignatureById(http_post('petitionId'), $oCurrentCustomer->customerId);

            if (empty($oSignature)) {
                // do SPAM checks
                if (hasLinks(http_post('comment'))) {
                    $_SESSION['signatureStatusUpdate'] = 'Your comment contains one or more links, this is not allowed. Remove them and try again.';
                } elseif (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) {
                    $_SESSION['signatureStatusUpdate'] = 'There was a problem submitting your signature, try again later. Sorry for the inconvenience';
                } else {
                    $oSignature = new PetitionSignature();
                    $oSignature->petitionId = intval(http_post('petitionId'));
                    $oSignature->customerId = $oCurrentCustomer->customerId;
                    $oSignature->comment = _e(http_post('comment', ''));

                    if ($oSignature->isValid()) {
                        PetitionManager::savePetitionSignature($oSignature);
                        $_SESSION['signatureStatusUpdate'] = 'Thanks for your collaboration, your signature has been submitted succesfully';
                    } else {
                        $_SESSION['signatureStatusUpdate'] = 'There was a problem submitting your signature, try again later. Sorry for the inconvenience';
                    }
                }
            } else {
                $_SESSION['signatureStatusUpdate'] = 'You have already signed this petition';
            }
        }
    }
} elseif (http_get('param1') == 'new') {
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online) {
        showHttpError('404');
    }

    if (http_post('action') == 'sendPetition') {
        
        $aErrors = array();
        $bLogError = true;
        $oParentCategory = null;
        $aCategories = array();        
        $recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            if (!http_post('author')) {
                $aErrors['author'] = 'You must enter your name or an alias';
            }
            if (!http_post('organization')) {
                $aErrors['organization'] = 'You must enter your organization name';
            }     
            if (!http_post('phone')) {
                $aErrors['phone'] = 'You must enter your phone';
            } 
            if (!http_post('countryId')) {
                $aErrors['phone'] = 'You must choose a country';
            }                
            if (!http_post('email')) {
                $aErrors['email'] = 'You must enter your email address';
            } elseif (!filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
                $aErrors['email'] = 'You\'ve entered an invalid email address';
            }                       
            if (!http_post('title')) {
                $aErrors['title'] = 'You must enter the title of your petition';
            }
            if (!http_post('intro')) {
                $aErrors['intro'] = 'You must enter an intro';
            }   
            if (!http_post('description')) {
                $aErrors['description'] = 'You must enter a description';
            }           

            // do SPAM checks
            if (hasLinks(http_post('intro'))) {
                $aErrors['intro'] = 'Your intro contains one or more links, this is not allowed. Remove them and try again.';
                $bLogError = false;
            }
            if (hasLinks(http_post('description'))) {
                $aErrors['description'] = 'Your description contains one or more links, this is not allowed. Remove them and try again.';
                $bLogError = false;
            }            
            // end SPAM checks

            $oPetition = new Petition();
            $oPetition->author = http_post('author');
            $oPetition->countryId = http_post('countryId');
            $oPetition->description = http_post('description');
            $oPetition->email = http_post('email');
            $oPetition->intro = http_post('intro');
            $oPetition->phone = http_post('phone');
            $oPetition->title = http_post('title');
            $oPetition->requiredSignatures = 1000;
            $oPetition->online = 0;

            if (empty($aErrors) && $oPetition->isValid()) {
                
                PetitionManager::savePetition($oPetition);
                $sSubject = 'New Petition Received';
                $sMail = '  <span style="font: 14px/22px Arial, sans-serif">
                            ' . _e(http_post('author')) . ' has send a petition. Below you can read the received info.<br />
                            <br />
                            <table style="font: 14px/22px Arial, sans-serif">
                                <tr>
                                    <td>Name:</td>
                                    <td>' . _e(http_post('author')) . '</td>
                                </tr>
                                <tr>
                                    <td>E-mail:</td>
                                    <td>' . _e(http_post('email')) . '</td>
                                </tr>
                                <tr>
                                    <td>Phone:</td>
                                    <td>' . _e(http_post('phone')) . '</td>
                                </tr>  
                                <tr>
                                    <td>Country:</td>
                                    <td>' . CountryManager::getCountry(http_post('countryId'))->name . '</td>
                                </tr>                                
                                <tr>
                                    <td>Title :</td>
                                    <td>' . _e(http_post('title')) . '</td>
                                </tr>
                                <tr>
                                    <td>Intro:</td>
                                    <td>' . nl2br(_e(http_post('intro'))) . '</td>
                                </tr> 
                                <tr>
                                    <td>Description:</td>
                                    <td>' . nl2br(_e(http_post('description'))) . '</td>
                                </tr> 
                              </table>
                        </span>
                        ';

                MailManager::sendMail(CLIENT_DEFAULT_EMAIL_TO, $sSubject, $sMail, http_post('email'), http_post('email'));
                http_redirect('/' . http_get('controller') . '/thanks?conversion=1');
            } else {
                if ($bLogError)
                    Debug::logError("", "Petition form on frontend validate error", __FILE__, __LINE__, "Tried to post the Petition form with wrong values despite javascript check (or rumpelstiltkin values are incorrect)." . print_r($_POST, 1), Debug::LOG_IN_EMAIL);
            }
        } else {
            //$errors = $resp->getErrorCodes();                            
        }
    }
    

    $oPageLayout->sPagePath = PAGES_FOLDER . '/petitions/petition_form.inc.php';
    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->sOGType = 'website';
    $oPageLayout->sOGTitle = $oPage->getWindowTitle();
    $oPageLayout->sOGDescription = $oPage->getMetaDescription();
    $oPageLayout->sOGUrl = getCurrentUrl();
} else {
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online) {
        showHttpError('404');
    }

    if (getCurrentUrlPath() == '/petitions/thanks') {
        $oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';
    } else {
    $oPageLayout->sPagePath = PAGES_FOLDER . '/petitions/petitions.inc.php';
    }
    
    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->sOGType = 'website';
    $oPageLayout->sOGTitle = $oPage->getWindowTitle();
    $oPageLayout->sOGDescription = $oPage->getMetaDescription();
    $oPageLayout->sOGUrl = getCurrentUrl();
    $aPetitions = PetitionManager::getPetitions(array());
}

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>