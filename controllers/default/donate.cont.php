<?php


/*
 * Controller to handle normal content pages
 */

if (http_post('action', false) === 'sendForm') {
    MailManager::sendMail(CLIENT_DEFAULT_EMAIL_TO, 'Donation', http_post('name').' wants to make a donation. You can contact it at <a href="mailto:'.http_post('email').'">'.http_post('email').'</a>');
    http_redirect('/'.http_get('controller').'/thanks');
}
$oPageLayout = new PageLayout();

$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

if (empty($oPage)) {
    $oPage = PageManager::getPageByUrlPath('/' . http_get('controller'));
}

if (empty($oPage) || !$oPage->online) {
    showHttpError('404');
}

if ($oPage->level > 1)
    $oPageForMenu = PageManager::getPageByUrlPath('/' . http_get('controller'));
else
    $oPageForMenu = $oPage;

$oPageLayout->sPagePath = PAGES_FOLDER . '/donations/donation_form.inc.php';
$oPageLayout->sWindowTitle = $oPage->getWindowTitle();
$oPageLayout->sMetaDescription = $oPage->getMetaDescription();
$oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
//$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());
$oPageLayout->sOGType = 'website';
$oPageLayout->sOGTitle = $oPage->getWindowTitle();
$oPageLayout->sOGDescription = $oPage->getMetaDescription();
$oPageLayout->sOGUrl = getCurrentUrl();

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>