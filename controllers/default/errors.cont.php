<?php

/*
 * controller for handeling the error pages
 * This is a very special controller that is included on other places beside the index
 */

# page layout object for layout properties
$oPageLayout = new PageLayout();

/* the errornumber can be set higher in the document includes
 * if no number is set, try to get one from the get
 * otherwise set to 404 because the error is 'Not Found'
 */
if (empty($iErrorNr)) {
    $iErrorNr = http_get("param1");
    if ($iErrorNr == '') {
        $iErrorNr = 404;
    }
}

// no errors are logged by default
$bLogError = false;

// some file extensions needs to be logged always and some only from own website
$aExtensionsToLog = array(
    'php',
    'html',
    'xhtml',
    'htm',
    ''
);
$sExtension = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION);

// if it is one of the controllers check if request came from own website and then log
if (!in_array($sExtension, $aExtensionsToLog) && isset($_SERVER['HTTP_REFERER'])) {
    $sHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    if ($sHost && isset($_SERVER['HTTP_HOST']) && $sHost == $_SERVER['HTTP_HOST']) {
        $bLogError = true;
    }
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    // do log if it's a physical link to this location
    $bLogError = true;
}

// Log error if DEBUG is set to true
if (DEBUG)
    $bLogError = true;

$sExtraInfo = '';
switch ($iErrorNr) {
    case 307:
        header($_SERVER['SERVER_PROTOCOL'] . " 307 Temporary Redirect"); //set the header to make it a real error
        $oPageLayout->sWindowTitle = CLIENT_NAME . ' - HTTP-statuscode: 307';
        $iErrorMessage = "Pagina of bestand tijdelijk verplaatst";
        $iErrorDescription = "De opgevraagde content is tijdelijk verplaatst!";
        $sExtraInfo = 'Link: ' . getCurrentUrl() . '<br />';
        break;
    case 403:
        header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden"); //set the header to make it a real error
        $oPageLayout->sWindowTitle = CLIENT_NAME . ' - HTTP-statuscode: 403';
        $iErrorMessage = "Verboden toegang";
        $iErrorDescription = "De opgevraagde content is beschermd!";
        $sExtraInfo = 'Link: ' . getCurrentUrl() . '<br />';
        break;
    case 405:
        header($_SERVER['SERVER_PROTOCOL'] . " 405 Method Not Allowed"); //set the header to make it a real error
        $oPageLayout->sWindowTitle = CLIENT_NAME . ' - HTTP-statuscode: 405';
        $iErrorMessage = "METHOD niet toegestaan";
        $iErrorDescription = "De pagina is met een niet toegestane METHOD aangeroepen";
        $bLogError = false;
        break;
    case 404:
    default:
        header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found"); //set the header to make it a real error
        $oPageLayout->sWindowTitle = CLIENT_NAME . ' - HTTP-statuscode: 404';
        $iErrorMessage = "Pagina of bestand niet gevonden";
        $iErrorDescription = "De opgevraagde content bestaat niet (meer)!";
        $sExtraInfo = 'Link: ' . getCurrentUrl() . '<br />';
        $sExtraInfo .= 'Page with link: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-');
        break;
}

# check if error need to be logged
if ($bLogError) {
    # send email with error details
    Debug::logError($iErrorNr, "http Error", __FILE__, __LINE__, $sExtraInfo, Debug::LOG_IN_DATABASE);

    // only log errors to email if debug is true or DO_NOT_EMAIL_HTTP_ERRORS = false
    if (DEBUG || !DO_NOT_EMAIL_HTTP_ERRORS)
        Debug::logError($iErrorNr, "http Error", __FILE__, __LINE__, $sExtraInfo, Debug::LOG_IN_EMAIL);
}

# include the error template
include_once TEMPLATES_FOLDER . '/errorTemplate.tmpl.php';
?>