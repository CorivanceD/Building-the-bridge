<?php

include_once INC_FOLDER . '/FeedWriter/Item.php';
include_once INC_FOLDER . '/FeedWriter/Feed.php';
include_once INC_FOLDER . '/FeedWriter/RSS2.php';

define('ARTICLE_PER_CATEGORY', 10);

$iPageId = http_get('param1', null);

use FeedWriter\RSS2;

$oRssFeed = new RSS2();

$aArticleCategories = array();

if (is_numeric($iPageId) && ($oPage = PageManager::getPageById($iPageId))) {
    $oRssFeed->setLink(CLIENT_HTTPS_URL);
    $oRssFeed->setChannelElement('language', 'en-US');
    $oRssFeed->setDate(date(DATE_RSS, time()));
    $oRssFeed->setChannelElement('pubDate', date(DATE_RSS, time()));
    $oRssFeed->setSelfLink(CLIENT_HTTPS_URL . '/rss/0');
    $oRssFeed->setTitle('Building the Bridge > ' . $oPage->title);
    $oRssFeed->setDescription('This feed includes the latest articles in "' . $oPage->title . '" category');
    $oRssFeed->addGenerator();

    if ($iPageId == Page::WORLD_LEADER_PAGE) {
            $aArticles = ArticleManager::getArticlesByFilter(array('articleCategoryId' => ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY, 'status' => Article::ARTICLE_STATUS_PUBLISHED));
            $iIndex = 0;

            foreach ($aArticles AS $oArticle) {
                $oNewItem = $oRssFeed->createNewItem();
                $oNewItem->setTitle($oArticle->title);
                $oNewItem->setLink(CLIENT_HTTPS_URL . $oArticle->getUrlPath());
                $oNewItem->setDescription('<i>Open letter from ' .  $oArticle->getAuthor()->name . ' to ' . $oArticle->getWorldLeaderLetter()->getWorldLeader()->getCompleteName() .'</i><br>'. $oArticle->intro);
                $oNewItem->setDate(Date::strToDate($oArticle->pubDate)->format('%Y-%m-%d %H:%M:%S'));
                $oNewItem->setAuthor($oArticle->getAuthor()->name, $oArticle->getAuthor()->email);
                $oRssFeed->addItem($oNewItem);
                $iIndex++;

                if ($iIndex === ARTICLE_PER_CATEGORY) {
                    break;
                }
            }        
    } else {
        $aArticleCategories = ArticleCategoryManager::getArticleCategoriesByFilter(array('parentPageId' => $oPage->pageId));

        foreach ($aArticleCategories AS $oArticleCategory) {
            $aArticles = ArticleManager::getArticlesByFilter(array('articleCategoryId' => $oArticleCategory->articleCategoryId, 'status' => Article::ARTICLE_STATUS_PUBLISHED));
            $iIndex = 0;

            foreach ($aArticles AS $oArticle) {
                $oNewItem = $oRssFeed->createNewItem();
                $oNewItem->setTitle($oArticle->title);
                $oNewItem->setLink(CLIENT_HTTPS_URL . $oArticle->getUrlPath());
                $oNewItem->setDescription($oArticle->intro);
                $oNewItem->setDate(Date::strToDate($oArticle->pubDate)->format('%Y-%m-%d %H:%M:%S'));
                $oNewItem->setAuthor($oArticle->getAuthor()->name, $oArticle->getAuthor()->email);
                $oRssFeed->addItem($oNewItem);
                $iIndex++;

                if ($iIndex === ARTICLE_PER_CATEGORY) {
                    break;
                }
            }
        }


        $aRssFeeds = $oPage->getRssFeeds();

        foreach ($aRssFeeds AS $oFeed) {
            if (empty($oFeed->filter)) {
                $aFeedItems = $oFeed->getRssFeedItems();
            } else {
                $aFilter = explode(',', $oFeed->filter);
                $aFeedItems = $oFeed->getRssFeedItems(array('keywords' => $aFilter, 'logicOperator' => $oFeed->logicOperator, 'negative' => intval($oFeed->negative)));
            }

            foreach ($aFeedItems AS $oFeedItem) {
                $oNewItem = $oRssFeed->createNewItem();
                $oNewItem->setTitle($oFeedItem->title);
//            $oNewItem->setLink($oFeedItem->link);
                //$oNewItem->setLink($oFeedItem->getPageLink());
                $oNewItem->setLink($oFeedItem->getPageLink($oPage));
                $oNewItem->setDescription($oFeedItem->description);
                $oNewItem->setDate(Date::strToDate($oFeedItem->pubDate)->format('%Y-%m-%d %H:%M:%S'));
                $oNewItem->setAuthor($oFeedItem->author, '');
                $oRssFeed->addItem($oNewItem);
            }
        }
    }
    

    $sFeed = $oRssFeed->generateFeed();
    echo $sFeed;    
} else {
    $aPages = PageManager::getPagesByFilter(array('hasArticles' => 1));
    $aPages = array_merge($aPages, PageManager::getPagesByFilter(array('hasRssFeeds' => 1)));
    $oPageLayout = new PageLayout();
    $oPageLayout->sPagePath = PAGES_FOLDER . '/rss/rss_feeds.inc.php';
    $oPageLayout->sWindowTitle = 'RSS Feeds';
    $oPageLayout->sMetaDescription = 'This page includes the rss feeds of Building the Bridge website';
    include_once TEMPLATES_FOLDER . '/default.tmpl.php';
}
?>

