<?php

# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('crop_crop');
$oPageLayout->sModuleName = sysTranslations::get('crop_crop');
$oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/crop/crop_form.inc.php';

# add Jcrop style sheet
$oPageLayout->addStylesheet('<link href="' . ADMIN_PLUGINS_FOLDER . '/jCrop/jquery.Jcrop.css" rel="stylesheet">');

# add Jcrop code
$oPageLayout->addJavascriptBottom('<script src="' . ADMIN_PLUGINS_FOLDER . '/jCrop/jquery-jcrop.min.js"></script>');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once

$aCropSettings = http_session('aCropSettings');

// set specific crop
if (http_post('action') == 'setCrop') {
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '?crop=' . http_post('crop'));
}
$iCropCurrent = http_get('crop', 0);
$iCropPrev = $iCropCurrent > 0 ? $iCropCurrent - 1 : false;
$iCropNext = $iCropCurrent < count((array) $aCropSettings) - 1 ? $iCropCurrent + 1 : false;

# check if cropsettings is richt type
if (!(($aCropSettings[$iCropCurrent] ?? null) instanceof CropSettings)) {
    http_redirect(ADMIN_FOLDER . "/");
}

# get image if there is one
$oImage = ImageManager::getImageById($aCropSettings[$iCropCurrent]->iImageId);

if ($oImage->isCropable() !== true) {
    die(sysTranslations::get('crop_not_allowed'));
}

// set if all needed crops are made
$aNeededCrops = array();
foreach ($aCropSettings[$iCropCurrent]->getCrops() AS $aCrop) {
    $aNeededCrops[] = $aCrop[3]; // reference name (fe crop_small)
}
$bHasNeededCrops = $oImage->hasImageFiles($aNeededCrops);

# count crops 2 go
$iCrops2Go = count($aCropSettings) - $iCropCurrent - 1;

# get imageFile by reference
if ($oImage) {
    $oOriginalImageFile = $oImage->getImageFileByReference($aCropSettings[$iCropCurrent]->sReferenceName);
}
if (!$oOriginalImageFile) {
    die(sysTranslations::get('crop_no_image_to_crop'));
}

# do crop image
if (http_post("action") == "crop") {

    $iCx = http_post("x");
    $iCy = http_post("y");
    $iCw = http_post("w");
    $iCh = http_post("h");

    # check all required values
    if ($iCx != '' && $iCy != '' && $iCw != '' && $iCh != '') {
        # make crops
        foreach ($aCropSettings[$iCropCurrent]->getCrops() AS $aCropInfo) {
            list($iCropW, $iCropH, $sCropLocation, $sReferenceName, $bShowUnderCropBox, $bAbsoluteSize, $iJpegQuality) = $aCropInfo;
            if (ImageManager::cropImage(DOCUMENT_ROOT . $oOriginalImageFile->link, DOCUMENT_ROOT . $sCropLocation, $iCropW, $iCropH, $iCx, $iCy, $iCw, $iCh, $sErrorMsg, $bAbsoluteSize, $iJpegQuality)) {
                # get imageFile if one exists
                $oImageFile = $oImage->getImageFileByReference($sReferenceName);
                if (!$oImageFile) {
                    $oImageFile = clone $oOriginalImageFile;
                    $oImageFile->mediaId = null; // reset mediaId
                    $oImageFile->link = $sCropLocation; // set link to crop location
                    $oImageFile->reference = $sReferenceName; // set reference name
                }
                $oImageFile->size = filesize(DOCUMENT_ROOT . $oImageFile->link); // set size
                # save imageFile
                ImageManager::saveImageFile($oImageFile);
            } else {
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
                $_SESSION['statusUpdate'] = sysTranslations::get('crop_couldnt_cut') . $sErrorMsg; // error occured
            }
        }

        # set status update
        $_SESSION['statusUpdate'] = sysTranslations::get('crop_cuts_saved');

        # to next image
        if (http_post('saveAndNext')) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '?crop=' . $iCropNext);
        }

        # back to crop screen
        if (http_post('apply')) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '?crop=' . $iCropCurrent);
        }

        # back to referrerUrl
        if (http_post('save')) {
            http_redirect($aCropSettings[$iCropCurrent]->sReferrer);
        }
    } else {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        $_SESSION['statusUpdate'] = sysTranslations::get('crop_cut_not_created') . $sErrorMsg; // error occured
    }
}


# cropbox size is set
if ($aCropSettings[$iCropCurrent]->getCropBoxSize() !== null) {
    list($iCx, $iCy, $iCw, $iCh) = $aCropSettings[$iCropCurrent]->getCropBoxSize();
}
# set cropBoxSize here
elseif ($aCropSettings[$iCropCurrent]->bAutoCropBoxSize === true && $aCropSettings[$iCropCurrent]->getAspectRatio() !== null) {
    # ratio is set, auto biggest cropbox
    ImageManager::getAutoCropBoxSize(DOCUMENT_ROOT . $oOriginalImageFile->link, $aCropSettings[$iCropCurrent]->getAspectRatio(), $iCx, $iCy, $iCw, $iCh);
} else {
    # cropbox equals 75% image size and is displayed centered
    list($iCw, $iCh) = getimagesize(DOCUMENT_ROOT . $oOriginalImageFile->link);
    $iCx = $iCw * 0.25 / 2;
    $iCy = $iCh * 0.25 / 2;
    $iCw = $iCw * 0.75;
    $iCh = $iCh * 0.75;
}

# set aspect ratio if needed
if ($aCropSettings[$iCropCurrent]->getAspectRatio() !== null) {
    $sAspectRatio = 'aspectRatio: ' . (float) $aCropSettings[$iCropCurrent]->getAspectRatio() . ',';
}

# get image size for proper cropping from browser resized image
list($iImageW, $iImageH) = getimagesize(DOCUMENT_ROOT . $oOriginalImageFile->link);

# set x2/y2 to right value
$iCx2 = $iCx + $iCw;
$iCy2 = $iCy + $iCh;

# set min size
$sMinSizeCalc = '';
$sMinSize = '';
if (is_array($aCropSettings[$iCropCurrent]->getMinSize())) {
    list($iMinSizeW, $iMinSizeH) = $aCropSettings[$iCropCurrent]->getMinSize();
    $sMinSizeCalc = 'var iMinSizeW = Math.round(' . $iMinSizeW . ' * resizeRatio);' . "\n";
    $sMinSizeCalc .= 'var iMinSizeH = Math.round(' . $iMinSizeH . ' * resizeRatio);';
    $sMinSize = 'minSize: [iMinSizeW,iMinSizeH],';
}

# set max size
$sMaxSizeCalc = '';
$sMaxSize = '';
if (is_array($aCropSettings[$iCropCurrent]->getMaxSize())) {
    list($iMaxSizeW, $iMaxSizeH) = $aCropSettings[$iCropCurrent]->getMaxSize();
    $sMaxSizeCalc = 'var iMaxSizeW = Math.round(' . $iMaxSizeW . ' * resizeRatio);' . "\n";
    $sMaxSizeCalc .= 'var iMaxSizeH = Math.round(' . $iMaxSizeH . ' * resizeRatio);';
    $sMaxSize = 'maxSize: [iMaxSizeW,iMaxSizeH],';
}

# set max preview width
if (is_numeric($aCropSettings[$iCropCurrent]->iMaxPreviewWidth)) {
    $iMaxPreviewW = $aCropSettings[$iCropCurrent]->iMaxPreviewWidth;
} else {
    $iMaxPreviewW = 'null';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>