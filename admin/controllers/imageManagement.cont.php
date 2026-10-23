<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# Controller is only used for ajax and redirecting

if (http_get("param1") == 'ajax-delete') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iImageId = http_post("imageId");
    if (is_numeric($iImageId)) {
        $oImage = ImageManager::getImageById($iImageId);
        if ($oImage->isDeletable() === true) {
            if (ImageManager::deleteImage($oImage)) {
                $oResObj->imageId = $iImageId;
                $oResObj->success = true;
            }
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-setOnline') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iImageId = http_post("imageId");
    $iOnline = http_post("online");
    if (is_numeric($iImageId)) {
        $oImage = ImageManager::getImageById($iImageId);
        if ($oImage->isOnlineChangeable()) {
            $oImage->online = $iOnline;
            ImageManager::saveImage($oImage);

            // also update imagefiles online value
            ImageManager::updateImageFilesOnlineByImage($iOnline, $iImageId);
            $oResObj->imageId = $iImageId;
            $oResObj->online = ($iOnline ? 0 : 1 );
            $oResObj->success = true;
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-edit') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $iImageId = http_post("imageId");
    $sTitle = http_post("title");
    if (is_numeric($iImageId)) {
        $oImage = ImageManager::getImageById($iImageId);
        if ($oImage->isEditable() === true) {
            ImageManager::updateImageFilesTitleByImage($sTitle, $iImageId);
            $oResObj->imageId = $iImageId;
            $oResObj->title = $sTitle;
            $oResObj->success = true;
        }
    }

    print json_encode($oResObj);
} elseif (http_get("param1") == 'ajax-saveOrder') {

    $oResObj = new stdClass();
    $oResObj->success = false;

    $sImageIds = http_post("imageIds"); // get image ids komma seperated
    $aImageIds = explode(",", $sImageIds); // explode ids to array
    # update order
    ImageManager::updateImageOrder($aImageIds);
    $oResObj->imageId = $aImageIds[0]; // return first imageId for highlighting changed images
    $oResObj->success = true;

    print json_encode($oResObj);
}
?>