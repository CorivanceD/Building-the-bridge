<?

# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('pages_manager');
$oPageLayout->sModuleName = sysTranslations::get('pages_manager');

# max levels for page structure depth
$iMaxLevels = Settings::get('pagesMaxLevels');

# reset crop settings
unset($_SESSION['aCropSettings']);

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    # set crop referrer for pages module
    $_SESSION['cropReferrer'] = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . http_get("param2");

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oPage = PageManager::getPageById(http_get("param2"));
        if (!$oPage)
            http_redirect(ADMIN_FOLDER . "/");
        # is editable?
        if (!$oPage->isEditable()) {
            $_SESSION['statusUpdate'] = sysTranslations::get('pages_not_editable_2'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
    } else {
        $oPage = new Page ();
        if (http_get("parentPageId")) {
            $oParentPage = PageManager::getPageById(http_get("parentPageId"));
            if (!$oParentPage || !$oParentPage->mayHaveSub() || $oParentPage->level >= $iMaxLevels) {
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
            }
            $oPage->copyFromParent($oParentPage);
        }
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oPage->_load($_POST);

        # set content after load, _load strips tags for all inputs
        $oPage->content = http_post('content');

        if ($oCurrentUser->isSEO()) {
            $oPage->setUrlPart(http_post('urlPart'));
            $oPage->setInMenu(http_post('inMenu'));
            $oPage->setUrlParameters(http_post('urlParameters'));
        }

        if ($oCurrentUser->isAdmin()) {
            $oPage->setOnlineChangeable(http_post('onlineChangeable'));
            $oPage->setControllerPath(http_post('controllerPath'));
            $oPage->setEditable(http_post('editable'));
            $oPage->setDeletable(http_post('deletable'));
            $oPage->setMayHaveSub(http_post('mayHaveSub'));
            $oPage->setLockUrlPath(http_post('lockUrlPath'));
            $oPage->setLockParent(http_post('lockParent'));
            $oPage->setHideImageManagement(http_post('hideImageManagement'));
            $oPage->setHideFileManagement(http_post('hideFileManagement'));
            $oPage->setHideLinkManagement(http_post('hideLinkManagement'));
            $oPage->setHideYoutubeLinkManagement(http_post('hideYoutubeLinkManagement'));
            $oPage->setHideRssManagement(http_post('hideRssManagement'));
        }

        # if object is valid, save
        if ($oPage->isValid()) {
            PageManager::savePage($oPage); //save page
            
            if (http_post('rssFeeds', false)) {
                $aFeeds = explode('^', http_post('rssFeeds'));
                $aRssFeeds = array();
                
                foreach ($aFeeds AS $sRssFeed) {
                    
                    $oRssFeed = new RssFeed();                                        
                    
                    if (strpos($sRssFeed, ":") === FALSE) { 
                        $oRssFeed->rssFeedId = intval($sRssFeed);
                        $oRssFeed->filter = null;
                    }
                    else {   
                        $aFeedData = explode(':', $sRssFeed);
                        $oRssFeed->rssFeedId = intval($aFeedData[0]);
                        $oRssFeed->filter = $aFeedData[1];
                        $oRssFeed->logicOperator = $aFeedData[2];
                        $oRssFeed->negative = $aFeedData[3] === 'YES' ? 1 : 0;                         
                    }    

                    $aRssFeeds[] = $oRssFeed;
                }
                
                $oPage->setRssFeeds($aRssFeeds);
            }
            else {
                $oPage->unsetRssFeeds();
            }
            
            $_SESSION['statusUpdate'] = sysTranslations::get('pages_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        } else {
            Debug::logError("", "Pages module php validate error", __FILE__, __LINE__, "Tried to save Page with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('pages_not_saved');
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
        $oUpload = new Upload($_FILES['image'], Page::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'png', 'gif', 'jpeg'), $bCheckMime);

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
            if (ImageManager::resizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Page::IMAGES_PATH . '/detail/' . $oImageFile->name, 960, 700, $sErrorMsg)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Page::IMAGES_PATH . '/detail/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'detail';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_image_detail') . $sErrorMsg; //error cropping image
            }

            // make auto crop if not doing manually
            if (http_post("cropAfterUpload") != 1) {
                # make crop small
                if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Page::IMAGES_PATH . '/crop_small/' . $oImageFile->name, 200, 150, $sErrorMsg, true)) {
                    $oImageFileThumb = clone $oImageFile;
                    $oImageFileThumb->link = Page::IMAGES_PATH . '/crop_small/' . $oImageFile->name;
                    $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                    $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                    $oImageFileThumb->reference = 'crop_small';

                    $aImageFiles[] = clone $oImageFileThumb;
                    $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded'); //upload OK
                } else {
                    $_SESSION['statusUpdate'] = sysTranslations::get('global_no_crop_small') . $sErrorMsg; //error cropping image
                }
            }

            # make cms thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Page::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 75, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Page::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
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
            PageManager::savePageImageRelation($oPage->pageId, $oImage->imageId);

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
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
            }
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        }
    }
    # action saveFile
    if (http_post("action") == 'saveFile') {

        $bCheckMime = true;

        # for upload from SWFUpload
        if (http_post("SWFUpload")) {
            $oResObj = new stdClass();
            $oResObj->success = false;
            session_id(http_post("PHPSESSID"));
            $bCheckMime = false;
        }


        # upload file or return error
        $oUpload = new Upload($_FILES['file'], Page::FILES_PATH . '/', (http_post('file_title') != '' ? http_post('file_title') : null), null, $bCheckMime);

        # save file to database on success
        if ($oUpload->bSuccess === true) {

            # make File object and save
            $oFile = new File();
            $oFile->name = $oUpload->sNewFileBaseName;
            $oFile->mimeType = $oUpload->sMimeType;
            $oFile->size = $oUpload->iSize;
            $oFile->link = $oUpload->sNewFilePath;
            $oFile->title = http_post('title') == '' ? $oUpload->sFileName : http_post('title');

            # save file
            FileManager::saveFile($oFile);

            # save page file relation
            PageManager::savePageFileRelation($oPage->pageId, $oFile->mediaId);

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->success = true;
                // add some extra values
                $oFile->isOnlineChangeable = $oFile->isOnlineChangeable();
                $oFile->isEditable = $oFile->isEditable();
                $oFile->isDeletable = $oFile->isDeletable();
                $oFile->extension = $oFile->getExtension();

                $oResObj->file = $oFile; // for adding file to list
                print json_encode($oResObj);
                die;
            }

            # back to edit
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_file_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        }
    }

    # action = saveLink
    if (http_post("action") == 'saveLink') {

        # load data in object
        $oLink = new Link();
        $oLink->_load($_POST);

        # if object is valid, save
        if ($oLink->isValid()) {
            LinkManager::saveLink($oLink); //save link
            PageManager::savePageLinkRelation($oPage->pageId, $oLink->mediaId);
            $_SESSION['statusUpdate'] = sysTranslations::get('global_link_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        } else {
            Debug::logError("", "Page module php validate error", __FILE__, __LINE__, "Tried to save Link with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('global_link_not_saved');
        }
    }

    # action = saveYoutubeLink
    if (http_post("action") == 'saveYoutubeLink') {

        # load data in object
        $oYoutubeLink = new YoutubeLink();
        $oYoutubeLink->_load($_POST);

        # if object is valid, save
        if ($oYoutubeLink->isValid()) {
            YoutubeLinkManager::saveYoutubeLink($oYoutubeLink); //save youtube link
            PageManager::savePageYoutubeLinkRelation($oPage->pageId, $oYoutubeLink->mediaId);
            $_SESSION['statusUpdate'] = sysTranslations::get('global_youtube_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId);
        } else {
            Debug::logError("", "Page module php validate error", __FILE__, __LINE__, "Tried to save Link with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('global_youtube_not_saved');
        }
    }

    # set settings for image management
    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->aImages = $oPage->getImages('all');
    $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb', 'crop_small');
    $oImageManagerHTML->bCropAfterUploadChecked = true;
    $oImageManagerHTML->bShowCropAfterUploadOption = true;
    $oImageManagerHTML->iContainerIDAddition = '2';
    $oImageManagerHTML->sExtraUploadLine = sysTranslations::get('global_image_size_960_700');
    $oImageManagerHTML->sExtraUploadedLine = sysTranslations::get('global_images_displayed'). ' <a href="' . $oPage->getUrlPath() . '" target="_blank">' . $oPage->getShortTitle() . '</a>';

    # set settings for file management
    $oFileManagerHTML = new FileManagerHTML();
    $oFileManagerHTML->aFiles = $oPage->getFiles('all');
    $oFileManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId;

    # set link manager
    $oLinkManagerHTML = new LinkManagerHTML();
    $oLinkManagerHTML->aLinks = $oPage->getLinks('all');
    $oLinkManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId;

    # set youtube manager
    $oYoutubeLinkManagerHTML = new YoutubeLinkManagerHTML();
    $oYoutubeLinkManagerHTML->aYoutubeLinks = $oPage->getYoutubeLinks('all');
    $oYoutubeLinkManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPage->pageId;

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/pages/page_form.inc.php';
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

    $oCropSettings->setAspectRatio(200, 150);

    $oCropSettings->bShowPreview = true;
    $oCropSettings->iMaxPreviewWidth = 200;

    $oCropSettings->sReferrer = http_session('cropReferrer');
    $oCropSettings->sReferrerTekst = 'pagina bewerken';

    # create crop and show on crop form page
    $oCropSettings->addCrop(200, 150, Page::IMAGES_PATH . '/crop_small/' . $oImage->getImageFileByReference('original')->name, 'crop_small', true, true);

    # create crop but do not show on crop form page
    $oCropSettings->addCrop(100, 75, $oImage->getImageFileByReference('cms_thumb')->link, 'cms_thumb', false, true);

    # add setting to session in an array
    $_SESSION['aCropSettings'][] = clone $oCropSettings;

    http_redirect(ADMIN_FOLDER . '/crop');
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {

    if (is_numeric(http_get("param2"))) {
        $oPage = PageManager::getPageById(http_get("param2"));
    }

    if ($oPage && PageManager::deletePage($oPage)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('pages_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('pages_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} elseif (http_get("param1") == 'structuur-wijzigen') {

    if (http_post("action") == 'savePageStructure') {

        #
        if (http_post("pageStructure")) {
            parse_str(http_post("pageStructure"), $aPageArray);
        }

        $iPageOrder = 0;
        # loop trough all pages and save new parentId
        foreach ($aPageArray['page'] AS $iPageId => $mParentPageReference) {

            # get page and save
            $oPage = PageManager::getPageById($iPageId);

            if (!$oPage)
                continue;
            if ($mParentPageReference == 'root') {
                $oPage->parentPageId = null;
            } elseif (is_numeric($mParentPageReference)) {
                $oPage->parentPageId = $mParentPageReference;
            } else {
                continue;
            }

            $oPage->order = $iPageOrder; // set the order to the page
            # save page
            if ($oPage->isValid()) {
                PageManager::savePage($oPage);
            }
            $iPageOrder++;
        }
        $_SESSION['statusUpdate'] = 'Paginastructuur is opgeslagen'; //save status update into session
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    $aAllLevel1Pages = PageManager::getPagesByFilter(array('showAll' => true, 'level' => 1));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/pages/pages_change_structure.inc.php';
} elseif (http_get("param1") == 'ajax-setOnline') {

    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iPageId = http_get("param2");
    $oPage = PageManager::getPageById($iPageId);
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for page
    if ($oPage && $oPage->isOnlineChangeable()) {
        $oResObj->success = PageManager::updateOnlineByPage($bOnline, $oPage);
        $oResObj->pageId = $iPageId;
        $oResObj->online = $bOnline;
    }

    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    print json_encode($oResObj);
    die;
} elseif (http_get('param1') == 'image-list') {
    $oPage = PageManager::getPageById(http_get('param2'));

    if (!$oPage)
        die;

    $aImages = $oPage->getImages('all');
    $aImageFiles = array();
    $aLinks = array();
    foreach ($aImages AS $oImage) {
        $oImageFile = $oImage->getImageFileByReference('detail');
        if (!$oImageFile)
            continue;
        $oLink = new stdClass();
        $oLink->title = $oImageFile->title ? $oImageFile->title : $oImageFile->name;
        $oLink->value = $oImageFile->link;
        $aLinks[] = $oLink;
    }

    die(json_encode($aLinks));
} elseif (http_get('param1') == 'link-list') {
    $aPages = PageManager::getPagesByFilter(array('showAll' => 1, 'level' => 1, 'online' => 1)); // all online level 1 pages

    $aLinks = array();

    function makeListTree($aPages, &$aLinks) {
        foreach ($aPages AS $oPage) {
            $sLeadingChars = '';
            for ($iC = $oPage->level; $iC > 1; $iC--) {
                $sLeadingChars .= '--';
            }
            $oLink = new stdClass();
            $oLink->title = $sLeadingChars . $oPage->getShortTitle();
            $oLink->value = $oPage->getUrlPath();
            $aLinks[] = $oLink;
            makeListTree($oPage->getSubPages('online-all'), $aLinks); //call function recursive
        }
    }

    makeListTree($aPages, $aLinks);

    die(json_encode($aLinks));
} else {

    #display page overview
    $aAllLevel1Pages = PageManager::getPagesByFilter(array('showAll' => 1, 'level' => 1));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/pages/pages_overview.inc.php';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>