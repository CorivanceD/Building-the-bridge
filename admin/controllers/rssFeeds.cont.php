<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;


# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_rss_feeds');
$oPageLayout->sModuleName = sysTranslations::get('global_rss_feeds');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once

if (http_get("param1") != 'bewerken' && http_get("param1") != 'toevoegen') {
    // handle perPage
    if (http_post('setPerPage')) {
        $_SESSION['rssFeedsPerPage'] = http_post('perPage');
    }

    // handle filter
    $aRssFeedFilter = http_session('rssFeedFilter');
    if (http_post('filterRssFeeds')) {
        $aRssFeedFilter = http_post('rssFeedFilter');
        $_SESSION['rssFeedFilter'] = $aRssFeedFilter;
    }

    if (http_post('resetFilter') || empty($aRssFeedFilter)) {
        unset($_SESSION['rssFeedFilter']);
        $aRssFeedFilter = array();
        $aRssFeedFilter['q'] = '';
    }
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {
        
    if (http_post('setPerPage')) {
        $_SESSION['rssFeedItemsPerPage'] = http_post('perPage');
    }
    # handle rss feed item filter

    $aRssFeedItemFilter = http_session('rssFeedItemFilter');
    if (http_post('filterRssFeedItems')) {
        $aRssFeedItemFilter = http_post('rssFeedItemFilter');
        $_SESSION['rssFeedItemFilter'] = $aRssFeedItemFilter;
    }

    if (http_post('resetFilter') || empty($aRssFeedItemFilter)) {
        unset($_SESSION['rssFeedItemFilter']);
        $aRssFeedItemFilter = array();
        $aRssFeedItemFilter['q'] = '';
    }

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oRssFeed = RssFeedManager::getRssFeedById(http_get("param2"));

        if (!$oRssFeed) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
        
        $iPerPage = http_session('rssFeedItemsPerPage', 10);
        $iCurrPage = http_get('page', 1);
        $iStart = (($iCurrPage - 1) * $iPerPage);
        if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }

        #display rssFeedItems overview
        $iFoundRows = 0;
        $aRssFeedItems = $oRssFeed->getRssFeedItems($aRssFeedItemFilter, $iPerPage, $iStart, $iFoundRows);
        $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    } else {
        $oRssFeed = new RssFeed();
        $aRssFeedItems = array();
    }

    # action = save
    if (http_post("action") == 'load' && http_post("link", null) !== null) {
        # load data in object
        //$oRssFeed->link = http_post('link');
        $oRssFeed->_load($_POST);
        $oRssFeed->updateInterval = http_post('updateInterval', 15);
        
        RssFeedManager::saveRssFeed($oRssFeed);
        
       // $oRssFeed->load();
        $_SESSION['statusUpdate'] = sysTranslations::get('rss_feed_loaded'); //save status update into session
        
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oRssFeed->rssFeedId);
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/rss/rssFeed_form.inc.php';
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {

    if (is_numeric(http_get("param2"))) {
        $oRssFeed = RssFeedManager::getRssFeedById(http_get("param2"));
    }

    if ($oRssFeed && RssFeedManager::deleteRssFeed($oRssFeed->rssFeedId)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('rss_feed_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('rss_feed_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} else {
    unset($_SESSION['rssFeedItemFilter']);
    $iPerPage = http_session('rssFeedsPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    #display rssFeeds overview
    $aRssFeeds = RssFeedManager::getRssFeedsByFilter($aRssFeedFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/rss/rssFeed_overview.inc.php';
}

# inlcude template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>