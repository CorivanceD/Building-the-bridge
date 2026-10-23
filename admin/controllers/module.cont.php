<?php

# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('modules_management');
$oPageLayout->sModuleName = sysTranslations::get('modules_management');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oModule = ModuleManager::getModuleById(http_get("param2"));
        if (!$oModule)
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oModule = new Module ();
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oModule->_load($_POST);

        # if object is valid, save
        if ($oModule->isValid()) {
            ModuleManager::saveModule($oModule); //save module
            $_SESSION['statusUpdate'] = sysTranslations::get('modules_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oModule->moduleId);
        } else {
            Debug::logError("", "Modules module php validate error", __FILE__, __LINE__, "Tried to save Module with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $oPageLayout->sStatusUpdate = sysTranslations::get('modules_not_saved');
        }
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/' . http_get('controller') . '/module_form.inc.php';
} elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2"))) {
        $oModule = ModuleManager::getModuleById(http_get("param2"));
    }

    if ($oModule && ModuleManager::deleteModule($oModule)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('modules_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('modules_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
} else {
    $aModules = ModuleManager::getModulesByFilter(array('showAll' => 1));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/' . http_get('controller') . '/modules_overview.inc.php';
}

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>