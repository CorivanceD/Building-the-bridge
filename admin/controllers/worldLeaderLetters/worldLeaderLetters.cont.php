<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# reset crop settings
unset($_SESSION['aCropSettings']);

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_world_leader_letters');
$oPageLayout->sModuleName = sysTranslations::get('global_world_leader_letters');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['lettersPerPage'] = http_post('perPage');
}

// handle filter
$aArticleFilter = http_session('letterFilter');
if (http_post('filterArticles')) {
    $aArticleFilter = http_post('articleFilter');
    //$aArticleFilter['status'] = Article::getAllStatus();
    $_SESSION['letterFilter'] = $aArticleFilter;
}

if (http_post('resetFilter') || empty($aArticleFilter)) {
    unset($_SESSION['letterFilter']);
    $aArticleFilter = array();
    $aArticleFilter['q'] = '';
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {
    if (http_post('action', null) == 'ajax-getLeaders') {
        $oResObj = new stdClass();
        $oResObj->success = true;

        if (http_post('countryId', false)) {
            $oResObj->leaders = WorldLeaderManager::getWorldLeadersByFilter(array('countryId' => http_post('countryId')));
        } else {
            $oResObj->leaders = array();
        }

        die(json_encode($oResObj));
    }
    
    if (http_post('action', null) == 'ajax-getAddresses') {
        $oResObj = new stdClass();
        $oResObj->success = true;

        if (http_post('worldLeaderId', false)) {
            $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId'));
            
            if (empty($oWorldLeader)) {
                $oResObj->addresses = array();
            }
            else {
                $aAddresses = $oWorldLeader->getAddresses();
                $oResObj->addresses = empty($aAddresses) ? array() : $aAddresses;
            }
        } else {
            $oResObj->addresses = array();
        }

        die(json_encode($oResObj));
    }    

    # set crop referrer for pages module
    $_SESSION['cropReferrer'] = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . http_get("param2");


    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oArticle = ArticleManager::getArticleById(http_get("param2"));
        if (!$oArticle)
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        # is editable?
        if (!$oArticle->isEditable()) {
            $_SESSION['statusUpdate'] = sysTranslations::get('letter_not_edited'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
        
        $oWorldLeaderLetter = $oArticle->getWorldLeaderLetter();
        
        if (empty($oWorldLeaderLetter)) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
    } else {
        $oArticle = new Article();
        $oWorldLeaderLetter = new WorldLeaderLetter();
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oArticle->_load($_POST);
        $oWorldLeaderLetter = new WorldLeaderLetter();
        # set some properties after load, _load strips tags for all inputs
        $oArticle->intro = http_post('intro');
        $oArticle->content = http_post('content');
        $oArticle->authorId = empty($oArticle->authorId) ? $oCurrentUser->userId : $oArticle->authorId;
        $oArticle->articleCategoryId = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
        $oArticle->parentPageId = Page::WORLD_LEADER_PAGE;

        # if object is valid, save
        if ($oArticle->isValid()) {
            ArticleManager::saveArticle($oArticle); //save letters item

            $oWorldLeaderLetter->worldLeaderLetterId = http_post('worldLeaderLetterId');
            $oWorldLeaderLetter->articleId = $oArticle->articleId;                                
            $oWorldLeaderLetter->worldLeaderId = http_post('worldLeaderId');
            $oWorldLeaderLetter->worldLeaderAddressId = http_post('worldLeaderAddressId');
            if ($oWorldLeaderLetter->isValid()) {
                WorldLeaderLetterManager::saveWorldLeaderLetter($oWorldLeaderLetter);
                $_SESSION['statusUpdate'] = sysTranslations::get('letter_saved'); //save status update into session
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
                
            } else {
                Debug::logError("", "Letter module php validate error", __FILE__, __LINE__, "Tried to save a letter with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
                $oPageLayout->sStatusUpdate = sysTranslations::get('letter_not_saved');
            }
        } else {
            Debug::logError("", "Letter module php validate error", __FILE__, __LINE__, "Tried to save a letter with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('letter_not_saved');
        }
    } elseif (http_post("action") == 'sendToReview') {
        ArticleManager::updateStatusByArticleId(Article::ARTICLE_STATUS_REVIEW, $oArticle->articleId);
        $oEditor = $oCurrentUser->getEditor();
        MailManager::sendMail($oEditor->email, 'New letter has been sent for reviewing', $oCurrentUser->name . ' has sent the letter "' . $oArticle->title . '" for reviewing', $oCurrentUser->email);
        $_SESSION['statusUpdate'] = 'Letter status changed';
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    # action saveImage
    if (http_post("action") == 'saveImage') {

        $bCheckMime = true;

        # upload file or return error
        $oUpload = new Upload($_FILES['image'], Article::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'jpeg', 'png', 'gif'), $bCheckMime);

        # save image to database on success
        if ($oUpload->bSuccess === true) {

            // resize original to acceptable size (1500*1500 max)
            if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg)) {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_not_resize') . $sErrorMsg; //error resizing image
            }

            # make Image object and save
            $oImage = new Image();
            $oImageFile = new ImageFile();
            $oImageFile->link = $oUpload->sNewFilePath;
            $oImageFile->title = http_post('title', '');
            $oImageFile->name = $oUpload->sNewFileBaseName;
            $oImageFile->mimeType = $oUpload->sMimeType;
            $oImageFile->size = $oUpload->iSize;
            $oImageFile->reference = 'original';
            $aImageFiles[] = $oImageFile;

            # always make detail image (resized of original)
            if (ImageManager::resizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Article::IMAGES_PATH . '/detail/' . $oImageFile->name, 960, 700, $sErrorMsg)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Article::IMAGES_PATH . '/detail/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'detail';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_image_uploaded');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_image_detail') . $sErrorMsg; //error cropping image
            }

            # make crop_small
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Article::IMAGES_PATH . '/crop_small/' . $oImageFile->name, 200, 150, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Article::IMAGES_PATH . '/crop_small/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'crop_small';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_automatic_cutout');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_automatic_cutout') . $sErrorMsg; //error cropping image
            }

            # make cms_thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Article::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 75, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Article::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'cms_thumb';

                $aImageFiles[] = clone $oImageFileThumb;
                $_SESSION['statusUpdate'] = sysTranslations::get('global_automatic_cutout');
            } else {
                $_SESSION['statusUpdate'] = sysTranslations::get('global_no_automatic_cutout') . $sErrorMsg; //error cropping image
            }

            $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
            # save image
            ImageManager::saveImage($oImage);

            # save letters item image relation
            ArticleManager::saveArticleImageRelation($oArticle->articleId, $oImage->imageId);

            # direct crop
            if (http_post("cropAfterUpload") == 1) {
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image/?imageId=' . $oImage->imageId);
            } else {
                # back to edit
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
            }
        } else {
            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
        }
    }

    # action saveFile
    if (http_post("action") == 'saveFile') {

        $bCheckMime = true;

        # upload file or return error
        $oUpload = new Upload($_FILES['file'], Article::FILES_PATH . '/', (http_post('file_title') != '' ? http_post('file_title') : null), null, $bCheckMime);

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
            ArticleManager::saveArticleFileRelation($oArticle->articleId, $oFile->mediaId);

            # back to edit
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
        } else {

            $_SESSION['statusUpdate'] = sysTranslations::get('global_file_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
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
            ArticleManager::saveArticleLinkRelation($oArticle->articleId, $oLink->mediaId);
            $_SESSION['statusUpdate'] = sysTranslations::get('global_link_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
        } else {
            Debug::logError("", "Letter module php validate error", _FILE_, _LINE_, "Tried to save Link with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
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
            ArticleManager::saveArticleYoutubeLinkRelation($oArticle->articleId, $oYoutubeLink->mediaId);
            $_SESSION['statusUpdate'] = sysTranslations::get('global_youtube_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId);
        } else {
            Debug::logError("", "Letter module php validate error", _FILE_, _LINE_, "Tried to save Link with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('global_youtube_not_saved');
        }
    }


    # set settings for image management
    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->aImages = $oArticle->getImages('all');
    $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb', 'detail', 'crop_small');
    $oImageManagerHTML->bCropAfterUploadChecked = true;
    $oImageManagerHTML->bShowCropAfterUploadOption = true;

    # set settings for file management
    $oFileManagerHTML = new FileManagerHTML();
    $oFileManagerHTML->aFiles = $oArticle->getFiles('all');
    $oFileManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId;

    # set link manager
    $oLinkManagerHTML = new LinkManagerHTML();
    $oLinkManagerHTML->aLinks = $oArticle->getLinks('all');
    $oLinkManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId;

    # set youtube manager
    $oYoutubeLinkManagerHTML = new YoutubeLinkManagerHTML();
    $oYoutubeLinkManagerHTML->aYoutubeLinks = $oArticle->getYoutubeLinks('all');
    $oYoutubeLinkManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticle->articleId;

    #load the needed data for the from
    $aCountries = CountryManager::getCountries();
    $aWorldLeaders = array();
    $aAddresses = array();
    $iCountryId = '';
    $iWorldLeaderId = '';
    $iWorldLeaderAddressId = '';
    
    if (http_post('countryId')) {
       $aWorldLeaders = WorldLeaderManager::getWorldLeadersByFilter(array('countryId' => http_post('countryId'))); 
       $iCountryId = http_post('countryId');
    }
    else {
       $oLetter = $oArticle->getWorldLeaderLetter();
       
       if (!empty($oLetter)) {
           if ($oLetter->getWorldLeader() != null) {
               $iCountryIdForFilter = empty($oLetter->getWorldLeaderAddress()->countryId) ? -1 : $oLetter->getWorldLeaderAddress()->countryId;
               $aWorldLeaders = WorldLeaderManager::getWorldLeadersByFilter(array('countryId' => $iCountryIdForFilter));
               $iCountryId = $iCountryIdForFilter;
           }
       }
    }
    
    if (http_post('worldLeaderId')) {        
        $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId', http_post('worldLeaderId')));
        $iWorldLeaderId = http_post('worldLeaderId');
        $aAddresses = $oWorldLeader->getAddresses();
    }
    elseif (isset($oWorldLeaderLetter) && !empty($oWorldLeaderLetter->worldLeaderId)) {
        $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId', $oWorldLeaderLetter->worldLeaderId));
        $iWorldLeaderId = $oWorldLeaderLetter->worldLeaderId;
        $aAddresses = $oWorldLeader->getAddresses();        
    }
    
    if (http_post('worldLeaderAddressId')) {
        $iWorldLeaderAddressId = http_post('worldLeaderAddressId');
    }
    elseif(isset($oWorldLeaderLetter) && !empty ($oWorldLeaderLetter->worldLeaderAddressId)) {
        $iWorldLeaderAddressId = $oWorldLeaderLetter->worldLeaderAddressId;
    }
    
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/worldLeaderLetters/letter_form.inc.php';
} elseif (http_get("param1") == 'crop-image' && is_numeric(http_get("imageId"))) {

    $oImage = ImageManager::getImageById(http_get("imageId"));

    if (!$oImage || !$oImage->getImageFileByReference("original")) {
        $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_avaliable'); //error getting image
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
    $oCropSettings->sReferrerTekst = 'letter edition';

    # create cropand show on crop form page
    $oCropSettings->addCrop(200, 150, Article::IMAGES_PATH . '/crop_small/' . $oImage->getImageFileByReference('original')->name, 'crop_small', true, true);

    $oCropSettings->addCrop(960, 700, Article::IMAGES_PATH . '/detail/' . $oImage->getImageFileByReference('original')->name, 'detail', true, true);

    # create crop but do not show on crop form page
    $oCropSettings->addCrop(100, 75, $oImage->getImageFileByReference('cms_thumb')->link, 'cms_thumb', false, true);

    # add setting to session in an array
    $_SESSION['aCropSettings'][] = clone $oCropSettings;

    http_redirect(ADMIN_FOLDER . '/crop');
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {

    if (is_numeric(http_get("param2"))) {
        $oArticle = ArticleManager::getArticleById(http_get("param2"));
    }

    if (!empty($oArticle) && ArticleManager::deleteArticle($oArticle)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} 
elseif (http_get("param1") == 'ajax-setOnline') {

    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iArticleId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for page
    if (is_numeric($iArticleId)) {
        $oResObj->success = ArticleManager::updateOnlineByArticleId($bOnline, $iArticleId);
        $oResObj->articleId = $iArticleId;
        $oResObj->online = $bOnline;
    }

    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    print json_encode($oResObj);
    die;
} elseif (http_get('param1') == 'image-list') {
    $oArticle = ArticleManager::getArticleById(http_get('param2'));

    if (!$oArticle)
        die;

    $aImages = $oArticle->getImages('all');
    $aImageFiles = array();
    $aLinks = array();
    foreach ($aImages AS $oImage) {
        $oImageFile = $oImage->getImageFileByReference('detail');
        if (!$oImageFile)
            continue;
        $oLink = new stdClass();
        $oLink->title = $oImageFile->title ? $oImageFile->title : $oImageFile->name;
        $oLink->value = CLIENT_HTTPS_URL . $oImageFile->link;
        $aLinks[] = $oLink;
    }

    die(json_encode($aLinks));
} else {
    $iPerPage = http_session('lettersPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    #display articles overview
    $aArticleFilter['authorId'] = $oCurrentUser->userId;
    $aArticleFilter['onlyLetters'] = 1;    
    $aArticles = ArticleManager::getArticlesByFilter($aArticleFilter, $iPerPage, $iStart, $iFoundRows);

    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/worldLeaderLetters/letters_overview.inc.php';
}

# inlcude template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>