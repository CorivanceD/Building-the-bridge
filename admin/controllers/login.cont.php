<?php
# check if controller is required by index.php 
if(!defined('ACCESS')) die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('login'). CLIENT_URL;

$sReferrer = http_session("loginReferrer", ADMIN_FOLDER . "/");

# user already logged in?
if ($oCurrentUser) {
    # send to admin
    http_redirect($sReferrer);
}

if (!empty($_POST['username']) && !empty($_POST['password']) && $_POST['login_form'] == 'send') {
    $oUser = UserManager::login($_POST['username'], $_POST['password']);
    
    if ($oUser) {
        http_redirect($sReferrer);
    } else {
        # set statusUpdate
        $oPageLayout->sStatusUpdate = sysTranslations::get('login_user_password_incorrect');
    }
}

#include right template
include_once ADMIN_TEMPLATES_FOLDER . '/login.tmpl.php';
?>