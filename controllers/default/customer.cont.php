<?php

/*
 * controller to handle the customer
 */

// make pageLayout Object
$oPageLayout = new PageLayout();

$sReferrer = http_session("frontendLoginReferrer", "/" . http_get('controller'));

// set accountStatusUpdate
$sAccountStatusUpdate = http_session('accountStatusUpdate');
unset($_SESSION['accountStatusUpdate']); // only show once
// check if an email address exists
if (http_get('ajax') == 'checkEmail') {
    // return if the required fields are empty
    if (empty($_GET['email']))
        die(json_encode(null));

    // return bool (true if email doesn't exists and vice versa)
    die(json_encode(!CustomerManager::emailExists(http_get('email'), http_get('customerId', null))));
}

// edit account
if (http_get('param1') == 'edit') {
    // check if Customer is logged in
    if (empty($oCurrentCustomer) || !is_numeric($oCurrentCustomer->customerId))
        http_redirect('/' . http_get('controller'));

    // get page by urlPath (/account/edit)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    // get the current Customer's info from the database
    $oCustomer = CustomerManager::getCustomerById($oCurrentCustomer->customerId);
    // action = save

    if (http_post('action') == 'save') {

        if (!empty($_POST['password']) && strlen(http_post('password')) >= 8) {
            $oCustomer->password = hashPasswordForDb(http_post('password'));
        } else {
            //$oCustomer->password = null;
        }

        CustomerManager::saveCustomer($oCustomer); //save object            
        $_SESSION['accountStatusUpdate'] = 'Account info updated successfully'; // set statusUpdate to session

        if (!empty($_FILES['imageFile']['name'])) {
            # upload file or return error
            $oUpload = new Upload($_FILES['imageFile'], Customer::IMAGES_PATH . "/original/", (http_post('name') != '' ? prettyUrlPart(http_post('name')) : null), array('jpg', 'png', 'gif', 'jpeg'), true);

            # save image to database on success
            if ($oUpload->bSuccess === true) {

                // resize original to acceptable size
                if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg)) {
                    $_SESSION['accountStatusUpdate'] = 'Image cannot be resized: ' . $sErrorMsg; //error resizing image
                }

                # make Image object and save
                $oImage = new Image();
                $oImageFile = new ImageFile();
                $oImageFile->link = $oUpload->sNewFilePath;
                $oImageFile->title = http_post('name', '');
                $oImageFile->name = $oUpload->sNewFileBaseName;
                $oImageFile->mimeType = $oUpload->sMimeType;
                $oImageFile->size = filesize(DOCUMENT_ROOT . $oImageFile->link);
                $oImageFile->reference = 'original';
                $aImageFiles[] = $oImageFile;

                # make cms thumb
                if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Customer::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 100, $sErrorMsg, true)) {
                    $oImageFileThumb = clone $oImageFile;
                    $oImageFileThumb->link = Customer::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                    $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                    $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                    $oImageFileThumb->reference = 'cms_thumb';

                    $aImageFiles[] = clone $oImageFileThumb;
                } else {
                    $_SESSION['accountStatusUpdate'] = '"cms_thumb" cannot be created: ' . $sErrorMsg; //error cropping image
                }

                # make cms thumb
                if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Customer::IMAGES_PATH . '/crop_detail/' . $oImageFile->name, 200, 200, $sErrorMsg, true)) {
                    $oImageFileThumb = clone $oImageFile;
                    $oImageFileThumb->link = Customer::IMAGES_PATH . '/crop_detail/' . $oImageFile->name;
                    $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                    $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                    $oImageFileThumb->reference = 'crop_detail';

                    $aImageFiles[] = clone $oImageFileThumb;
                } else {
                    $_SESSION['accountStatusUpdate'] = '"crop_detail" cannot be created' . $sErrorMsg; //error cropping image
                }

                $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
                # save image
                ImageManager::saveImage($oImage);
                $oCustomer->imageId = $oImage->imageId;
                CustomerManager::saveCustomer($oCustomer);
                # save page image relation
                //CustomerManager::saveCustomerImageRelation($oCustomer->customerId, $oImage->imageId);

                $_SESSION['oCurrentCustomer'] = $oCustomer;
            } else {
                $_SESSION['accountStatusUpdate'] = "Image cannot be uploaded: " . $oUpload->getErrorMessage(); //error uploading file
            }
        }

        CustomerManager::setCustomerInSession($oCustomer); // set Customer in session
    }

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_edit.inc.php';
}

// form to change the password (after receiving the forgot-password mail)
elseif (http_get('param1') == 'password-forgotten' && http_get('param2') == 'edit') {
    // redirect to edit page if Customer is logged in
    if (!empty($oCurrentCustomer))
        http_redirect('/' . http_get('controller') . '/edit');

    // get page by urlPath (/account/wachtwoord-vergeten/edit)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    $sConfirmCode = http_post('code', http_get('code'));
    $sEmail = http_post('email', http_get('email'));

    // action updatePassword
    if (http_post("action") == 'updatePassword') {
        if (!empty($sConfirmCode) && !empty($sEmail) && http_post('password')) {
            // get the Customer
            $oCustomer = CustomerManager::getCustomerByConfirmCodeAndEmail($sConfirmCode, $sEmail);

            if ($oCustomer) {
                // define the password
                if (!empty($_POST['password']) && strlen(http_post('password')) >= 8) {
                    $oCustomer->password = hashPasswordForDb(http_post('password'));
                } else {
                    $oCustomer->password = null;
                }
            }

            if ($oCustomer && $oCustomer->isValid()) {
                $oCustomer = CustomerManager::updatePasswordAndLogin($oCustomer); //update password and login
                http_redirect('/' . http_get('controller') . '/' . http_get('param1') . '/opgeslagen');
            } elseif ($oCustomer) {
                $aErrorsChangePass = array();
                if (!$oCustomer->isPropValid('password')) {
                    $aErrorsChangePass['password'] = 'You must enter a password with at least 8 characters';
                }
                Debug::logError("", "Frontend Customer module php validate error", __FILE__, __LINE__, "Tried to update a Customer's password with wrong values<br />" . _d($oCustomer, 1, 1), Debug::LOG_IN_EMAIL);
            } else {
                $aErrorsChangePass = array();
                $aErrorsChangePass['combination'] = 'The code and e-mail entered don\'t match';
            }
        } else {
            $aErrorsChangePass = array();
            $aErrorsChangePass['combination'] = 'Enter the email address, the code you\'ve received in the email and a new password';
        }
    }

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());
    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_editPassword.inc.php';
}

// email sent after forgot-password page
elseif (http_get('param1') == 'password-forgotten' && http_get('param2') == 'saved') {
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online) {
        showHttpError('404');
    }

    $oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';
    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());
}

// forgot password
elseif (http_get('param1') == 'password-forgotten') {
    // redirect to edit page if Customer is logged in
    if (!empty($oCurrentCustomer))
        http_redirect('/' . http_get('controller') . '/edit');

    // get page by urlPath (/account/wachtwoord-vergeten)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    // action requestPassword
    if (http_post("action") == 'requestPassword') {
        if (http_post('email') && ($oCustomer = CustomerManager::getCustomerByEmail(http_post('email')))) {

            if ($oCustomer->online == "1") {
                // create a unqiue confirmCode
                $oCustomer->confirmCode = substr(hash('sha512', uniqid(microtime() . $oCustomer->email, 1)), 15, 50); // I think it's safe to assume this code will be unique ^_^

                if ($oCustomer->isValid()) {
                    CustomerManager::saveCustomer($oCustomer); //save object
                    // mail Customer with confirm email
                    $sTo = $oCustomer->email;
                    $oTemplate = TemplateManager::getTemplateByName('account_new_password');

                    // check if template exists
                    if (empty($oTemplate)) {
                        Debug::logError('', 'Template doesn\'t exist: `news password` (account_new_password)', __FILE__, __LINE__, '', Debug::LOG_IN_EMAIL);
                    } else {
                        $oTemplate->replaceVariables($oCustomer);
                        $sSubject = $oTemplate->getSubject();
                        $sMailBody = $oTemplate->getTemplate();
                        MailManager::sendMail($sTo, $sSubject, $sMailBody);
                    }

                    http_redirect('/' . http_get('controller') . '/' . http_get('param1') . '/edit');
                } else {
                    Debug::logError("", "Frontend Customer module php validate error", __FILE__, __LINE__, "Tried to reset customer's password with wrong values<br />" . _d($oCustomer, 1, 1), Debug::LOG_IN_EMAIL);
                }
            } else {
                $aErrorsForgotPass = array();
                $aErrorsForgotPass['general'] = 'Your account has been disabled, please <a href="/contact">contact</a> us for more info';
            }
        } else {
            $aErrorsForgotPass = array();
            $aErrorsForgotPass['email'] = 'Invalid email address';
        }
    }

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_forgotPassword.inc.php';
}

// confirm (activate) an account
elseif (http_get('param1') == 'confirm') {
    // redirect to edit page if Customer is logged in
    if (!empty($oCurrentCustomer) && is_numeric($oCurrentCustomer->customerId))
        http_redirect('/' . http_get('controller') . '/edit');

    // get page by urlPath (/account/bevestigen)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    $sConfirmCode = http_post('code', http_get('code'));
    $sEmail = http_post('email', http_get('email'));

    // confirm the account
    if (!empty($sConfirmCode) && !empty($sEmail) && CustomerManager::confirmCustomerByConfirmCodeAndEmail($sConfirmCode, $sEmail)) {
        unset($_SESSION['accountConfirmEmail']); // for no use after confirmation
        // redirect to Thank you page
        http_redirect('/' . http_get('controller') . '/created');
    } elseif (!empty($sConfirmCode) && !empty($sEmail)) {
        $aErrorsActivate = array();
        $aErrorsActivate['combinationUnknown'] = 'Email address and confirmation code don\'t match';
    }

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_confirm.inc.php';
}

// Thank you page after registering and confirming
elseif (http_get('param1') == 'created') {

    if (!empty($sReferrer)) {
        http_redirect($sReferrer);
    }

    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_confirmed.inc.php';
}

// logout
elseif (http_get('param1') == 'logout') {
    CustomerManager::logout($sReferrer);
}

//newsletter
elseif (http_get('param1') == 'newsletter') {

    if (http_post('action') === 'subscribeNewsletter') {
        $aErrors = array();

        if (!empty($oCurrentCustomer)) {
            CustomerManager::saveCustomerGroupRelation($oCurrentCustomer->customerId, CustomerGroup::CUSTOMERGROUP_NEWSLETTER);
            http_redirect('/' . http_get('controller') . '/' . http_get('param1') . '/thanks?conversion=1');
        } elseif (http_post('email')) {

            if (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) { // temporary removed for testing trap || hasLinks(http_post('bericht'))
                $aErrors['S-P-A-M'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
            }
            // end SPAM checks
            elseif (!filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
                $aErrors['email'] = 'You have entered an invalid e-mail address';
            } elseif (CustomerManager::getCustomerByEmail(http_post('email')) !== null) {
                $aErrors['email'] = 'There already was a registered user with that e-mail address. Please log in or use another e-mail address';
            } else {
                $oFakeCustomer = new Customer();
                $oFakeCustomer->name = http_post('name', 'Anonymous');
                $oFakeCustomer->email = http_post('email');
                $oFakeCustomer->companyName = http_post('companyName');
                $oFakeCustomer->city = 'NA';
                $oFakeCustomer->contactByEmail = 1;
                $oFakeCustomer->contactBySms = 0;
                $oFakeCustomer->gender = 'M';
                $oFakeCustomer->countryId = http_post('countryId', -1);
                $oFakeCustomer->online = 1;
                $oFakeCustomer->age = 0;
                $oFakeCustomer->password = 'XXXXXXXX';
                CustomerManager::saveCustomer($oFakeCustomer);
                CustomerManager::saveCustomerGroupRelation($oFakeCustomer->customerId, CustomerGroup::CUSTOMERGROUP_NEWSLETTER);
                http_redirect('/' . http_get('controller') . '/' . http_get('param1') . '/thanks?conversion=1');
            }
        }
    }

    // get page by urlPath (/account)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    if (http_get('param2') === 'thanks') {
        $oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';
    } else {
        $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_newsletter.inc.php';
    }
}

// login and register form
else {
    // redirect to edit page if Customer is logged in
    if (!empty($oCurrentCustomer))
        http_redirect('/' . http_get('controller') . '/edit');

    // get page by urlPath (/account)
    $oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

    if (empty($oPage) || !$oPage->online)
        showHttpError('404');

    // create Customer for registering
    $oCustomer = new Customer();

    // login
    if (http_post("action") == 'login') {
        $oCustomer = CustomerManager::getCustomerByEmail(http_post('email'));
        if (empty($oCustomer)) {
            $aErrorsLogin['general'] = 'You haven\'t signed up yet.';
            $oCustomer = new Customer();
        } elseif (!empty($oCustomer) && $oCustomer->online == "0") {
            $aErrorsLogin = array();
            $aErrorsLogin['general'] = 'Your account has been disabled, please <a href="/contact">contact</a> us for more info';
        } elseif (http_post('email') && http_post('password') && (CustomerManager::login(http_post('email'), http_post('password')))) {
            // redirect to referrer page
            http_redirect($sReferrer);
        } else {
            if (!http_post('email'))
                $aErrorsLogin['signup-login-email'] = 'You must enter a valid email address';
            elseif (!filter_var(http_post('email'), FILTER_VALIDATE_EMAIL))
                $aErrorsLogin['signup-login-email'] = 'You must enter a valid email address';
            if (!http_post('password'))
                $aErrorsLogin['signup-login-password'] = 'You must enter a password';
            if (!http_post('combination'))
                $aErrorsLogin['general'] = 'Password and email address don\'t match';
            $oCustomer = new Customer();
        }
    }

    // register
    if (http_post("action") == 'register') {
        // check password is valid
        if (http_post('password') && strlen(http_post('password')) >= 8) {
            $oCustomer->password = hashPasswordForDb(http_post('password'));
        } else {
            $oCustomer->password = null; //object validation will trigger error
        }

        $oCustomer->email = http_post('email');
        $oCustomer->name = http_post('name');
        $oCustomer->city = http_post('city');
        $oCustomer->countryId = http_post('countryId');
        $oCustomer->gender = http_post('gender');
        $oCustomer->age = http_post('age');

        // if object is valid, save
        if ($oCustomer->isValid()) {
            // create a unqiue confirmCode
            $oCustomer->confirmCode = substr(hash('sha512', uniqid(microtime() . $oCustomer->email, 1)), 15, 50); // I think it's safe to assume this code will be unique ^_^
            CustomerManager::saveCustomer($oCustomer); //save object
            // set email in session to prefill on site
            $_SESSION['accountConfirmEmail'] = $oCustomer->email;

            // mail Customer with confirm email
            $sTo = $oCustomer->email;

            $oTemplate = TemplateManager::getTemplateByName('account_confirm');

            // check if template exists
            if (empty($oTemplate)) {
                Debug::logError('', 'Template not found: `account confirm` (account_confirm)', __FILE__, __LINE__, '', Debug::LOG_IN_EMAIL);
            } else {
                $oTemplate->replaceVariables($oCustomer);
                $sSubject = $oTemplate->getSubject();
                $sMailBody = $oTemplate->getTemplate();
                MailManager::sendMail($sTo, $sSubject, $sMailBody);
            }

            http_redirect('/' . http_get('controller') . '/confirm');
        } else {
            $aErrorsSignup = array();
            if (!$oCustomer->isPropValid("email"))
                $aErrorsSignup['signup-email'] = 'You must enter a valid email address';
            if (!$oCustomer->isPropValid("emailExists"))
                $aErrorsSignup['signup-email'] = 'That email address is already in use';
            if (!$oCustomer->isPropValid("password"))
                $aErrorsSignup['signup-password'] = 'You must enter a valid password (min. 8 characters)';
            if (!$oCustomer->isPropValid("name"))
                $aErrorsSignup['signup-name'] = 'You must enter your name';
            if (!$oCustomer->isPropValid("city"))
                $aErrorsSignup['signup-city'] = 'You must enter your city';
            if (!$oCustomer->isPropValid("countryId"))
                $aErrorsSignup['signup-city'] = 'You must select your country';
            if (!$oCustomer->isPropValid("gender"))
                $aErrorsSignup['signup-city'] = 'You must choose a gender';
            if (!$oCustomer->isPropValid("age"))
                $aErrorsSignup['signup-city'] = 'You must enter your age';

            Debug::logError("", "Frontend Customer module php validate error", __FILE__, __LINE__, "Tried to save Customer with wrong values despite javascript check.<br />" . _d($oCustomer, 1, 1), Debug::LOG_IN_EMAIL);
        }
    }

    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    $oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

    $oPageLayout->sPagePath = PAGES_FOLDER . '/customer/customer_signUp.inc.php';
}

// include the error template
include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>