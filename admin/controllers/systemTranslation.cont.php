<?php

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('sysTrans_manager');
$oPageLayout->sModuleName = sysTranslations::get('sysTrans_manager');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['systemTranslationsPerPage'] = http_post('perPage');
}

// handle filter
$aSystemTranslationFilter = http_session('systemTranslationFilter');
if (http_post('filterSystemTranslations')) {
    $aSystemTranslationFilter = http_post('systemTranslationFilter');
    $_SESSION['systemTranslationFilter'] = $aSystemTranslationFilter;
}

if (http_post('resetFilter') || empty($aSystemTranslationFilter)) {
    unset($_SESSION['systemTranslationFilter']);
    $aSystemTranslationFilter = array();
    $aSystemTranslationFilter['systemLanguageId'] = '';
    $aSystemTranslationFilter['text'] = '';
    $aSystemTranslationFilter['label'] = '';
}

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oSystemTranslation = SystemTranslationManager::getTranslationById(http_get("param2"));
        if (!$oSystemTranslation)
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oSystemTranslation = new SystemTranslation ();
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oSystemTranslation->_load($_POST);
        $oSystemTranslation->text = http_post('text');

        $oSystemTranslation->label = trim($oSystemTranslation->label, '[]'); // trim `[` and `]` at beginning and end
        # if object is valid, save
        if ($oSystemTranslation->isValid()) {
            $bNew = empty($oSystemTranslation->systemTranslationId);
            SystemTranslationManager::saveTranslation($oSystemTranslation); //save systemTranslation
            $_SESSION['statusUpdate'] = sysTranslations::get('sysTrans_saved'); //save status update into session
            sysTranslations::reset(); // reset system translations
            if ($bNew)
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/toevoegen');
            else
                http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
        } else {
            Debug::logError("", "SystemTranslation module php validate error", __FILE__, __LINE__, "Tried to save systemTranslation with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('sysTrans_not_saved');
        }
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/systemTranslations/systemTranslation_form.inc.php';
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2"))) {
        $oSystemTranslation = SystemTranslationManager::getTranslationById(http_get("param2"));
    }

    if ($oSystemTranslation && SystemTranslationManager::deleteTranslation($oSystemTranslation)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('sysTrans_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('sysTrans_not_deleted'); //save status update into session
    }
    sysTranslations::reset(); // reset system translations
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} elseif (http_get('param1') == 'ajax-checkLabel') {

    # check if systemTranslationname exists
    $oSystemTranslation = SystemTranslationManager::getTranslationByLabel(http_get('systemLanguageId', SystemLanguage::default_languageId), trim(http_get('label'), '[]'));
    if ($oSystemTranslation && $oSystemTranslation->systemTranslationId != http_get('systemTranslationId')) {
        echo 'false';
    } else {
        echo 'true';
    }
    die;
} elseif (http_get('param1') == 'edit-full-system-translation') {
    $oSystemLanguage = SystemLanguageManager::getLanguageById(http_get('param2'));
    if (!$oSystemLanguage) {
        die;
    }

    if (http_post('action') == 'save') {
        $aSystemTranslations = http_post('systemTranslations');
        foreach ($aSystemTranslations['labels'] AS $iKey => $sLabel) {
            $sText = $aSystemTranslations['texts'][$iKey];
            if (!empty($sText)) {
                $oSystemTranslation = SystemTranslationManager::getTranslationByLabel($oSystemLanguage->systemLanguageId, $sLabel);
                if (!$oSystemTranslation) {
                    $oSystemTranslation = new SystemTranslation();
                }
                $oSystemTranslation->systemLanguageId = $oSystemLanguage->systemLanguageId;
                $oSystemTranslation->label = $sLabel;
                $oSystemTranslation->text = $sText;
                if ($oSystemTranslation->isValid()) {
                    SystemTranslationManager::saveTranslation($oSystemTranslation);
                }
            }
        }
        $_SESSION['statusUpdate'] = sysTranslations::get('sysTrans_saved'); //save status update into session
        http_redirect(getCurrentUrlPath(true));
    } else {

        $oCompareSystemLanguage = SystemLanguageManager::getLanguageById(http_get('compareSystemLanguageId', SystemLanguage::default_languageId));
        if ($oCompareSystemLanguage) {
            $aCompareSystemTranslations = SystemTranslationManager::getTranslationsByFilter(array('systemLanguageId' => $oCompareSystemLanguage->systemLanguageId));
            $aCompareTextsByLabel = array();
            foreach ($aCompareSystemTranslations AS $oCompareTranslation) {
                $aCompareTextsByLabel[$oCompareTranslation->label] = $oCompareTranslation->text;
            }
        }

        // get all unique labels and, texts where filled
        $aSystemTranslations = SystemTranslationManager::getFullTranslationForLanguage($oSystemLanguage->systemLanguageId);

        $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/systemTranslations/systemTranslation_full_form.inc.php';
    }
} else {

    $iPerPage = http_session('systemTranslationsPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0){
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    
    $aSystemTranslations = SystemTranslationManager::getTranslationsByFilter($aSystemTranslationFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/systemTranslations/systemTranslations_overview.inc.php';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>