<?php

/*
 * controller to get all data for homepage
 */
# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

if ($oCurrentUser->isAdmin()) {

    include DOCUMENT_ROOT . CronManager::CRONJOBS_FOLDER . '/updateRssFeeds.cron.php';
    echo 'OK!';
    die();
}
?>