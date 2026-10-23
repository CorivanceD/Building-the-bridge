<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('world_leaders');
$oPageLayout->sModuleName = sysTranslations::get('world_leaders');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# get import error log
$aErrorLog = http_session('errorLog');
unset($_SESSION['errorLog']);

// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['leadersPerPage'] = http_post('perPage');
}

// handle filter
$aLeaderFilter = http_session('leaderFilter');

if (http_post('filterLeaders')) {
    $aLeaderFilter = http_post('leaderFilter');
    $_SESSION['leaderFilter'] = $aLeaderFilter;
}

if (http_post('resetFilter') || empty($aLeaderFilter)) {
    unset($_SESSION['leaderFilter']);
    $aLeaderFilter = array();
    $aLeaderFilter['name'] = '';
    $aLeaderFilter['regionId'] = '';
    $aLeaderFilter['countryId'] = '';
}

if (http_post('action') == 'ajax-getRegionCountries' && is_numeric(http_post('regionId'))) {
    $oResObj = new stdClass();
    $oResObj->countries = CountryManager::getCountriesByRegionId(http_post('regionId'));
    die(json_encode($oResObj));
}

if (http_post('action') == 'import') {

    if (http_post('regionId', false)) {
        require_once INC_FOLDER . '/PHPExcel/PHPExcel.php';

        # upload file or return error
        $oUpload = new Upload($_FILES['importFile'], ADMIN_TEMP, null, null, false);

        # save file to database on success
        if ($oUpload->bSuccess) {
            try {
                $oExcelDoc = PHPExcel_IOFactory::load(DOCUMENT_ROOT . $oUpload->sNewFilePath);
                $oExcelDoc->setActiveSheetIndex(0);
                $oExcelSheet = $oExcelDoc->getActiveSheet();

                $iCurrRow = 1;
                $aFieldMapping = array();
                $aLetters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
                $aFields = array('prefix', 'contact', 'title', 'company', 'department', 'e-mail', 'address 1', 'city', 'zip code', 'state', 'country', 'country2');
                $aReferences = array();

                //map the field names with the column letters
                foreach ($aLetters as $sLetter) {
                    $sField = strtolower(trim($oExcelSheet->getCell($sLetter . $iCurrRow)->getValue()));

                    if (strlen($sField) > 0) {
                        $aFieldMapping[strtolower(trim($oExcelSheet->getCell($sLetter . $iCurrRow)->getValue()))] = $sLetter;
                    }
                }

                $iCurrRow++;
                $aErrorLog = array();
                $iOrder = 1;

                do {
                    $oWorldLeader = WorldLeaderManager::getWorldLeaderByName(trim($oExcelSheet->getCell($aFieldMapping['contact'] . $iCurrRow)->getValue()));

                    if (empty($oWorldLeader)) {
                        $oWorldLeader = new WorldLeader();
                    }

                    $oWorldLeader->prefix = trim($oExcelSheet->getCell($aFieldMapping['prefix'] . $iCurrRow)->getValue());
                    $oWorldLeader->name = trim($oExcelSheet->getCell($aFieldMapping['contact'] . $iCurrRow)->getValue());
                    $oWorldLeader->email = trim($oExcelSheet->getCell($aFieldMapping['e-mail'] . $iCurrRow)->getValue());
                    $oWorldLeader->position = trim($oExcelSheet->getCell($aFieldMapping['title'] . $iCurrRow)->getValue());
                    $oWorldLeader->company = trim($oExcelSheet->getCell($aFieldMapping['company'] . $iCurrRow)->getValue());
                    $oWorldLeader->department = trim($oExcelSheet->getCell($aFieldMapping['department'] . $iCurrRow)->getValue());

                    if ($oWorldLeader->isValid()) {
                        WorldLeaderManager::saveWorldLeader($oWorldLeader);
                    }

                    $oWorldLeaderAddress = new WorldLeaderAddress();

                    if ($oWorldLeader->getAddresses() != null) {
                        foreach ($oWorldLeader->getAddresses() as $oAddress) {
                            if (trim($oExcelSheet->getCell($aFieldMapping['address 1'] . $iCurrRow)->getValue()) == $oAddress->address && $oAddress->regionId == http_post('regionId')) {
                                $oWorldLeaderAddress = $oAddress;
                                break;
                            }
                        }
                    }

                    $oWorldLeaderAddress->worldLeaderId = $oWorldLeader->worldLeaderId;
                    $oWorldLeaderAddress->address = trim($oExcelSheet->getCell($aFieldMapping['address 1'] . $iCurrRow)->getValue());
                    $oWorldLeaderAddress->name = trim($oExcelSheet->getCell($aFieldMapping['address 1'] . $iCurrRow)->getValue());
                    $oWorldLeaderAddress->city = trim($oExcelSheet->getCell($aFieldMapping['city'] . $iCurrRow)->getValue());
                    $oWorldLeaderAddress->postalCode = trim($oExcelSheet->getCell($aFieldMapping['zip code'] . $iCurrRow)->getValue());
                    $oWorldLeaderAddress->regionId = http_post('regionId');
                    $oWorldLeaderAddress->order = $iOrder++;

                    $oAddressCountry = CountryManager::getCountryByName(trim($oExcelSheet->getCell($aFieldMapping['country'] . $iCurrRow)->getValue()));

                    if (!empty($oAddressCountry)) {
                        $oWorldLeaderAddress->addressCountryId = $oAddressCountry->countryId;
                    } else {
                        $aErrorLog[] = 'Error (line ' . $iCurrRow . '): Country not found ' . trim($oExcelSheet->getCell($aFieldMapping['country'] . $iCurrRow)->getValue());
                    }

                    $sCountryName = trim($oExcelSheet->getCell($aFieldMapping['country2'] . $iCurrRow)->getValue());

                    if (strlen($sCountryName) > 0) {
                        $oCountry = CountryManager::getCountryByName($sCountryName);

                        if (!empty($oCountry)) {
                            $oWorldLeaderAddress->countryId = $oCountry->countryId;
                        } elseif (!empty($oAddressCountry)) {
                            $oWorldLeaderAddress->countryId = $oAddressCountry->countryId;
                        } else {
                            $aErrorLog[] = 'Error (line ' . $iCurrRow . '): Country not found ' . trim($oExcelSheet->getCell($aFieldMapping['country2'] . $iCurrRow)->getValue());
                        }
                    }
                    else {
                        if (!empty($oAddressCountry)) {
                            $oWorldLeaderAddress->countryId = $oAddressCountry->countryId;
                        } else {
                            $aErrorLog[] = 'Error (line ' . $iCurrRow . '): Country not found ' . trim($oExcelSheet->getCell($aFieldMapping['country2'] . $iCurrRow)->getValue());
                        }
                    }

                    if ($oWorldLeaderAddress->isValid()) {
                        WorldLeaderAddressManager::saveWorldLeaderAddress($oWorldLeaderAddress);
                    } else {
                        $aErrorLog[] = 'Error (line ' . $iCurrRow . '): Address not imported for ' . trim($oExcelSheet->getCell($aFieldMapping['contact'] . $iCurrRow)->getValue()) . '. Invalid props.: ' . implode(', ', $oWorldLeaderAddress->getInvalidProps());
                    }

                    $iCurrRow++;
                    $sNextContact = trim($oExcelSheet->getCell($aFieldMapping['contact'] . $iCurrRow)->getValue());
                } while (!empty($sNextContact));
                unlink(DOCUMENT_ROOT . $oUpload->sNewFilePath);
                $_SESSION['statusUpdate'] = 'Import complete';
                $_SESSION['errorLog'] = $aErrorLog;
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
            } catch (Exception $e) {
                unlink(DOCUMENT_ROOT . $oUpload->sNewFilePath);
                $_SESSION['statusUpdate'] = $e->getMessage(); //error uploading file
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
            }
        } else {
            $_SESSION['statusUpdate'] = sysTranslations::get('global_file_not_uploaded') . $oUpload->getErrorMessage(); //error uploading file
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('leader_address_no_region'); //error uploading file
        http_redirect(getCurrentUrl());
    }
}

if (http_get('param1') === 'toevoegen' || http_get('param1') === 'bewerken') {

    if (is_numeric(http_get('param2'))) {
        $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_get('param2'));

        if (empty($oWorldLeader)) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
    } else {
        $oWorldLeader = new WorldLeader();
    }

    if (http_post('action') === 'save') {
        $oWorldLeader->_load($_POST);

        if ($oWorldLeader->isValid()) {
            WorldLeaderManager::saveWorldLeader($oWorldLeader);
            $_SESSION['statusUpdate'] = sysTranslations::get('leader_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oWorldLeader->worldLeaderId);
        } else {
            Debug::logError("", "World leader module php validate error", __FILE__, __LINE__, "Tried to save a world leader with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('letter_not_saved');
        }
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/worldLeaders/leader_form.inc.php';
} elseif ((http_get('param1') === 'adres-toevoegen' || http_get('param1') === 'adres-bewerken') && is_numeric(http_get('param2'))) {

    $aCountries = null;

    if (http_get('param1') === 'adres-toevoegen') {
        $oWorldLeaderAddress = new WorldLeaderAddress();
        $oWorldLeaderAddress->worldLeaderId = http_get('param2');
    } elseif (http_get('param1') === 'adres-bewerken') {
        $oWorldLeaderAddress = WorldLeaderAddressManager::getWorldLeaderAddressById(http_get('param2'));
        if (empty($oWorldLeaderAddress)) {
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        }
        $aCountries = CountryManager::getCountriesByRegionId($oWorldLeaderAddress->regionId);
    }

    if (http_post('action') === 'save') {
        $oWorldLeaderAddress->_load($_POST);

        if (http_get('param1') === 'adres-toevoegen') {
            $oWorldLeaderAddress->worldLeaderId = http_get('param2');
        }

        if ($oWorldLeaderAddress->isValid()) {
            WorldLeaderAddressManager::saveWorldLeaderAddress($oWorldLeaderAddress);
            $_SESSION['statusUpdate'] = sysTranslations::get('leader_address_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/adres-bewerken/' . $oWorldLeaderAddress->worldLeaderAddressId);
        } else {
            Debug::logError("", "World leader module php validate error", __FILE__, __LINE__, "Tried to save a world leader address with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('leader_address_not_saved');
        }
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/worldLeaders/leader_address_form.inc.php';
} elseif (http_get('param1') === 'adres-verwijderen' && is_numeric(http_get('param2'))) {
    $oWorldLeaderAddress = WorldLeaderAddressManager::getWorldLeaderAddressById(http_get('param2'));

    if (!empty($oWorldLeaderAddress)) {
        $oWorldLeader = $oWorldLeaderAddress->getWorldLeader();
        WorldLeaderAddressManager::deleteWorldLeaderAddressById(http_get('param2'));
        $_SESSION['statusUpdate'] = sysTranslations::get('leader_address_deleted');
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oWorldLeader->worldLeaderId);
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('leader_address_not_deleted');
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
} elseif (http_get('param1') === 'verwijderen') {

    if (is_numeric(http_get("param2"))) {
        $oWorldLeader = WorldLeaderManager::getWorldLeaderById(http_get("param2"));
    }

    if (!empty($oWorldLeader) && WorldLeaderManager::deleteWorldLeaderById($oWorldLeader->worldLeaderId)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('leader_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('leader_not_deleted'); //save status update into session
    }

    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} else {
    $iPerPage = http_session('leadersPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);

    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    #display leader overview
    $aCountries = array();
    if (!empty($aLeaderFilter['regionId'])) {
        $aCountries = CountryManager::getCountriesByRegionId($aLeaderFilter['regionId']);
    }
    $aLeaders = WorldLeaderManager::getWorldLeadersByFilter($aLeaderFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/worldLeaders/leaders_overview.inc.php';
}
# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>