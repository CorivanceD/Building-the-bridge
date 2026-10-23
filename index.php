<?php

# get micro time to check what time it takes to load the PHP
$iSMT = microtime(true);

// To see what the document root is you can use next line (not working with cron jobs)
//echo $_SERVER['DOCUMENT_ROOT'];
# set document root
// set document root
if (!defined('DOCUMENT_ROOT')) {
    define("DOCUMENT_ROOT", dirname(__FILE__));
}

include_once DOCUMENT_ROOT . '/inc/config.inc.php';

# get the controller from the $_GET
$sControllerRequest = strtolower(http_get("controller", ''));

/* save referrer for redirecting after browserwarning */
if ($sControllerRequest != 'browserwarning' && empty($_SESSION['bDontShowBrowserWarning'])) {
    $_SESSION['sBrowserWarningReferrer'] = getCurrentUrlPath(true, true);
} elseif (!empty($_SESSION['bDontShowBrowserWarning'])) {
    unset($_SESSION['sBrowserWarningReferrer']);
}

if (class_exists('CustomerManager')) {
    $oCurrentCustomer = http_session('oCurrentCustomer', null);
}

// Key = 'controller name', Value = 'path to file' ()
$aControllers = array(
    "errors" => "/errors.cont.php",
    "browserwarning" => "/browserwarning.cont.php",
    "sitemap" => "/sitemap.cont.php",
    "the-great-debate" => "/page" . http_get("amp") . ".cont.php",   
    "common-ground" => "/page" . http_get("amp") . ".cont.php",
    "world-leaders" => "/page" . http_get("amp") . ".cont.php", 
    "news-palestine" => "/page" . http_get("amp") . ".cont.php", 
    "news-israel" => "/page" . http_get("amp") . ".cont.php", 
    "global-perspective" => "/page" . http_get("amp") . ".cont.php",  
    "confirm-comment" => "/page.cont.php",
    "account" => "/customer.cont.php",
    "petitions" => "/signPetition.cont.php",
    "rss" => "/rss.cont.php",
    "chat" => "/chat.cont.php",
    "afmelden" => "/afmelden.cont.php",
    "newsletter-redirect" => "/newsletter-redirect.cont.php",
    "links" => "/link.cont.php",
);

# get the controller 'path to file' from the controller array
if (array_key_exists($sControllerRequest, $aControllers)) {
    $sControllerPath = $aControllers[$sControllerRequest];
} else {
    /*
     * Check if class could exists
     * Then search for a matching page in the Database
     * if a pageControllerPath is found, take this one for displaying
     */
    $sControllerPath = PageManager::getControllerPathByUrlPath(getCurrentUrlPath());

# also try pageRedirects table
    if (empty($sControllerPath)) {
        $sPageRedirectUrlPath = PageManager::getRedirectUrlPathByUrlPath(getCurrentUrlPath());

# redirect if there is a path
        if ($sPageRedirectUrlPath) {
# 301 redirect
            http_redirect($sPageRedirectUrlPath, false, true);
        }
    }

# Path not found? show 404 error
    if (empty($sControllerPath)) {
# non existing page
        showHttpError(404);
    }
}

if (file_exists(DOCUMENT_ROOT . '/controllers' . PATH_PREFIX . $sControllerPath)) {
    include_once DOCUMENT_ROOT . '/controllers' . PATH_PREFIX . $sControllerPath;
} else {
    showHttpError(404);
}

if (class_exists('CustomerManager')) {
# save referrer for redirecting after login
    if (http_get('controller') != 'account') {
        $_SESSION['frontendLoginReferrer'] = getCurrentUrl();
    }
}


# get micro time to check what time it takes to load the PHP
$iEMT = microtime(true);
if (DEBUG) {
//echo "Time to load PHP: " . ($iEMT - $iSMT) . " sec";
}
?>