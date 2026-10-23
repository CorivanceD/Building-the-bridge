<?php
require_once DOCUMENT_ROOT . '/libs/mpdf571/mpdf.php';
# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_petitions');
$oPageLayout->sModuleName = sysTranslations::get('global_petitions');
$oPetition = new Petition();

# reset crop settings
unset($_SESSION['aCropSettings']);

// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['petitionsPerPage'] = http_post('perPage');
}

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once

# set object online/offline
if (http_get("param1") == 'ajax-setOnline') {
    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iPetitionId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for object
    if (is_numeric($iPetitionId)) {
        $oResObj->success = PetitionManager::setActivePetition($iPetitionId, $bOnline);
        $oResObj->petitionId = $iPetitionId;
        $oResObj->online = $bOnline;
    }

    # redirect to overview page if this isn't AJAX
    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '');
    }

    die(json_encode($oResObj));
}


# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    # set crop referrer for petitions module
    $_SESSION['cropReferrer'] = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . http_get("param2");

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oPetition = PetitionManager::getPetitionById(intval(http_get("param2")));
        if (empty($oPetition))
            http_redirect(ADMIN_FOLDER . "/");
        # is editable?
        if (!$oPetition->isEditable()) {
            $_SESSION['statusUpdate'] = sysTranslations::get('petitions_not_editable_2'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oPetition->_load($_POST);

        # set content after load, _load strips tags for all inputs
        $oPetition->intro = http_post('intro');
        $oPetition->description = http_post('description');


        # if object is valid, save
        if ($oPetition->isValid()) {
            PetitionManager::savePetition($oPetition); //save page
            
//            if ($oPetition->online == 1) {
//                PetitionManager::setActivePetition($oPetition->petitionId);
//            }


            $_SESSION['statusUpdate'] = sysTranslations::get('petitions_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPetition->petitionId);
        } else {
            Debug::logError("", "Petitions module php validate error", __FILE__, __LINE__, "Tried to save a petition with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('petitions_not_saved');
        }
    }

    # action saveImage
    if (http_post("action") == 'saveImage') {
        $bCheckMime = true;

        # for upload from SWFUpload
        if (http_post("SWFUpload")) {
            $oResObj = new stdClass();
            $oResObj->success = false;
            session_id(http_post("PHPSESSID"));
            $bCheckMime = false;
        }

        # upload file or return error
        $oUpload = new Upload($_FILES['image'], Petition::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'png', 'gif', 'jpeg'), $bCheckMime);

        # save image to database on success
        if ($oUpload->bSuccess === true) {

            // resize original to acceptable size
            if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg)) {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_resized') . $sErrorMsg; //error resizing image
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

            # always make detail image (resized of original)
            if (ImageManager::resizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Petition::IMAGES_PATH . '/detail/' . $oImageFile->name, 960, 700, $sErrorMsg)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Petition::IMAGES_PATH . '/detail/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'detail';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_image_detail') . $sErrorMsg; //error cropping image
            }

            # make cms thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Petition::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 75, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Petition::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'cms_thumb';
                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded'); //upload OK
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_cms_thumb') . $sErrorMsg; //error cropping image
            }

            $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
            # save image
            ImageManager::saveImage($oImage);

            # save page image relation
            PetitionManager::savePetitionImageRelation($oPetition->petitionId, $oImage->imageId);

            # direct crop
            if (http_post("cropAfterUpload") == 1) {
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image/?imageId=' . $oImage->imageId);
            } else {

                # for SWFUpload
                if (http_post("SWFUpload")) {
                    $oResObj->success = true;
                    $oImageFileThumb->imageId = $oImage->imageId; // set for use in SWFUpload
                    // add some extra values
                    $oImageFileThumb->isOnlineChangeable = $oImage->isOnlineChangeable();
                    $oImageFileThumb->isCropable = $oImage->isCropable();
                    $oImageFileThumb->isEditable = $oImage->isEditable();
                    $oImageFileThumb->isDeletable = $oImage->isDeletable();
                    $oImageFileThumb->hasNeededImageFiles = $oImage->hasImageFiles(array('cms_thumb', 'detail', 'crop_small'));

                    $oResObj->imageFile = $oImageFileThumb; // for adding image to list (last imageFile object)
                    print json_encode($oResObj);
                    die;
                }

                # back to edit
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPetition->petitionId);
            }
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPetition->petitionId);
        }
    }

    if (!empty($oPetition->petitionId)) {

        # set settings for image management
        $oImageManagerHTML = new ImageManagerHTML();
        $oImageManagerHTML->iMaxImages = 1;
        $oImageManagerHTML->aImages = ($oPetition->getImage() === null) ? array() : array($oPetition->getImage());
        $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
        $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPetition->petitionId;
        $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb', 'detail');
        $oImageManagerHTML->bCropAfterUploadChecked = true;
        $oImageManagerHTML->bShowCropAfterUploadOption = true;
        $oImageManagerHTML->iContainerIDAddition = '29';
        $oImageManagerHTML->sExtraUploadLine = sysTranslations::get('global_image_size_960_700');
    }


    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/petitions/petition_form.inc.php';
} elseif (http_get("param1") == 'crop-image' && is_numeric(http_get("imageId"))) {

    $oImage = ImageManager::getImageById(http_get("imageId"));

    if (!$oImage || !$oImage->getImageFileByReference("original")) {
        $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_available'); //error getting image
        http_redirect(http_session('cropReferrer'));
    }

    # set crop settings
    $oCropSettings = new CropSettings();
    $oCropSettings->iImageId = $oImage->imageId;
    $oCropSettings->sReferenceName = 'original';

    $oCropSettings->setAspectRatio(960, 700);

    $oCropSettings->bShowPreview = true;
    $oCropSettings->iMaxPreviewWidth = 700;

    $oCropSettings->sReferrer = http_session('cropReferrer');
    $oCropSettings->sReferrerTekst = 'petition edition';

    # create crop and show on crop form page
    $oCropSettings->addCrop(960, 700, Petition::IMAGES_PATH . '/detail/' . $oImage->getImageFileByReference('original')->name, 'detail', true, true);

    # create crop but do not show on crop form page
    $oCropSettings->addCrop(100, 75, $oImage->getImageFileByReference('cms_thumb')->link, 'cms_thumb', false, true);

    # add setting to session in an array
    $_SESSION['aCropSettings'][] = clone $oCropSettings;

    http_redirect(ADMIN_FOLDER . '/crop');
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {

    if (is_numeric(http_get("param2"))) {
        $oPetition = PetitionManager::getPetitionById(http_get("param2"));
    }

    if ($oPetition && PetitionManager::deletePetition($oPetition)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('petitions_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('petitions_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} elseif (http_get("param1") == 'export') {

    $iPetitionId = intval(http_get("param2", 0)); //no value, set offline by default
    $oPetition = PetitionManager::getPetitionById($iPetitionId); 
    $aRequiredSignatures = $oPetition->getSignatures();
    @$oSignaturePdf = new mPDF('', 'A4', 0, '', 21, 9, 60, 40, 14, 12);
    @$oSignaturePdf->SetTitle('Petition Signatures: ' . $oPetition->title);
    @$oSignaturePdf->use_kwt = true;
    @$oSignaturePdf->shrink_tables_to_fit = 1;
    @$oSignaturePdf->setAutoTopMargin = 'stretch';
    $sStylesheet = file_get_contents(DOCUMENT_ROOT. ADMIN_CSS_FOLDER . '/petition_pdf.css');
    @$oSignaturePdf->WriteHTML($sStylesheet, 1);

    /* $sOrderHeader = file_get_contents(TEMPLATES_FOLDER . '/elements/order_pdf_header.tmpl.html'); */
    ob_start();
    include_once ADMIN_TEMPLATES_FOLDER . '/elements/petitions/petition_pdf_header.tmpl.php';
    $sHtml = ob_get_contents();
    ob_end_clean();
    @$oSignaturePdf->SetHTMLHeader($sHtml);

    ob_start();
    include_once ADMIN_TEMPLATES_FOLDER . '/elements/petitions/petition_pdf_body.tmpl.php';
    $sHtml = ob_get_contents();
    ob_end_clean();
    @$oSignaturePdf->WriteHTML($sHtml);
    $sPdfFooter = file_get_contents(ADMIN_TEMPLATES_FOLDER . '/elements/petitions/petition_pdf_footer.tmpl.html');
    @$oSignaturePdf->SetHTMLFooter($sPdfFooter);    
    @$oSignaturePdf->Output('petition-'.$oPetition->petitionId.'.pdf', 'D');
    die;
} else {
    $iPerPage = http_session('petitionsPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);

    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }


    $aPetitions = PetitionManager::getPetitions(array('showAll' => 1), $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    #display petition overview

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/petitions/petitions_overview.inc.php';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>