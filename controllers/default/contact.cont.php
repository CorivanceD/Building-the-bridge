<?php

/*
 * Controller to handle contact pages
 */
$oPageLayout = new PageLayout();
$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());
if (empty($oPage) || !$oPage->online) {
    showHttpError('404');
}

if ($oPage->level > 1)
    $oPageForMenu = PageManager::getPageByUrlPath('/' . http_get('controller'));
else
    $oPageForMenu = $oPage;

$oPageLayout->sWindowTitle = $oPage->getWindowTitle();
$oPageLayout->sMetaDescription = $oPage->getMetaDescription();
$oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

$aImages = $oPage->getImages();
$aYoutubeVideos = $oPage->getYoutubeLinks();
$aFiles = $oPage->getFiles();
$aLinks = $oPage->getLinks();

if (http_get('param1')) {
    $oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';
} else {

    if (http_post('action') == 'sendForm') {

        $aErrors = array();
        $bLogError = true;
        if (!http_post('name')) {
            $aErrors['name'] = 'You must enter your name';
        }
        if (!http_post('email')) {
            $aErrors['email'] = 'You must enter your email address';
        } elseif (!filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
            $aErrors['email'] = 'You\'ve entered an invalid email address';
        }
        if (!http_post('message')) {
            $aErrors['message'] = 'You must enter a message';
        }

        // do SPAM checks
        if (hasLinks(http_post('message'))) {
            $aErrors['message'] = 'Your message contains one or more links, this is not allowed. Remove them and try again.';
            $bLogError = false;
        }

        if (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) { // temporary removed for testing trap || hasLinks(http_post('bericht'))
            $aErrors['S-P-A-M'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
            $bLogError = false;
        }
        // end SPAM checks

        if (empty($aErrors)) {
            $sSubject = 'Contact formulier verstuurd';
            $sMail = '  <span style="font: 14px/22px Arial, sans-serif">
                            ' . _e(http_post('name')) . ' has filled in and sent the contact form. Below you can read the transmitted data.<br />
                            <br />
                            <table style="font: 14px/22px Arial, sans-serif">
                                <tr>
                                    <td>Name:</td>
                                    <td>' . _e(http_post('name')) . '</td>
                                </tr>
                                <tr>
                                    <td>Organization:</td>
                                    <td>' . _e(http_post('organisation')) . '</td>
                                </tr>
                                <tr>
                                    <td>Phone:</td>
                                    <td>' . _e(http_post('phone')) . '</td>
                                </tr>
                                <tr>
                                    <td>E-mail:</td>
                                    <td>' . _e(http_post('email')) . '</td>
                                </tr>
                                <tr>
                                    <td>Subject:</td>
                                    <td>' . _e(http_post('subject')) . '</td>
                                </tr>
                                <tr>
                                    <td>Message:</td>
                                    <td>' . nl2br(_e(http_post('message'))) . '</td>
                                </tr>
                              </table>
                            <br />
                            <br />
                            Please contact as soon as possible.<br />
                        </span>
                        ';

            MailManager::sendMail(CLIENT_DEFAULT_EMAIL_TO, $sSubject, $sMail, http_post('email'), http_post('email'));
            FormBackupManager::makeFormBackup('contactform', $_POST);

            http_redirect(http_get('controller') . '/thanks?conversion=1');
        } /*else {
            if ($bLogError)
                Debug::logError("", "Contact form on frontend validate error", __FILE__, __LINE__, "Tried to post the Contact form with wrong values despite javascript check (or rumpelstiltkin values are incorrect)." . print_r($_POST, 1), Debug::LOG_IN_EMAIL);
        }*/
    }

    $oPageLayout->sPagePath = PAGES_FOLDER . '/contact/contact_form.inc.php';
}

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>