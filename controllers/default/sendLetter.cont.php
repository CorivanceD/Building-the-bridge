<?php

/*
 * Controller to handle contact pages
 */
$oPageLayout = new PageLayout();
$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());
if (empty($oPage) || !$oPage->online) {
    showHttpError('404');
}

if ($oPage->level > 1)
    $oPageForMenu = PageManager::getPageByUrlPath('/' . http_get('controller'));
else
    $oPageForMenu = $oPage;

$oPageLayout->sWindowTitle = $oPage->getWindowTitle();
$oPageLayout->sMetaDescription = $oPage->getMetaDescription();
$oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());

$aImages = $oPage->getImages();
$aYoutubeVideos = $oPage->getYoutubeLinks();
$aFiles = $oPage->getFiles();
$aLinks = $oPage->getLinks();
$bAjax = http_post('ajax', 0);

if ($bAjax) {
    $oResObj = new stdClass();
    $oResObj->success = true;

    if (http_post('action', 'filterAddressees') && is_numeric(http_post('regionId'))) {
        $oRegion = CountryManager::getRegionById(http_post('regionId'));

        if (!empty($oRegion)) {
            $aCountries = $oRegion->getCountries();
        }

        $_SESSION['oRegion'] = $oRegion;
        $oResObj->countries = empty($aCountries) ? array() : $aCountries;
    }

    if (http_post('action', 'filterAddressees') && is_numeric(http_post('countryId'))) {


        if (intval(http_post('countryId')) === Country::UN_NY_ID) {
            $aFilter['regionId'] = Region::REGION_ID_UN_NY;
            $_SESSION['oRegion'] = CountryManager::getRegionById(Region::REGION_ID_UN_NY);
            unset($_SESSION['oCountry']);
        } elseif (intval(http_post('countryId')) === Country::UN_GE_ID) {
            $aFilter['regionId'] = Region::REGION_ID_UN_GE;
            $_SESSION['oRegion'] = CountryManager::getRegionById(Region::REGION_ID_UN_GE);
            unset($_SESSION['oCountry']);
        } else {
            $oRegion = http_session('oRegion', false);
            $aFilter = array('countryId' => http_post('countryId'));
            $_SESSION['oCountry'] = CountryManager::getCountry(http_post('countryId'));
            if (!empty($oRegion)) {
                $aFilter['regionId'] = $oRegion->regionId;
            }
        }


        $aWorldLeaders = WorldLeaderManager::getWorldLeadersByFilter($aFilter, null, 0, $iFoundRows, array('`wla`.`order`' => 'ASC'));
        $aAddressees = array();

        foreach ($aWorldLeaders as $oWorldLeader) {
            $oAddressee = new stdClass();
            $oAddressee->worldLeaderId = $oWorldLeader->worldLeaderId;
            $oAddressee->name = $oWorldLeader->getCompleteName();
            $aAddressees[] = $oAddressee;
        }
        $oResObj->addressees = empty($aAddressees) ? new stdClass() : $aAddressees;
    }

    if (http_post('action', 'getAddresses') && is_numeric(http_post('worldLeaderId'))) {
        $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId'));
        $oRegion = http_session('oRegion', false);
        $oCountry = http_session('oCountry', false);
        $iRegionId = null;
        $iCountryId = null;

        if (!empty($oRegion)) {
            $iRegionId = $oRegion->regionId;
        }

        if (!empty($oCountry)) {
            $iCountryId = $oCountry->countryId;
        }

        $aAddresses = $oWorldLeader->getAddresses($iRegionId, $iCountryId);

        $oResObj->addresses = empty($aAddresses) ? new stdClass() : $aAddresses;
    }


    if (http_post('action', 'getAddress') && is_numeric(http_post('worldLeaderAddressId'))) {
        $oWorldLeaderAddress = WorldLeaderAddressManager::getWorldLeaderAddressById(http_post('worldLeaderAddressId'));

        if (!empty($oWorldLeaderAddress)) {
            $oResObj->address = $oWorldLeaderAddress->address;
            $oResObj->postalCode = empty($oWorldLeaderAddress->postalCode) ? '' : $oWorldLeaderAddress->postalCode;
            $oResObj->city = $oWorldLeaderAddress->city;

            $oCountry = $oWorldLeaderAddress->getAddressCountry();
            if (!empty($oCountry)) { 
                $oResObj->country = $oCountry->name;
            }
        }
    }

    die(json_encode($oResObj));
}

if (http_get('param1')) {
    $oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';
} else {

    if (http_post('action') == 'sendForm') {

        $aErrors = array();
        $bLogError = true;

        if (!http_post('worldLeaderId')) {
            $aErrors['country'] = 'You must choose an addresee';
        }

        if (!http_post('alias')) {
            $aErrors['alias'] = 'You must enter an alias';
        }

        if (http_post('email', false) && !filter_var(http_post('email'), FILTER_VALIDATE_EMAIL)) {
            $aErrors['email'] = 'You\'ve entered an invalid email address';
        }

        if (!http_post('countryId')) {
            $aErrors['countryId'] = 'You must select a country';
        }

        if (!http_post('title')) {
            $aErrors['title'] = 'You must enter the title of the letter';
        }

        if (!http_post('intro')) {
            $aErrors['intro'] = 'You must enter the intro of the letter';
        }

        if (!http_post('text')) {
            $aErrors['text'] = 'You must enter the text of the letter';
        }


        // do SPAM checks
        if (hasLinks(http_post('title'))) {
            $aErrors['intro'] = 'Your title contains one or more links, this is not allowed. Remove them and try again.';
            $bLogError = false;
        }

        if (hasLinks(http_post('intro'))) {
            $aErrors['intro'] = 'Your intro contains one or more links, this is not allowed. Remove them and try again.';
            $bLogError = false;
        }

        if (hasLinks(http_post('text'))) {
            $aErrors['text'] = 'Your text contains one or more links, this is not allowed. Remove them and try again.';
            $bLogError = false;
        }

        if (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) { // temporary removed for testing trap || hasLinks(http_post('bericht'))
            $aErrors['S-P-A-M'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
            $bLogError = false;
        }
        // end SPAM checks

        if (empty($aErrors)) {
            $oArticle = new Article();
            $oArticle->articleCategoryId = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
            $oArticle->authorId = User::ANONYMOUS_USER_ID;
            $oArticle->alias = http_post('alias');

            if (http_post('email')) {
                $oArticle->authorEmail = http_post('email');
            }

            $oArticle->content = _e(http_post('text'));
            $oArticle->intro = _e(http_post('intro'));
            $oArticle->parentPageId = Page::WORLD_LEADER_PAGE;
            $oArticle->title = $oArticle->shortTitle = http_post('title');
            $oArticle->status = Article::ARTICLE_STATUS_REVIEW;

            if (!$oArticle->isValid()) {
                http_redirect(getCurrentUrl());
            }

            ArticleManager::saveArticle($oArticle);
            $oWorldLeaderLetter = new WorldLeaderLetter();
            $oWorldLeaderLetter->articleId = $oArticle->articleId;
            $oWorldLeaderLetter->worldLeaderAddressId = http_post('worldLeaderAddressId', null);
            $oWorldLeaderLetter->worldLeaderId = http_post('worldLeaderId', null);
            WorldLeaderLetterManager::saveWorldLeaderLetter($oWorldLeaderLetter);

            if (http_post('links', false)) {
                $aLinks = explode('^', http_post('links', false));

                foreach ($aLinks AS $sLink) {
                    $aLinkInfo = explode('|', $sLink);

                    $oLink = new Link();
                    $oLink->title = $aLinkInfo[0];
                    $oLink->link = $aLinkInfo[1];
                    $oLink->type = Link::LINK;

                    LinkManager::saveLink($oLink);
                    ArticleManager::saveArticleLinkRelation($oArticle->articleId, $oLink->mediaId);
                }
            }

            if (http_post('videos', false)) {
                $aLinks = explode('^', http_post('videos', false));

                foreach ($aLinks AS $sLink) {
                    $aLinkInfo = explode('|', $sLink);

                    $oLink = new YoutubeLink();
                    $oLink->title = $aLinkInfo[0];
                    $oLink->link = $aLinkInfo[1];
                    $oLink->type = Link::YOUTUBE;

                    YoutubeLinkManager::saveYoutubeLink($oLink);
                    ArticleManager::saveArticleYoutubeLinkRelation($oArticle->articleId, $oLink->mediaId);
                }
            }

            if (!empty($_FILES['files']['name'][0])) {
                $iNumFiles = count($_FILES['files']['name']);
                $aFiles = array();

                foreach ($_FILES['files'] AS $sProperty => $aValues) {

                    for ($i = 0; $i < $iNumFiles; $i++) {

                        if (!isset($aFiles[$i])) {
                            $aFiles[$i] = array();
                        }

                        $aFiles[$i][$sProperty] = $aValues[$i];
                    }
                }

                for ($i = 0; $i < $iNumFiles; $i++) {
                    # upload file or return error
                    $oUpload = new Upload($aFiles[$i], Article::FILES_PATH . '/', array('txt', 'xls', 'xlsx', 'rtf', 'doc', 'docx', 'odt', 'ods', 'pdf'), null, true);

                    # save file to database on success
                    if ($oUpload->bSuccess === true) {

                        # make File object and save
                        $oFile = new File();
                        $oFile->name = $oUpload->sNewFileBaseName;
                        $oFile->mimeType = $oUpload->sMimeType;
                        $oFile->size = $oUpload->iSize;
                        $oFile->link = $oUpload->sNewFilePath;
                        $oFile->title = $oUpload->sFileName;

                        # save file
                        FileManager::saveFile($oFile);

                        # save page file relation

                        ArticleManager::saveArticleFileRelation($oArticle->articleId, $oFile->mediaId);
                    } else {
                        $aErrors['files'] = $oUpload->getErrorMessage();
                        ArticleManager::deleteArticle($oArticle->articleId);
                        break;
                    }
                }
            }

            if (!empty($_FILES['images']['name'][0])) {
                $iNumFiles = count($_FILES['images']['name']);
                $aFiles = array();

                foreach ($_FILES['images'] AS $sProperty => $aValues) {

                    for ($i = 0; $i < $iNumFiles; $i++) {

                        if (!isset($aFiles[$i])) {
                            $aFiles[$i] = array();
                        }

                        $aFiles[$i][$sProperty] = $aValues[$i];
                    }
                }

                for ($i = 0; $i < $iNumFiles; $i++) {
                    # upload file or return error
                    $oUpload = new Upload($aFiles[$i], Article::IMAGES_PATH . "/original/", null, array('jpg', 'jpeg', 'png', 'gif'), true);

                    # save image to database on success
                    if ($oUpload->bSuccess === true) {

                        // resize original to acceptable size (1500*1500 max)
                        ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg);

                        # make Image object and save
                        $oImage = new Image();
                        $oImageFile = new ImageFile();
                        $oImageFile->link = $oUpload->sNewFilePath;
                        $oImageFile->title = '';
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
                        }

                        # make crop_small
                        if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Article::IMAGES_PATH . '/crop_small/' . $oImageFile->name, 200, 150, $sErrorMsg, true)) {
                            $oImageFileThumb = clone $oImageFile;
                            $oImageFileThumb->link = Article::IMAGES_PATH . '/crop_small/' . $oImageFile->name;
                            $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                            $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                            $oImageFileThumb->reference = 'crop_small';
                            $aImageFiles[] = clone $oImageFileThumb;
                        }

                        # make cms_thumb
                        if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . $oImageFile->link, DOCUMENT_ROOT . Article::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 75, $sErrorMsg, true)) {
                            $oImageFileThumb = clone $oImageFile;
                            $oImageFileThumb->link = Article::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                            $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                            $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                            $oImageFileThumb->reference = 'cms_thumb';

                            $aImageFiles[] = clone $oImageFileThumb;
                        }

                        $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
                        # save image
                        ImageManager::saveImage($oImage);

                        # save articles item image relation
                        ArticleManager::saveArticleImageRelation($oArticle->articleId, $oImage->imageId);
                    } else {
                        $aErrors['images'] = $oUpload->getErrorMessage();
                        ArticleManager::deleteArticle($oArticle->articleId);
                        break;
                    }
                }
            }


            if (empty($aErrors)) {
                MailManager::sendMail(DEFAULT_EDITOR_EMAIL, 'You received a letter for "' . WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId'))->name . '"', _e(http_post('alias')) . ' has written an <a href="' . (CLIENT_HTTP_URL . '/admin/letter-review/bewerken/' . $oArticle->articleId) . '" target="_blank">open letter to "' . WorldLeaderManager::getWorldLeaderById(http_post('worldLeaderId'))->name . '" titled "' . $oArticle->title . '"</a>', $oArticle->authorEmail);
                http_redirect(http_get('controller') . '/thanks?conversion=1');
            }
        }
    }
    $oPageLayout->sPagePath = PAGES_FOLDER . '/sendLetter/sendLetter_form.inc.php';
}

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>