<?
# check if controller is required by index.php 
if(!defined('ACCESS')) die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('global_home');
$oPageLayout->sModuleName = sysTranslations::get('global_home');

$oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/home/home.inc.php';

include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>