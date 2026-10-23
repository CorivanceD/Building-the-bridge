<?php

if (http_post('action', null) === 'reportComment') {
    $oResObj = new stdClass();

    if (!is_numeric(http_post('articleCommentId', null))) {
        $oResObj->success = false;
    } else {
        $oArticleComment = ArticleManager::getArticleCommentById(http_post('articleCommentId'));
        $oArticleComment->inappropriate = ArticleComment::COMMENT_INAPPROPRIATE;
        ArticleManager::saveArticleComment($oArticleComment);
        $oArticle = $oArticleComment->getArticle();
        $sTo = DEFAULT_EMAIL_FROM;
        $oTemplate = TemplateManager::getTemplateByName('report_comment');

        // check if template exists
        if (empty($oTemplate)) {
            $oResObj->success = false;
            Debug::logError('', 'Template cannot be found: `report comment` (report_comment)', __FILE__, __LINE__, '', Debug::LOG_IN_EMAIL);
        } else {
            $oTemplate->replaceVariables(null, array('[comment_postedOn]' => Date::strToDate($oArticleComment->postedOn)->format('%d-%m-%Y %H:%M'), '[comment_articleTitle]' => $oArticleComment->getArticle()->title, '[comment_email]' => $oArticleComment->authorEmail, '[comment_name]' => $oArticleComment->authorName, '[comment_text]' => $oArticleComment->comment, '[comment_confirmCode]' => $oArticleComment->confirmCode));
            $sSubject = $oTemplate->getSubject();
            $sMailBody = $oTemplate->getTemplate();
            MailManager::sendMail($sTo, $sSubject, $sMailBody);
            $oResObj->success = true;
        }
    }

    die(json_encode($oResObj));
}

/*
 * Controller to handle normal content pages
 */
$oPageLayout = new PageLayout();

$oPage = PageManager::getPageByUrlPath(getCurrentUrlPath());

if (empty($oPage)) {
    $oPage = PageManager::getPageByUrlPath('/' . http_get('controller'));
}

if (empty($oPage) || !$oPage->online) {
    showHttpError('404');
}

if ($oPage->level > 1)
    $oPageForMenu = PageManager::getPageByUrlPath('/' . http_get('controller'));
else
    $oPageForMenu = $oPage;

$oPageLayout->sPagePath = PAGES_FOLDER . '/pages/page_details.inc.php';

if (is_numeric(http_get('rssitem'))) {
    $sRssFeedItemId = intval(http_get('rssitem'));
    $oRssFeedItem = RssFeedItemManager::getRssFeedItemById($sRssFeedItemId);

    if (!empty($oRssFeedItem)) {
        $oPageLayout->sWindowTitle = strip_tags($oRssFeedItem->title);
        $oPageLayout->sMetaDescription = strip_tags($oRssFeedItem->description);
//$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());
        $oPageLayout->sOGType = 'website';
        $oPageLayout->sOGTitle = strip_tags($oRssFeedItem->title);
        $oPageLayout->sOGDescription = strip_tags($oRssFeedItem->description);
        $oPageLayout->sOGUrl = $oRssFeedItem->getPageLink($oPage);
    } else {
        http_redirect('/' . http_get('controller'));
    }
} else {
    $oPageLayout->sWindowTitle = $oPage->getWindowTitle();
    $oPageLayout->sMetaDescription = $oPage->getMetaDescription();
    $oPageLayout->sMetaKeywords = $oPage->getMetaKeywords();
    //$oPageLayout->generateCustomCrumblePath($oPage->getCrumbles());
    $oPageLayout->sOGType = 'website';
    $oPageLayout->sOGTitle = $oPage->getWindowTitle();
    $oPageLayout->sOGDescription = $oPage->getMetaDescription();
    $oPageLayout->sOGUrl = getCurrentUrl();
}

if (($oImage = $oPage->getImages('first-online')) && ($oImageFile = $oImage->getImageFileByReference('crop_small'))) {
    $oPageLayout->sOGImage = CLIENT_HTTPS_URL . $oImageFile->link;
}

$aImages = $oPage->getImages();
$aYoutubeVideos = $oPage->getYoutubeLinks();
$aFiles = $oPage->getFiles();
$aLinks = $oPage->getLinks();
$aRssFeeds = $oPage->getRssFeeds();

if (!isset($_SESSION['articleCategoryId'])) {
    $_SESSION['articleCategoryId'] = array();
}

$aArticleCategoryIds = http_session('articleCategoryId', null);
$iArticleCategoryId = empty($aArticleCategoryIds) ? 0 : (!isset($aArticleCategoryIds[http_get('controller')]) ? 0 : $aArticleCategoryIds[http_get('controller')]);
$_SESSION['articleCategoryId'][http_get('controller')] = http_post('articleCategoryId', $iArticleCategoryId);
$iArticleCategoryId = $_SESSION['articleCategoryId'][http_get('controller')];
$oArticle = null;
$aErrors = array();
$sComment = http_session('comment', '');
unset($_SESSION['comment']);
# handle perPage
if (http_post('setPerPage')) {

    if (!isset($_SESSION['itemsPerPage'])) {
        $_SESSION['itemsPerPage'] = array();
    }

    $_SESSION['itemsPerPage'][http_get('controller')] = http_post('perPage');
}

$aItemPerPage = http_session('itemsPerPage', null);
$iPerPage = empty($aItemPerPage) ? 10 : (!isset($aItemPerPage[http_get('controller')]) ? 0 : $aItemPerPage[http_get('controller')]);
$iCurrPage = http_get('page', 1);
$iStart = (($iCurrPage - 1) * $iPerPage);

if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
    http_redirect($oPage->getUrlPath());
}

$aRssFeedItems = array();

foreach ($aRssFeeds AS $oRssFeed) {
    if (empty($oRssFeed->filter)) {
        $aItems = $oRssFeed->getRssFeedItems();
    } else {
        $aFilter = explode(',', $oRssFeed->filter);
        $aItems = $oRssFeed->getRssFeedItems(array('keywords' => $aFilter, 'logicOperator' => $oRssFeed->logicOperator, 'negative' => intval($oRssFeed->negative)));
    }

    $aRssFeedItems = array_merge($aRssFeedItems, $aItems);
}

function sortFeedItems($oA1, $oA2) {
    $oDate1 = new Date($oA1->pubDate);
    $oDate2 = new Date($oA2->pubDate);

    return $oDate2->iTime - $oDate1->iTime;
}

usort($aRssFeedItems, "sortFeedItems");

$iPageCount = !empty($iPerPage) ? (ceil(count($aRssFeedItems) / $iPerPage)) : 0;

// Pagination
// page is greater than max page count, redirect to main page
if ($iPageCount > 0 && $iCurrPage > $iPageCount) {
    http_redirect($oPage->getUrlPath());
}

// pagecount is greater than 1 and not last page
if ($iPageCount > 1 && $iCurrPage < $iPageCount) {
    $oPageLayout->sRelNext = CLIENT_HTTPS_URL . $oPage->getUrlPath() . '?page=' . ($iCurrPage + 1);
}

// pagecount is greater than 1 and not first page
if ($iPageCount > 1 && $iCurrPage > 1) {
    $oPageLayout->sRelPrev = CLIENT_HTTPS_URL . $oPage->getUrlPath();
    // is second page, previous is url without page
    if (($iCurrPage - 1) > 1) {
        $oPageLayout->sRelPrev .= '?page=' . ($iCurrPage - 1);
    }
}

// is first page, set canonical in case ?page=1 is set
if ($iCurrPage == 1) {
    $oPageLayout->sCanonical = CLIENT_HTTPS_URL . $oPage->getUrlPath();
}

$aRssFeedItems = array_slice($aRssFeedItems, $iStart, empty($iPerPage) ? 10000 : $iPerPage);

if (http_get('controller', null) === 'confirm-comment') {

    if (http_get('email', null) !== null && !filter_var(http_get('email'), FILTER_VALIDATE_EMAIL)) {
        $aErrors['email'] = 'You must enter a valid email address';
    }

    if (http_get('email', null) !== null && http_get('code', null) !== null) {
        if (($iArticleCommentId = ArticleManager::confirmComment(http_get('email'), http_get('code'))) !== null) {
            $oArticleComment = ArticleManager::getArticleCommentById($iArticleCommentId);
            $oArticle = ArticleManager::getArticleById($oArticleComment->articleId);
            //http_redirect($oArticle->getUrlPath() . '#comment-' . $iArticleCommentId);
            $_SESSION['confirmedCommentUrl'] = $oArticle->getUrlPath() . '#comment-' . $iArticleCommentId;
            http_redirect('/please-like-us');
        }
    }
} elseif ($oPage->hasArticles > 0) {
    $oArticleComment = http_session('articleComment', null);

    if (!empty($oArticleComment) && isset($oCurrentCustomer)) {
        unset($_SESSION['articleComment']);
        $oArticleComment->authorId = $oCurrentCustomer->customerId;
        $oArticleComment->authorName = $oCurrentCustomer->name;
        $oArticleComment->authorEmail = $oCurrentCustomer->email;
        $oArticleComment->confirmCode = null;

        if ($oArticleComment->isValid()) {
            ArticleManager::saveArticleComment($oArticleComment);
        } else {
            $aErrors['comment'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
        }

        http_redirect(getCurrentUrl() . '#comment-' . $oArticleComment->articleCommentId);
    }

    if (http_post('action', false) == 'postComment' && is_numeric(http_post('articleId', false))) {

        if (empty($oCurrentCustomer)) {
            if (http_post('authorName', null) === null) {
                $aErrors['authorName'] = 'You must enter your name';
            }

            if (http_post('authorEmail', null) === null) {
                $aErrors['authorEmail'] = 'You must enter your email address';
            } elseif (!filter_var(http_post('authorEmail'), FILTER_VALIDATE_EMAIL)) {
                $aErrors['authorEmail'] = 'You must enter a valid email address';
            } else if (CustomerManager::emailExists(http_post('authorEmail'))) {
                $_SESSION['frontendLoginReferrer'] = getCurrentUrl();
                $_SESSION['accountStatusUpdate'] = 'You have already have an account. Please, log in before posting a comment';
                // do SPAM checks
                if (hasLinks(http_post('comment'))) {
                    $aErrors['comment'] = 'Comments cannot contain links';
                } elseif (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) { // temporary removed for testing trap || hasLinks(http_post('bericht'))
                    $aErrors['S-P-A-M'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
                }
                // end SPAM checks     
                else {
                    $oArticleComment = new ArticleComment();
                    $oArticleComment->articleId = intval(http_post('articleId'));
                    $oArticleComment->comment = http_post('comment');

                    $_SESSION['articleComment'] = $oArticleComment;
                    http_redirect('/account');
                }
            }
        }

        if (http_post('comment', null) === null) {
            $aErrors['comment'] = 'You must write your comment';
        }

        // do SPAM checks
        if (hasLinks(http_post('comment'))) {
            $aErrors['comment'] = 'Comments cannot contain links';
        }

        if (http_post('rumpelstiltskin-empty') || !http_post('rumpelstiltskin-filled')) { // temporary removed for testing trap || hasLinks(http_post('bericht'))
            $aErrors['S-P-A-M'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
        }
        // end SPAM checks     

        if (empty($aErrors)) {
            $oArticleComment = new ArticleComment();
            $oArticleComment->articleId = intval(http_post('articleId'));
            $oArticleComment->authorId = empty($oCurrentCustomer) ? null : $oCurrentCustomer->customerId;
            $oArticleComment->authorName = empty($oCurrentCustomer) ? http_post('authorName') : $oCurrentCustomer->name;
            $oArticleComment->authorEmail = empty($oCurrentCustomer) ? http_post('authorEmail') : $oCurrentCustomer->email;
            $oArticleComment->comment = http_post('comment');
            $oArticleComment->confirmCode = empty($oCurrentCustomer) ? substr(hash('sha512', uniqid(microtime() . $oArticleComment->authorEmail, 1)), 15, 50) : null;

            if ($oArticleComment->isValid()) {
                ArticleManager::saveArticleComment($oArticleComment);

                if (empty($oCurrentCustomer)) {
                    $sTo = $oArticleComment->authorEmail;

                    $oTemplate = TemplateManager::getTemplateByName('confirm_comment');

                    // check if template exists
                    if (empty($oTemplate)) {
                        Debug::logError('', 'Template cannot be found: `confirm comment` (confirm_comment)', __FILE__, __LINE__, '', Debug::LOG_IN_EMAIL);
                    } else {
                        $oTemplate->replaceVariables(null, array('[comment_postedOn]' => Date::strToDate($oArticleComment->postedOn)->format('%d-%m-%Y %H:%M'), '[comment_articleTitle]' => $oArticleComment->getArticle()->title, '[comment_email]' => $oArticleComment->authorEmail, '[comment_name]' => $oArticleComment->authorName, '[comment_text]' => $oArticleComment->comment, '[comment_confirmCode]' => $oArticleComment->confirmCode));
                        $sSubject = $oTemplate->getSubject();
                        $sMailBody = $oTemplate->getTemplate();
                        MailManager::sendMail($sTo, $sSubject, $sMailBody);
                        http_redirect('/confirm-comment');
                    }
                }
            } else {
                $aErrors['comment'] = 'An error occurred while sending the request. Try again or take another way to contact us.';
            }
        }
    }

//    if (is_numeric(http_post('articleCategoryId'))) {
//        $_SESSION['articleCategoryId'] = intval(http_post('articleCategoryId', 0));
//    } else {
//        $_SESSION['articleCategoryId'] = http_session('articleCategoryId', 0);
//    }

    $aArticleCategories = ArticleCategoryManager::getArticleCategoriesByFilter(array('hasNArticles' => 1, 'parentPageId' => $oPage->pageId));

    if (is_numeric(http_get('lastparam'))) {
        $iArticleId = intval(http_get('lastparam'));

        if (!empty($oCurrentUser) && ($oCurrentUser->isAdmin() || $oCurrentUser->userProfile == User::USER_EDITOR || $oCurrentUser->userProfile == User::USER_CLIENT_ADMIN)) {
            $aArticles = ArticleManager::getArticlesByFilter(array('articleId' => $iArticleId));
        } else {
            $aArticles = ArticleManager::getArticlesByFilter(array('status' => Article::ARTICLE_STATUS_PUBLISHED, 'articleId' => $iArticleId));
        }

        function articleSort(Article $oA1, Article $oA2) {
            $oDate1 = new Date($oA1->pubDate);
            $oDate2 = new Date($oA2->pubDate);

            return $oDate2->iTime - $oDate1->iTime;
        }

        usort($aArticles, 'articleSort');

        $aArticles = array_slice($aArticles, $iStart, empty($iPerPage) ? 10000 : $iPerPage);

        if (count($aArticles) === 0) {
            http_redirect('/' . http_get('controller'));
        } else {
            $oArticle = $aArticles[0];

            $oPageLayout->sWindowTitle = $oArticle->getWindowTitle();
            $oPageLayout->sMetaDescription = $oArticle->getMetaDescription();
            $oPageLayout->sMetaKeywords = $oArticle->getMetaKeywords();
            $oPageLayout->sOGType = 'article';
            $oPageLayout->sOGTitle = $oArticle->getWindowTitle();
            $oPageLayout->sOGDescription = $oArticle->getMetaDescription();
            $oImage = $oArticle->getImages('first-online');
            if (!empty($oImage)) {
                $oPageLayout->sOGImage = CLIENT_HTTPS_URL . $oImage->getImageFileByReference('detail')->link;
            }
            $oPageLayout->sOGUrl = getCurrentUrl();
            $aImages = $oArticle->getImages('online');
            $aYoutubeVideos = $oArticle->getYoutubeLinks('online');
            $aLinks = $oArticle->getFiles('online');
            $aFiles = $oArticle->getLinks('online');
        }
    } else {

        //if (http_session('articleCategoryId', 0) == 0) {
        if ($iArticleCategoryId == 0) {
            $iFoundRows = false;

            if ($oPage->pageId == Page::WORLD_LEADER_PAGE) {
                $aArticles = ArticleManager::getArticlesByFilter(array('status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => $oPage->pageId), null, 0, $iFoundRows, array('`a`.`pubDate`' => 'DESC'));
            } else {
                $aArticles = ArticleManager::getArticlesByFilter(array('status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => $oPage->pageId));
            }
        } else {
            $aArticles = ArticleManager::getArticlesByFilter(array('status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => $oPage->pageId, 'articleCategoryId' => $iArticleCategoryId));

            if (empty($aArticles)) {
                //$oArticleCategory = ArticleCategoryManager::getArticleCategoryById(http_session('articleCategoryId'));
                $oArticleCategory = ArticleCategoryManager::getArticleCategoryById($iArticleCategoryId);
                $aSubcategories = $oArticleCategory->getSubcategories();
                $aArticles = array();
                foreach ($aSubcategories AS $oSubcategory) {
                    $aAux = ArticleManager::getArticlesByFilter(array('status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => $oPage->pageId, 'articleCategoryId' => $oSubcategory->articleCategoryId));

                    if (!empty($aAux)) {
                        $aArticles = array_merge($aArticles, $aAux);
                    }
                }
            }
        }

        function articleSort(Article $oA1, Article $oA2) {
//            $oDate1 = new Date($oA1->pubDate);
//            $oDate2 = new Date($oA2->pubDate);
//            return $oDate2->iTime - $oDate1->iTime;
            return $oA2->order - $oA1->order;
        }

        $iPageCount = !empty($iPerPage) ? (ceil(count($aArticles) / $iPerPage)) : 0;

        // Pagination
        // page is greater than max page count, redirect to main page
        if ($iPageCount > 0 && $iCurrPage > $iPageCount) {
            http_redirect($oPage->getUrlPath());
        }

        // pagecount is greater than 1 and not last page
        if ($iPageCount > 1 && $iCurrPage < $iPageCount) {
            $oPageLayout->sRelNext = CLIENT_HTTPS_URL . $oPage->getUrlPath() . '?page=' . ($iCurrPage + 1);
        }

        // pagecount is greater than 1 and not first page
        if ($iPageCount > 1 && $iCurrPage > 1) {
            $oPageLayout->sRelPrev = CLIENT_HTTPS_URL . $oPage->getUrlPath();
            // is second page, previous is url without page
            if (($iCurrPage - 1) > 1) {
                $oPageLayout->sRelPrev .= '?page=' . ($iCurrPage - 1);
            }
        }

        // is first page, set canonical in case ?page=1 is set
        if ($iCurrPage == 1) {
            $oPageLayout->sCanonical = CLIENT_HTTPS_URL . $oPage->getUrlPath();
        }

        if ($oPage->pageId != Page::WORLD_LEADER_PAGE) {
            usort($aArticles, 'articleSort');
        }
        $aArticles = array_slice($aArticles, $iStart, empty($iPerPage) ? 10000 : $iPerPage);
    }
}

include_once TEMPLATES_FOLDER . '/default.tmpl.php';
?>