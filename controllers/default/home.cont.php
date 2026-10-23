<?php

/*
 * controller to get all data for homepage
 */

# make pageLayout Object
$oPageLayout = new PageLayout();

# get Page by id
$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

$oPageLayout->sWindowTitle = $oPage->getWindowTitle();
$oPageLayout->sMetaDescription = $oPage->getMetaDescription();
$oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles(), false);
$oPageLayout->sOGType = 'website';
$oPageLayout->sOGTitle = $oPage->getWindowTitle();
$oPageLayout->sOGDescription = $oPage->getMetaDescription();
$oPageLayout->sOGUrl = getCurrentUrl();

$aArticleFilter = array('status' => Article::ARTICLE_STATUS_PUBLISHED);

unset($_SESSION['searchArticles']);
$iFoundArticles = true;
$iFoundRssFeedItems = true;

if (http_post('action', false) === 'search' && http_post('q', false)) {
    $aArticleFilter['q'] = http_post('q');
    $_SESSION['searchArticles'] = 1;
    $aLastArticles = ArticleManager::getArticlesByFilter($aArticleFilter, 5, 0, $iFoundArticles);
    $aRssFeedItems = RssFeedItemManager::getRssFeedItemsByFilter($aArticleFilter, 5, 0, $iFoundRssFeedItems);
} elseif (http_post('contentType')) {
    $_SESSION['contentType'] = http_post('contentType');
    switch (intval(http_post('contentType'))) {
        case CONTENT_HOPE:
            $aArticleFilter['notLetters'] = 1;
            $aLastArticles = ArticleManager::getArticlesByFilter($aArticleFilter, 5, 0, $iFoundArticles);
            $aRssFeedItems = RssFeedItemManager::getRssFeedItemsByFilter($aArticleFilter, 5, 0, $iFoundRssFeedItems);
            break;
        case CONTENT_LATEST_LETTERS:
            $aArticleFilter['articleCategoryId'] = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
            $aLastArticles = ArticleManager::getArticlesByFilter($aArticleFilter, 5, 0, $iFoundArticles);
            break;
        case CONTENT_LUMINARIES:
            $aArticleFilter['onlyLuminaries'] = 1;
            $aLastArticles = ArticleManager::getArticlesByFilter($aArticleFilter, 5, 0, $iFoundArticles);
            break;
        case CONTENT_SEND_LETTER:
            http_redirect(CLIENT_HTTP_URL . '/send-letter');
        case CONTENT_WORLD_LEADERS:
            http_redirect(CLIENT_HTTP_URL . '/world-leaders');            
    }
} else {
    $aArticleFilter['notLetters'] = 1;
    $aLastArticles = ArticleManager::getArticlesByFilter($aArticleFilter, 10, 0, $iFoundArticles);
    $aRssFeedItems = RssFeedItemManager::getRssFeedItemsByFilter($aArticleFilter, 5, 0, $iFoundRssFeedItems);
}

$oPageLayout->sPagePath = PAGES_FOLDER . '/home/home.inc.php'; // special home page
# include the error template
include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>
