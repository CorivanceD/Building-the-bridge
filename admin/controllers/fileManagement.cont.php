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
        $oFile = FileManager::getFileById($iMediaId);
        if ($oFile->isDeletable() === true) {
            if (FileManager::deleteFile($oFile)) {
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
        $oFile = FileManager::getFileById($iMediaId);
        if ($oFile->isOnlineChangeable()) {
            $oFile->online = $iOnline;
            FileManager::saveFile($oFile);
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
    if (is_numeric($iMediaId)) {
        $oFile = FileManager::getFileById($iMediaId);
        if ($oFile->isEditable() === true) {
            $oFile->title = $sTitle;
            FileManager::saveFile($oFile);
            $oResObj->mediaId = $iMediaId;
            $oResObj->title = $sTitle;
            $oResObj->name = $oFile->name;
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
    FileManager::updateFileOrder($aMediaIds);
    $oResObj->success = true;

    print json_encode($oResObj);
}
?>