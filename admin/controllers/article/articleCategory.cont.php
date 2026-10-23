<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('articles_categories');
$oPageLayout->sModuleName = sysTranslations::get('articles_categories');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {
    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oArticleCategory = ArticleCategoryManager::getArticleCategoryById(http_get("param2"));
        
        if (empty($oArticleCategory)) {
            http_redirect(ADMIN_FOLDER . "/");
        }
    } else {
        $oArticleCategory = new ArticleCategory();
    }

    # action = save
    if (http_post("action") == 'save') {
        # load data in object
        $oArticleCategory->_load($_POST);

        if ($oCurrentUser->isSEO()) {
            $oArticleCategory->setUrlPartText(http_post('urlPartText'));
        }

        # if object is valid, save
        if ($oArticleCategory->isValid()) {
            ArticleCategoryManager::saveArticleCategory($oArticleCategory); //save object
            $_SESSION['statusUpdate'] = sysTranslations::get('articles_category_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oArticleCategory->articleCategoryId);
        } else {
            Debug::logError("", "ArticleCategory module php validate error", __FILE__, __LINE__, "Tried to save ArticleCategory with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $_SESSION['statusUpdate'] = sysTranslations::get('articles_category_not_saved');
        }
    }  
    
    if (http_post('action', null) == 'ajaxChangePage') {
        $oResObj = new stdClass();
        $oResObj->success = true;
        $oResObj->categories = ArticleCategoryManager::getArticleCategoriesByFilter(array('onlyParent' => 1, 'parentPageId' => intval(http_post('page'))));
        die(json_encode($oResObj));
    }

    $aPages = PageManager::getPagesByFilter(array('hasArticles' => 1));    
    $aParentCategories = ArticleCategoryManager::getArticleCategoriesByFilter(array('onlyParent' => 1, 'parentPageId' => $oArticleCategory->parentPageId));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/article/articleCategory/articleCategory_form.inc.php';
}

# set object online/offline
elseif (http_get("param1") == 'ajax-setOnline') {
    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iArticleCategoryId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for object
    if (is_numeric($iArticleCategoryId)) {
        $oResObj->success = ArticleCategoryManager::updateOnlineByArticleCategoryId($bOnline, $iArticleCategoryId);
        $oResObj->articleCategoryId = $iArticleCategoryId;
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
    if (is_numeric(http_get("param2"))) {
        $oArticleCategory = ArticleCategoryManager::getArticleCategoryById(http_get("param2"));
    }
    if (!empty($oArticleCategory) && ArticleCategoryManager::deleteArticleCategory($oArticleCategory)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_category_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_category_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}
# display overview
else {
    $aArticleCategories = ArticleCategoryManager::getArticleCategoriesByFilter(array('showAll' => true));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/article/articleCategory/articleCategories_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>