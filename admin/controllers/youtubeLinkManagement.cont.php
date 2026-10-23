<?php

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# Controller is only used for ajax and redirecting

if (http_get("param1") == 'ajax-delete') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iMediaId = http_post("mediaId");
    if (is_numeric($iMediaId)) {
        $oYoutubeLink = YoutubeLinkManager::getYoutubeLinkById($iMediaId);
        if ($oYoutubeLink->isDeletable() === true) {
            if (YoutubeLinkManager::deleteYoutubeLink($oYoutubeLink)) {
                $oResObj->mediaId = $iMediaId;
                $oResObj->success = true;
            }
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-setOnline') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iMediaId = http_post("mediaId");
    $iOnline = http_post("online");
    if (is_numeric($iMediaId)) {
        $oYoutubeLink = YoutubeLinkManager::getYoutubeLinkById($iMediaId);
        if ($oYoutubeLink->isOnlineChangeable()) {
            $oYoutubeLink->online = $iOnline;
            YoutubeLinkManager::saveYoutubeLink($oYoutubeLink);
            $oResObj->mediaId = $iMediaId;
            $oResObj->online = ($iOnline ? 0 : 1 );
            $oResObj->success = true;
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-edit') {

    $oResObj = new stdClass();
    $oResObj->success = false;
    $iMediaId = http_post("mediaId");
    $sTitle = http_post("title");
    $sLink = http_post("link");
    if (is_numeric($iMediaId)) {
        $oYoutubeLink = YoutubeLinkManager::getYoutubeLinkById($iMediaId);
        
        if ($oYoutubeLink->isEditable() === true) {
            $oYoutubeLink->title = $sTitle;
            $oYoutubeLink->link = $sLink;
            YoutubeLinkManager::saveYoutubeLink($oYoutubeLink);
            $oResObj->mediaId = $iMediaId;
            $oResObj->title = $oYoutubeLink->title;
            $oResObj->link = $oYoutubeLink->link;
            $oResObj->embedLink = $oYoutubeLink->getEmbedLink();
            $oResObj->thumbLink0 = $oYoutubeLink->getThumbLink(0);
            $oResObj->thumbLink1 = $oYoutubeLink->getThumbLink(1);
            $oResObj->thumbLink2 = $oYoutubeLink->getThumbLink(2);
            $oResObj->thumbLink3 = $oYoutubeLink->getThumbLink(3);
            $oResObj->success = true;
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-saveOrder') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $sMediaIds = http_post("mediaIds"); // get media ids komma seperated
    $aMediaIds = explode(",", $sMediaIds); // explode ids to array
    # update order
    YoutubeLinkManager::updateYoutubeLinkOrder($aMediaIds);
    $oResObj->success = true;

    print json_encode($oResObj);
}
?>