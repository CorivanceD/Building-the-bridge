<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('news_source_logos');
$oPageLayout->sModuleName = sysTranslations::get('news_source_logos');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle newsSourceLogoFilter
$aNewsSourceLogoFilter = http_session('newsSourceLogoFilter');
if (http_post('filterForm')) {
    $aNewsSourceLogoFilter = http_post('newsSourceLogoFilter');
    $_SESSION['newsSourceLogoFilter'] = $aNewsSourceLogoFilter;
}

if (http_post('resetFilter') || empty($aNewsSourceLogoFilter)) {
    unset($_SESSION['newsSourceLogoFilter']);
    $aNewsSourceLogoFilter = array();
    $aNewsSourceLogoFilter['sourceName'] = '';
}

# handle perPage
if (http_post('setPerPage')) {
    $_SESSION['newsSourceLogosPerPage'] = http_post('perPage');
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {


    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oNewsSourceLogo = NewsSourceLogoManager::getNewsSourceLogoById(http_get("param2"));
        if (empty($oNewsSourceLogo))
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oNewsSourceLogo = new NewsSourceLogo();
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oNewsSourceLogo->_load($_POST);
        
        # if object is valid, save
        if ($oNewsSourceLogo->isValid()) {
            NewsSourceLogoManager::saveNewsSourceLogo($oNewsSourceLogo); //save object
            $_SESSION['statusUpdate'] = sysTranslations::get('news_source_logo_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oNewsSourceLogo->newsSourceLogoId);
        } else {
            Debug::logError("", "NewsSourceLogo module php validate error", __FILE__, __LINE__, "Tried to save NewsSourceLogo with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $_SESSION['statusUpdate'] = sysTranslations::get('news_source_logo_not_saved');
        }
    }

    if (http_post('action') == 'saveImage') {
        $oUpload = new Upload($_FILES['image'], NewsSourceLogo::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'png', 'gif', 'jpeg'), true);

        # save image to database on success
        if ($oUpload->bSuccess === true) {

            // resize original to acceptable size
            if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1200, 280, $sErrorMsg)) {
                $_SESSION['accountStatusUpdate'] = 'Image cannot be resized: ' . $soErrorMsg; //error resizing image
            }

            # make Image object and save
            $oImage = new Image();
            $oImageFile = new ImageFile();
            $oImageFile->link = $oUpload->sNewFilePath;
            $oImageFile->title = http_post('title', '');
            $oImageFile->name = $oUpload->sNewFileBaseName;
            $oImageFile->mimeType = $oUpload->sMimeType;
            $oImageFile->size = filesize(DOCUMENT_ROOT . $oImageFile->link);
            $oImageFile->reference = 'original';
            $aImageFiles[] = $oImageFile;

            # make cms thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . NewsSourceLogo::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 120, 28, $sErrorMsg, false)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = NewsSourceLogo::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'cms_thumb';

                $aImageFiles[] = clone $oImageFileThumb;
            } else {
                $_SESSION['accountStatusUpdate'] = '"cms_thumb" cannot be created: ' . $sErrorMsg; //error cropping image
            }

            $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
            # save image
            ImageManager::saveImage($oImage);
            # save page image relation
            $oNewsSourceLogo->imageId = $oImage->imageId;
            NewsSourceLogoManager::saveNewsSourceLogo($oNewsSourceLogo);
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oNewsSourceLogo->newsSourceLogoId);
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oNewsSourceLogo->newsSourceLogoId);
        }
    }

    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->iMaxImages = 1;
    $oImageManagerHTML->cropable= false;
    $oImageManagerHTML->onlineChangeable = false;
    $oImageManagerHTML->aImages = $oNewsSourceLogo->getImage() == null ? array() : array($oNewsSourceLogo->getImage());
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oNewsSourceLogo->newsSourceLogoId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb');
    $oImageManagerHTML->bCropAfterUploadChecked = false;
    $oImageManagerHTML->bShowCropAfterUploadOption = false;
    $oImageManagerHTML->iContainerIDAddition = '2';
    $oImageManagerHTML->sExtraUploadLine = 'Recommended size: <b>120px</b> x <b>28px</b>';
    

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/newsSourceLogos/news_source_logo_form.inc.php';
}
# delete object
elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2")))
        $oNewsSourceLogo = NewsSourceLogoManager::getNewsSourceLogoById(http_get("param2"));

    if (!empty($oNewsSourceLogo) && NewsSourceLogoManager::deleteNewsSourceLogo($oNewsSourceLogo->newsSourceLogoId)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('news_source_logo_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('news_source_logo_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

# display overview
else {
    $iPerPage = http_session('newsSourceLogosPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    $aNewsSourceLogos = NewsSourceLogoManager::getNewsSourceLogosByFilter($aNewsSourceLogoFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/newsSourceLogos/news_source_logos_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>