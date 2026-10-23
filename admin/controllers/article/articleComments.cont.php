<?php

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('articles_comments');
$oPageLayout->sModuleName = sysTranslations::get('articles_comments');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle articleCommentFilter
$aArticleCommentFilter = http_session('articleCommentFilter');
if (http_post('filterForm')) {
    $aArticleCommentFilter = http_post('articleCommentFilter');
    $_SESSION['articleCommentFilter'] = $aArticleCommentFilter;
}

if (http_post('resetFilter') || empty($aArticleCommentFilter)) {
    unset($_SESSION['articleCommentFilter']);
    $aArticleCommentFilter = array();
    $aArticleCommentFilter['q'] = '';
}

# handle perPage
if (http_post('setPerPage')) {
    $_SESSION['articleCommentsPerPage'] = http_post('perPage');
}

if (http_get("param1") == 'ajax-setOnline') {
    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iArticleCommentId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for object
    if (is_numeric($iArticleCommentId)) {
        $oResObj->success = ArticleManager::updateArticleCommentOnlineStatus($iArticleCommentId, $bOnline);
        $oResObj->articleCommentId = $iArticleCommentId;
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
        $oArticleComment = ArticleManager::getArticleCommentById(http_get("param2"));

    if (!empty($oArticleComment) && ArticleManager::deleteArticleComment($oArticleComment->articleCommentId)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_comment_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_comment_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

# display overview
else {
    $iPerPage = http_session('articleCommentsPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0){
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    
    $aArticleComments = ArticleManager::getArticleCommentsByFilter($aArticleCommentFilter, $iPerPage, $iStart, $iFoundRows);
    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/article/article_comments.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>