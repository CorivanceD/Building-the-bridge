<?php

set_time_limit(0);

// set docuemnt root
if (!defined('DOCUMENT_ROOT')) {
    define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
}
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

$sReferenceName = 'updateRssFeeds';

// check if cron lock is set
if (CronManager::isLocked($sReferenceName)) {
    if (DEBUG)
        echo 'Cron locked `' . $sReferenceName . '`';
    exit();
}
// check if cron lock can be set
if (!CronManager::setCronLock($sReferenceName)) {
    if (DEBUG)
        echo 'Kon cron lock niet setten `' . $sReferenceName . '`';
    else
        Debug::logError('0', 'CRON ERROR', __FILE__, __LINE__, 'Kon cron lock niet setten `' . $sReferenceName . '`', Debug::LOG_IN_EMAIL);
    exit();
}
// log cron starting
CronManager::log('cron started', $sReferenceName . '.log');


$aRssFeeds = RssFeedManager::getRssFeedsByFilter(array("showAll"=>1));

foreach ($aRssFeeds AS $oRssFeed) {
    $oLastUpdate = new Date($oRssFeed->lastUpdate, Date::FORMAT_DB);
    CronManager::log('Checking "'.$oRssFeed->title.'" - last update ('.$oLastUpdate->format('%d-%m-%Y %H:%M').')', $sReferenceName . '.log');
    if ( time() >= ($oLastUpdate->iTime + $oRssFeed->updateInterval * 60)) {
        $oRssFeed->load();
        CronManager::log('"'.$oRssFeed->title.'" updated', $sReferenceName . '.log');
    }
    else {
        CronManager::log('"'.$oRssFeed->title.'" not updated', $sReferenceName . '.log');
    }
}

// check if cron can be unlocked
if (!CronManager::unsetCronLock($sReferenceName)) {
    if (DEBUG)
        echo 'Kon cron lock niet verwijderen `' . $sReferenceName . '`';
    else
        Debug::logError('0', 'CRON ERROR', __FILE__, __LINE__, 'Kon cron lock niet verwijderen `' . $sReferenceName . '`', Debug::LOG_IN_EMAIL);
    exit();
}
// log cron ending
CronManager::log('cron ended', $sReferenceName . '.log');
?>