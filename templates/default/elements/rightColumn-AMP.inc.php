<div class="column-width float-left hide-on-mobile no-print">&nbsp;</div>
<div class="column-33 float-left rightColumn no-print">
    <h3>LATEST OPEN LETTERS</h3>
    <hr>
    <ul class="worldLeaders">
        <?php
        $iFoundRows = false;
        foreach (ArticleManager::getArticlesByFilter(array('articleCategoryId' => ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY, 'status' => Article::ARTICLE_STATUS_PUBLISHED, 'parentPageId' => Page::WORLD_LEADER_PAGE), 10, 0, $iFoundRows, array('pubDate' => 'DESC')) AS $oArticle) {
            ?>
            <li><div class="pubDate"><?= Date::strToDate($oArticle->pubDate)->format('%d-%m') ?></div><a href="/amp<?= $oArticle->getUrlPath() ?>"><?= $oArticle->title ?></a></li>
        <?php } ?>        
    </ul>    
    <h3>Latest Blog Articles</h3>
    <hr>
    <ul class="latestArticles">
        <?php
        $iFoundRows = false;

        foreach (ArticleManager::getArticlesByFilter(array('notLetters' => 1, 'status' => Article::ARTICLE_STATUS_PUBLISHED, 'notParentPageId' => Page::WORLD_LEADER_PAGE), 11, 0, $iFoundRows, array('pubDate' => 'DESC')) AS $oArticle) {
            ?>
            <li><div class="pubDate"><?= Date::strToDate($oArticle->pubDate)->format('%d-%m') ?></div><a href="/amp<?= $oArticle->getUrlPath() ?>"><?= $oArticle->title ?></a></li>
        <?php } ?>
    </ul>
   
</div>