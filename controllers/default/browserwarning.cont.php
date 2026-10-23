<?php

# set showbrowserwarning if someone wants to view the website with their current browser
$oPageLayout = new PageLayout();

$bDontShowBrowserWarning = http_session('bDontShowBrowserWarning');

if (http_get("action") == 'dontShowBrowserWarning') {
    $bDontShowBrowserWarning = true;
    $_SESSION['bDontShowBrowserWarning'] = true;
}

# don't show browserwarning if normal view is wanted
if ($bDontShowBrowserWarning) {
    $sReferrer = http_session("sBrowserWarningReferrer", "/");
    http_redirect($sReferrer);
}

# include the warning template
include_once TEMPLATES_FOLDER . '/browserwarning.tmpl.php';
?>