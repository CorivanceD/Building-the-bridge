<?php

// check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

// reset crop settings
unset($_SESSION['aCropSettings']);

// set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('formbackups_formbackups');
$oPageLayout->sModuleName = sysTranslations::get('formbackups_formbackups');

// get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['formBackupsPerPage'] = http_post('perPage');
}

// handle filter
$aFormBackupFilter = http_session('formBackupFilter');
if (http_post('filterFormBackups')) {
    $aFormBackupFilter = http_post('formBackupFilter');
    $_SESSION['formBackupFilter'] = $aFormBackupFilter;
}

if (http_post('resetFilter') || empty($aFormBackupFilter)) {
    unset($_SESSION['formBackupFilter']);
    $aFormBackupFilter = array();
    $aFormBackupFilter['q'] = '';
    $aFormBackupFilter['created'] = '';
}

if (http_get('param1') == 'ajax-getDetails') {

    # get formBackup
    $oFormBackup = FormBackupManager::getFormBackupById(http_get('param2'));

    #include page and `die` for ajax without template
    include_once ADMIN_PAGES_FOLDER . '/formBackup/formBackup_details.inc.php';
    die;
} else {

    $iPerPage = http_session('formBackupsPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0){
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    
    #display FormBackup overview
    $aFormBackups = FormBackupManager::getFormBackupsByFilter($aFormBackupFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/formBackup/formBackups_overview.inc.php';
}
# inlcude template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>