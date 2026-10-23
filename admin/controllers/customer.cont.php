<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_customers');
$oPageLayout->sModuleName = sysTranslations::get('global_customers');
# reset crop settings
unset($_SESSION['aCropSettings']);
# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle customerFilter
$aCustomerFilter = http_session('customerFilter');
if (http_post('filterForm')) {
    $aCustomerFilter = http_post('customerFilter');
    $_SESSION['customerFilter'] = $aCustomerFilter;
}

if (http_post('resetFilter') || empty($aCustomerFilter)) {
    unset($_SESSION['customerFilter']);
    $aCustomerFilter = array();
    $aCustomerFilter['name'] = '';
}

# handle perPage
if (http_post('setPerPage')) {
    $_SESSION['customersPerPage'] = http_post('perPage');
}

# check if an email address exists
if (http_get('ajax') == 'checkEmail') {
    # return if the required fields are empty
    if (empty($_GET['email']))
        die(json_encode(null));

    # return bool (true if email doesn't exists and vice versa)
    die(json_encode(!CustomerManager::emailExists(http_get('email'), http_get('customerId', null))));
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    # set crop referrer for pages module
    $_SESSION['cropReferrer'] = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . http_get("param2");

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oCustomer = CustomerManager::getCustomerById(http_get("param2"));
        if (empty($oCustomer))
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oCustomer = new Customer();
    }

    # action = save
    if (http_post("action") == 'save') {
        # do special things before load (because of password hashing)
        if (http_get("param1") == 'bewerken') {
            if (empty($_POST["password"])) {
                #set password from object
                $_POST['password'] = $oCustomer->password;
            } else {
                #hash and check password for saving later
                if (!empty($_POST['password']) && strlen(http_post('password')) >= 8) {
                    $_POST['password'] = hashPasswordForDb(http_post('password'));
                } else {
                    $_POST['password'] = null;
                }
            }
        } else {
            # check password is valid
            if (!empty($_POST['password']) && strlen(http_post('password')) >= 8) {
                $_POST['password'] = hashPasswordForDb(http_post('password'));
            } else {
                $_POST['password'] = null; //object validation will trigger error
            }
        }

        # load data in object
        $oCustomer->_load($_POST);

        # update didSignOffForEmail property when adminuser overwrites contactByEmail property
        if($oCustomer->contactByEmail == 1) {
            $oCustomer->didSignOffForEmail = 0;
        }
        
        # add http when not already added
        $oCustomer->companyWebsite = addHttp($oCustomer->companyWebsite);

        foreach (http_post("customerGroupIds", array()) AS $iCustomerGroupId) {
            $aCustomerGroups[] = new CustomerGroup(array('customerGroupId' => $iCustomerGroupId));
        }
        
        # set modules into customer
        $oCustomer->setCustomerGroups($aCustomerGroups);
        
        # if object is valid, save
        if ($oCustomer->isValid()) {
            CustomerManager::saveCustomer($oCustomer); //save object
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomer->customerId);
        } else {
            Debug::logError("", "Customer module php validate error", __FILE__, __LINE__, "Tried to save Customer with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_not_saved');
        }
    }

    if (http_post('action') == 'saveImage') {
        $oUpload = new Upload($_FILES['image'], Customer::IMAGES_PATH . "/original/", (http_post('title') != '' ? http_post('title') : null), array('jpg', 'png', 'gif', 'jpeg'), true);

        # save image to database on success
        if ($oUpload->bSuccess === true) {

            // resize original to acceptable size
            if (!ImageManager::resizeImage(DOCUMENT_ROOT . $oUpload->sNewFilePath, DOCUMENT_ROOT . $oUpload->sNewFilePath, 1500, 1500, $sErrorMsg)) {
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
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Customer::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name, 100, 100, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Customer::IMAGES_PATH . '/cms_thumb/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'cms_thumb';

                $aImageFiles[] = clone $oImageFileThumb;
            } else {
                $_SESSION['accountStatusUpdate'] = '"cms_thumb" cannot be created: ' . $sErrorMsg; //error cropping image
            }

            # make cms thumb
            if (ImageManager::autoCropAndResizeImage(DOCUMENT_ROOT . '/' . $oImageFile->link, DOCUMENT_ROOT . Customer::IMAGES_PATH . '/crop_detail/' . $oImageFile->name, 200, 200, $sErrorMsg, true)) {
                $oImageFileThumb = clone $oImageFile;
                $oImageFileThumb->link = Customer::IMAGES_PATH . '/crop_detail/' . $oImageFile->name;
                $oImageFileThumb->name = pathinfo($oImageFileThumb->link, PATHINFO_BASENAME);
                $oImageFileThumb->size = filesize(DOCUMENT_ROOT . $oImageFileThumb->link);
                $oImageFileThumb->reference = 'crop_detail';

                $aImageFiles[] = clone $oImageFileThumb;
            } else {
                $_SESSION['accountStatusUpdate'] = '"crop_detail" cannot be created' . $sErrorMsg; //error cropping image
            }

            $oImage->setImageFiles($aImageFiles); //set imageFiles to Image object
            # save image
            ImageManager::saveImage($oImage);
            # save page image relation
            CustomerManager::saveCustomerImageRelation($oCustomer->customerId, $oImage->imageId);
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomer->customerId);
        } else {

            # for SWFUpload
            if (http_post("SWFUpload")) {
                $oResObj->errorMsg = $oUpload->getErrorMessage();
                print json_encode($oResObj);
                die;
            }

            $_SESSION['statusUpdate'] = sysTranslations::get('global_image_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomer->customerId);
        }
    }

    $oImageManagerHTML = new ImageManagerHTML();
    $oImageManagerHTML->iMaxImages = 1;
    $oImageManagerHTML->aImages = $oCustomer->getImage() == null ? array() : array($oCustomer->getImage());
    $oImageManagerHTML->cropLink = ADMIN_FOLDER . '/' . http_get('controller') . '/crop-image';
    $oImageManagerHTML->sUploadUrl = ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomer->customerId;
    $oImageManagerHTML->aNeededImageFileReferences = array('cms_thumb', 'crop_detail');
    $oImageManagerHTML->bCropAfterUploadChecked = true;
    $oImageManagerHTML->bShowCropAfterUploadOption = true;
    $oImageManagerHTML->iContainerIDAddition = '2';

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customers/customer_form.inc.php';
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
# set object online/offline
} elseif (http_get("param1") == 'ajax-setOnline') {
    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iCustomerId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
# update online for object
    if (is_numeric($iCustomerId)) {
        $oResObj->success = CustomerManager::updateOnlineByCustomerId($bOnline, $iCustomerId);
        $oResObj->customerId = $iCustomerId;
        $oResObj->online = $bOnline;
    }

# redirect to overview page if this isn't AJAX
    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '');
    }

    die(json_encode($oResObj));
}

# delete object
elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2")))
        $oCustomer = CustomerManager::getCustomerById(http_get("param2"));

    if (!empty($oCustomer) && CustomerManager::deleteCustomer($oCustomer)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('customer_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('customer_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

# display overview
else {
    $iPerPage = http_session('customersPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    
    $aCustomerFilter['showAll'] = 1;

    $aCustomers = CustomerManager::getCustomersByFilter($aCustomerFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customers/customers_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>