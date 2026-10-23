<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# Controller is only used for ajax and redirecting

if (http_get("param1") == 'ajax-delete') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iMediaId = http_post("mediaId");
    if (is_numeric($iMediaId)) {
        $oLink = LinkManager::getLinkById($iMediaId);
        if ($oLink && $oLink->isDeletable() === true) {
            if (LinkManager::deleteLink($oLink)) {
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
        $oLink = LinkManager::getLinkById($iMediaId);
        if ($oLink->isOnlineChangeable()) {
            $oLink->online = $iOnline;
            LinkManager::saveLink($oLink);
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
        $oLink = LinkManager::getLinkById($iMediaId);
        if ($oLink->isEditable() === true) {
            $oLink->title = $sTitle;
            $oLink->link = $sLink;
            LinkManager::saveLink($oLink);
            $oResObj->mediaId = $iMediaId;
            $oResObj->title = $oLink->title;
            $oResObj->link = $oLink->link;
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
    LinkManager::updateLinkOrder($aMediaIds);
    $oResObj->success = true;

    print json_encode($oResObj);
}
?>