<?

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

if (!$oCurrentUser->userProfile == User::USER_EDITOR && !$oCurrentUser->userProfile == User::USER_CLIENT_ADMIN && !$oCurrentUser->isAdmin()) {
    die;
}

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('articles_review');
$oPageLayout->sModuleName = sysTranslations::get('articles_review');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
// handle perPage
if (http_post('setPerPage')) {
    $_SESSION['articlesPerPage'] = http_post('perPage');
}

// handle filter
$aArticleFilter = http_session('articleFilter');
if (http_post('filterArticles')) {
    $aArticleFilter = http_post('articleFilter');

//    if (empty($aArticleFilter['status'])) {
//        $aArticleFilter['status'] = array(Article::ARTICLE_STATUS_REVIEW, Article::ARTICLE_STATUS_PUBLISHED, Article::ARTICLE_STATUS_REJECTED, Article::ARTICLE_STATUS_OFFLINE);
//    }

    $_SESSION['articleFilter'] = $aArticleFilter;
} elseif (http_post('resetFilter') || empty($aArticleFilter)) {
    unset($_SESSION['articleFilter']);
    $aArticleFilter = array();
    $aArticleFilter['q'] = '';
    $aArticleFilter['articleCategoryId'] = '';
    $aArticleFilter['authorId'] = '';
    $aArticleFilter['status'] = '';
}

if ($oCurrentUser->userProfile == User::USER_CLIENT_ADMIN || $oCurrentUser->isAdmin()) {
    
} elseif ($oCurrentUser->userProfile == User::USER_EDITOR) {
    $aArticleFilter['editorId'] = $oCurrentUser->getEditor()->userId;
}

# handle add/edit

if (http_post("action") == 'updateStatus' && is_numeric(http_post('articleId')) && http_post('subject', false) && http_post('message', false) && is_numeric(http_post('status'))) {
    $iArticleId = intval(http_post('articleId'));
    $oArticle = ArticleManager::getArticleById($iArticleId);

    if (empty($oArticle)) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    ArticleManager::updateStatusByArticleId(intval(http_post('status')), $iArticleId);

    if (!empty($oArticle->getAuthor()->email)) {
          MailManager::sendMail($oArticle->getAuthor()->email, http_post('subject'), http_post('message'), $oCurrentUser->email);  
    }
    
    $_SESSION['statusUpdate'] = 'Article status changed';
}

if (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {

    if (is_numeric(http_get("param2"))) {
        $oArticle = ArticleManager::getArticleById(http_get("param2"));
    }

    if (!empty($oArticle) && ArticleManager::deleteArticle($oArticle)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

$iPerPage = http_session('articlesPerPage', 10);
$iCurrPage = http_get('page', 1);
$iStart = (($iCurrPage - 1) * $iPerPage);

if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

$aArticleFilter['notLetters'] = 1;
$aArticles = ArticleManager::getArticlesByFilter($aArticleFilter, $iPerPage, $iStart, $iFoundRows);
$iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;
$oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/article/articles_review.inc.php';

if (http_get("param1") == 'structuur-wijzigen') {

    if (http_post("action") == 'saveArticleStructure') {

        #
        if (http_post("articleStructure")) {
            parse_str(http_post("articleStructure"), $aArticleArray);
        }

        $iArticleOrder = 0;
        # loop trough all pages and save new parentId
        foreach ($aArticleArray['article'] AS $iArticleId => $mParentArticleReference) {

            # get page and save
            $oArticle = ArticleManager::getArticleById($iArticleId);

            if (!$oArticle)
                continue;
            if ($mParentArticleReference == 'root') {
                $oArticle->parentArticleId = null;
            } else {
                continue;
            }

            $oArticle->order = $iArticleOrder; // set the order to the page
            # save page
            if ($oArticle->isValid()) {
                ArticleManager::saveArticle($oArticle);
            }
            $iArticleOrder++;
        }
        $_SESSION['statusUpdate'] = sysTranslations::get('articles_order_saved'); //save status update into session
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    $aArticles = ArticleManager::getArticlesByFilter(array('notLetters' => 1));
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/article/articles_change_structure.inc.php';
}
# inlcude template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>