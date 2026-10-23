<?php

# get micro time to check what time it takes to load the PHP
$iSMT = microtime(true);

# set document root
define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');

# for checking access in controllers
define("ACCESS", 'xXx');

# include configuration file
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

# login is required for all admin activities
include_once ADMIN_INC_FOLDER . '/loginControl.inc.php';

# get the controller from the $_GET ( is module name)
$sControllerRequest = strtolower(http_get("controller", ''));

# Key = 'module name', Value = 'path to controller file'
$aControllers = array(
    "" => ADMIN_FOLDER . "/controllers/home.cont.php",
    "login" => ADMIN_FOLDER . "/controllers/login.cont.php",
    "gebruikers" => ADMIN_FOLDER . "/controllers/user.cont.php",
    "modules" => ADMIN_FOLDER . "/controllers/module.cont.php",
    "paginas" => ADMIN_FOLDER . "/controllers/page.cont.php",
    "form-backups" => ADMIN_FOLDER . "/controllers/formBackup.cont.php",
    "imagemanagement" => ADMIN_FOLDER . "/controllers/imageManagement.cont.php",
    "filemanagement" => ADMIN_FOLDER . "/controllers/fileManagement.cont.php",
    "linkmanagement" => ADMIN_FOLDER . "/controllers/linkManagement.cont.php",
    "youtubelinkmanagement" => ADMIN_FOLDER . "/controllers/youtubeLinkManagement.cont.php",
    "crop" => ADMIN_FOLDER . "/controllers/crop.cont.php",
    "brandbox" => ADMIN_FOLDER . "/controllers/brandboxItem.cont.php",
    "instellingen" => ADMIN_FOLDER . "/controllers/settings.cont.php",
    "templates-beheer" => ADMIN_FOLDER . "/controllers/template.cont.php",
    "nieuws" => ADMIN_FOLDER . "/controllers/newsItem/newsItem.cont.php",
    "nieuws-categorieen" => ADMIN_FOLDER . "/controllers/newsItem/newsItemCategory.cont.php",
    "system-translations" => ADMIN_FOLDER . "/controllers/systemTranslation.cont.php",
    "rss" => ADMIN_FOLDER . "/controllers/rssFeeds.cont.php",
    "articles" => ADMIN_FOLDER . "/controllers/article/article.cont.php",
    "article-comments" => ADMIN_FOLDER . "/controllers/article/articleComments.cont.php",
    "article-categories" => ADMIN_FOLDER . "/controllers/article/articleCategory.cont.php",
    "article-review" => ADMIN_FOLDER . "/controllers/article/articleReview.cont.php",
    "klanten" => ADMIN_FOLDER . "/controllers/customer.cont.php",
    "klant-groepen" => ADMIN_FOLDER . "/controllers/customerGroups.cont.php",
    "klanten-import" => ADMIN_FOLDER . "/controllers/customerImport.cont.php",
    "communicatie" => ADMIN_FOLDER . "/controllers/communication.cont.php",
    "petitions" => ADMIN_FOLDER . "/controllers/petitions.cont.php",
    "update-rss-feeds" => ADMIN_FOLDER . "/controllers/updateRssFeeds.cont.php",
    "chatmanagement" => ADMIN_FOLDER . "/controllers/chatManagement.cont.php",
    "rss-pages" => ADMIN_FOLDER . "/controllers/rssPage.cont.php",
    "logos" => ADMIN_FOLDER . "/controllers/newsSourceLogo.cont.php",
    "correspondenties-wereldleiders" => ADMIN_FOLDER . "/controllers/worldLeaderLetters/worldLeaderLetters.cont.php",
    "import-world-leaders" => ADMIN_FOLDER . "/controllers/worldLeaderLetters/importWorldLeaders.cont.php",
    "letter-review" => ADMIN_FOLDER . "/controllers/worldLeaderLetters/letterReview.cont.php",
    "letter-comments" => ADMIN_FOLDER . "/controllers/worldLeaderLetters/letterComments.cont.php",
    "wereldleiders" => ADMIN_FOLDER . "/controllers/worldLeaders/worldLeaders.cont.php",
    "link-tool" => ADMIN_FOLDER . "/controllers/linkTool.cont.php",
);

$aExceptions = array("login", "imagemanagement", "crop", "filemanagement", "linkmanagement", "youtubelinkmanagement", "update-rss-feeds", "import-world-leaders");

# if there is a controller request, check rights and access
if ($sControllerRequest) {
    # module check
    if (!in_array($sControllerRequest, $aExceptions)) {
        # if not an exception, check user rights
        if (!$oCurrentUser->hasRightsFor($sControllerRequest) || !ModuleManager::isActive($sControllerRequest)) {
            showHttpError(403);
        }
    }
}

# get the controller 'path to file' from the controller array
if (array_key_exists($sControllerRequest, $aControllers)) {
    $sControllerPath = $aControllers[$sControllerRequest];
} else {
    # Path found? GO
    if (empty($sControllerPath) && !empty($sControllerRequest)) {
        # non existing controller request and page
        showHttpError(404);
    }
}

if (file_exists(DOCUMENT_ROOT . $sControllerPath)) {
    include_once DOCUMENT_ROOT . $sControllerPath;
} else {
    showHttpError(404);
}

# get micro time to check what time it takes to load the PHP
$iEMT = microtime(true);
if (DEBUG) {
    //echo "Time to load PHP: " . ($iEMT - $iSMT) . " sec";
}

?>