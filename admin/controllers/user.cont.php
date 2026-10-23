<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('user_managament');
$oPageLayout->sModuleName = sysTranslations::get('user_managament');

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
        $oUser = UserManager::getUserById(http_get("param2"));
        if (!$oUser)
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oUser = new User ();
    }

    # action = save
    if (http_post("action") == 'save') {

        # do special things before load (because of password hashing)
        if (http_get("param1") == 'bewerken') {
            if (empty($_POST["password"])) {
                #set password from object
                $_POST['password'] = $oUser->password;
            } else {
                #hash and check password for saving later
                if (isValidPassword($_POST['password']))
                    $_POST['password'] = hashPasswordForDb($_POST['password']);
                else
                    $_POST['password'] = null;
            }
        }
        else {
            # check password is valid
            if (isValidPassword($_POST['password']))
                $_POST['password'] = $_POST['password'];
            else
                $_POST['password'] = null; //object validation will trigger error
        }

        # load data in object
        $oUser->_load($_POST);

        if ($oCurrentUser->userProfile == User::USER_EDITOR) {
            $oUser->userProfile = User::USER_WRITER;
            $oUser->editorId = $oCurrentUser->userId;
        }
        
        # default empty array
        $aModules = array();

        switch (intval($oUser->userProfile)) {
            case User::USER_CLIENT_ADMIN:
                $aModules[] = ModuleManager::getModuleByName(' ');
                $aModules[] = ModuleManager::getModuleByName('gebruikers');
                $aModules[] = ModuleManager::getModuleByName('paginas');
                //$aModules[] = ModuleManager::getModuleByName('form-backups');
                $aModules[] = ModuleManager::getModuleByName('article-review');
                $aModules[] = ModuleManager::getModuleByName('templates-beheer');
                $aModules[] = ModuleManager::getModuleByName('rss');
                $aModules[] = ModuleManager::getModuleByName('articles');
                $aModules[] = ModuleManager::getModuleByName('article-categories');
                $aModules[] = ModuleManager::getModuleByName('klanten');
                $aModules[] = ModuleManager::getModuleByName('article-comments');
                $aModules[] = ModuleManager::getModuleByName('petitions');
                $aModules[] = ModuleManager::getModuleByName('chatmanagement');
                $aModules[] = ModuleManager::getModuleByName('logos');
                $aModules[] = ModuleManager::getModuleByName('communicatie');
                $aModules[] = ModuleManager::getModuleByName('klant-groepen');
                $aModules[] = ModuleManager::getModuleByName('communicatie');
                $aModules[] = ModuleManager::getModuleByName('correspondenties-wereldleiders');
                $aModules[] = ModuleManager::getModuleByName('letter-review');
                $aModules[] = ModuleManager::getModuleByName('letter-comments');
                $aModules[] = ModuleManager::getModuleByName('wereldleiders');
                $aModules[] = ModuleManager::getModuleByName('link-tool');
                $oUser->setSEO(1);
                $oUser->setAdministrator(0);
                break;
            case User::USER_EDITOR:
                $aModules[] = ModuleManager::getModuleByName('gebruikers');
                $aModules[] = ModuleManager::getModuleByName('articles');
                $aModules[] = ModuleManager::getModuleByName('article-categories');
                $aModules[] = ModuleManager::getModuleByName('article-review');
                $aModules[] = ModuleManager::getModuleByName('article-comments');
                $aModules[] = ModuleManager::getModuleByName('link-tool');                
                $oUser->setSEO(0);
                $oUser->setAdministrator(0);                
                break;
            case User::USER_WRITER:
                $aModules[] = ModuleManager::getModuleByName('articles');
                $oUser->setSEO(0);
                $oUser->setAdministrator(0);                
                break;
            default:
                # foreach module id, add module object to temporary array for filling user object later
                foreach (http_post("moduleIds", array()) AS $sName => $iModuleId) {
                    $aModules[] = new Module(array('moduleId' => $iModuleId, 'name' => $sName));
                }
                
                # set modules into user
                
                if ($oCurrentUser->isAdmin()) {
                    $oUser->setAdministrator(http_post('administrator', 0));
                }

                if ($oCurrentUser->isAdmin() || $oCurrentUser->isSEO()) {
                    $oUser->setSEO(http_post('seo', 0));
                }
        }
        
        $oUser->setModules($aModules);
        # if object is valid, save
        if ($oUser->isValid()) {
            UserManager::saveUser($oUser); //save user            
            if (empty($oUser->editorId)) {
                $oUser = UserManager::getUserById($oUser->userId);                
                $oUser->editorId = $oUser->userId;
                $oUser->setModules($aModules);
                UserManager::saveUser($oUser);
            }
            
            $_SESSION['statusUpdate'] = sysTranslations::get('user_status_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oUser->userId);
        } else {
            Debug::logError("", "User module php validate error", __FILE__, __LINE__, "Tried to save user with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('user_status_not_saved');
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
        $oUpload = new Upload($_FILES['image'], User::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'png', 'gif', 'jpeg'), $bCheckMime);

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
            $oImageFile->title = http_post('username', '');
            $oImageFile->name = $oUpload->sNewFileBaseName;
            $oImageFile->mimeType = $oUpload->sMimeType;
            $oImageFile->size = filesize(DOCUMENT_ROOT . $oImageFile->link);
            $oImageFile->reference = 'original';

            $aImageFiles[] = $oImageFile;


            # make cms thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . User::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 100, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = User::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
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
            UserManager::saveUserImageRelation($oUser->userId, $oImage->imageId);

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
                    $oImageFileThumb->hasNeededImageFiles = $oImage->hasImageFiles(array('cms_thumb'));

                    $oResObj->imageFile = $oImageFileThumb; // for adding image to list (last imageFile object)
                    print json_encode($oResObj);
                    die;
                }

                # back to edit
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oUser->userId);
            }
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oUser->userId);
        }
    }
    # mask password for form (just in case)
    $oUser->maskPass();

    $aFilter = array();
    $aFilter['showAll'] = 1;
    $aFilter['active'] = 1;

    // aside admin can see all modules, others only the ones they have rights for
    if (!$oCurrentUser->isAsideAdmin())
        $aFilter['checkRights'] = true;

    # get all modules for rights
    $aModules = ModuleManager::getModulesByFilter($aFilter);

    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->aImages = $oUser->getImage() == null ? array() : array($oUser->getImage());
    $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oUser->userId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb');
    $oImageManagerHTML->bCropAfterUploadChecked = true;
    $oImageManagerHTML->bShowCropAfterUploadOption = true;
    $oImageManagerHTML->iMaxImages = 1;
    $oImageManagerHTML->iContainerIDAddition = '35';
    $oImageManagerHTML->sExtraUploadLine = sysTranslations::get('global_image_size_960_700');

    $aEditors = UserManager::getEditors();
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/users/user_form.inc.php';
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

    $oCropSettings->setAspectRatio(100, 100);

    $oCropSettings->bShowPreview = true;
    $oCropSettings->iMaxPreviewWidth = 100;

    $oCropSettings->sReferrer = http_session('cropReferrer');
    $oCropSettings->sReferrerTekst = 'user bewerken';

    # create crop but do not show on crop form page
    $oCropSettings->addCrop(100, 100, $oImage->getImageFileByReference('cms_thumb')->link, 'cms_thumb', true, true);

    # add setting to session in an array
    $_SESSION['aCropSettings'][] = clone $oCropSettings;

    http_redirect(ADMIN_FOLDER . '/crop');
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2"))) {
        $oUser = UserManager::getUserById(http_get("param2"));
    }

    if (!empty($oUser) && UserManager::deleteUser($oUser)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('user_status_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('user_status_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} elseif (http_get('param1') == 'ajax-checkUsername') {

    # check if username exists
    $oUser = UserManager::getUserByUsername(http_get('username'));
    if ($oUser && $oUser->userId != http_get('userId')) {
        echo 'false';
    } else {
        echo 'true';
    }
    die;
} elseif (http_get('param1') == 'ajax-checkEmail') {

    # check if username exists
    $oUser = UserManager::getUserByUsername(http_get('email'));
    if ($oUser && $oUser->userId != http_get('userId')) {
        echo 'false';
    } else {
        echo 'true';
    }
    die;
} else {
    $aUserFilter = array();
    
    if (intval($oCurrentUser->userProfile) === User::USER_EDITOR) {
        $aUserFilter = array('editorId' => $oCurrentUser->userId);
    }
    
    $aUsers = UserManager::getUsersByFilter($aUserFilter);
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/users/users_overview.inc.php';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>