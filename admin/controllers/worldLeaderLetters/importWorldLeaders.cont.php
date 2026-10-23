<?

# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

foreach (ArticleCategoryManager::getArticleCategoriesByFilter() as $oArticleCategory) {

    if (intval($oArticleCategory->articleCategoryId) >= 28) {
        $sCatName = $oArticleCategory->name;
        $aCatName = explode(',', $sCatName);

        if (isset($aCatName[1])) {
            $sPosition = trim($aCatName[1]);
        } 
        else {
            $sPosition = '';
        }
        
        $aCountryAndName = explode(' ', trim($aCatName[0]));
        $sCountryName = $aCountryAndName[0];
        $oCountry = CountryManager::getCountryByName($sCountryName);
        $aName = array_slice($aCountryAndName, 1);
        $sName = implode(' ', $aName);

        echo $sCountryName . ' - ' . $oCountry->name . ', ' . $sName . ', ' . $sPosition . '<br>';

        $oWorldLeader = WorldLeaderManager::getWorldLeaderByName($sName);
        
        if (empty($oWorldLeader)) {
            $oWorldLeader = new WorldLeader();

            $oWorldLeader->name = $sName;
            $oWorldLeader->position = $sPosition;
            WorldLeaderManager::saveWorldLeader($oWorldLeader);
        }
        
        foreach ($oArticleCategory->getArticles() as $oArticle) {
            $oArticle->articleCategoryId = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
            ArticleManager::saveArticle($oArticle);
            $oWorldLeaderLetter = new WorldLeaderLetter();
            $oWorldLeaderLetter->articleId = $oArticle->articleId;
            $oWorldLeaderLetter->worldLeaderId = $oWorldLeader->worldLeaderId;  
            WorldLeaderLetterManager::saveWorldLeaderLetter($oWorldLeaderLetter);
        }
    }
}
?>