<?php

/* Models and managers used by the ArticleCategory model */
require_once 'ArticleCategory.class.php';

class ArticleCategoryManager {

    /**
     * get the full ArticleCategory object by id
     * @param int $iArticleCategoryId
     * @return ArticleCategory
     */
    public static function getArticleCategoryById($iArticleCategoryId) {
        $sQuery = ' SELECT
                        `ac`.*
                    FROM
                        `articleCategories` `ac`
                    WHERE
                        `ac`.`articleCategoryId` = ' . db_int($iArticleCategoryId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "ArticleCategory");
    }
        
    
    /**
     * Get the subcategories of a category
     * 
     * @param int $iArticleCategoryId
     * @return array ArticleCategory
     */
    public static function getArticleCategorySubcategories($iArticleCategoryId) {
        $sQuery = ' SELECT
                        `ac`.*
                    FROM
                        `articleCategories` `ac`
                    WHERE
                        `ac`.`parentCategoryId` = ' . db_int($iArticleCategoryId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "ArticleCategory");
    }

    /**
     * get the full ArticleCategory object by urlPart
     * @param string $sUrlPart
     * @return ArticleCategory
     */
    public static function getArticleCategoryByUrlPart($sUrlPart) {
        $sQuery = ' SELECT
                        `ac`.*
                    FROM
                        `articleCategories` `ac`
                    WHERE
                        `ac`.`urlPart` = ' . db_str($sUrlPart) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "ArticleCategory");
    }

    /**
     * check if urlPart exists excluding given ArticleCategory
     * @param string $sPartToCheck
     * @param int $iArticleCategoryId
     * @return bool
     */
    private static function urlPartExists($sPartToCheck, $iArticleCategoryId) {
        $oArticleCategory = self::getArticleCategoryByUrlPart($sPartToCheck);
        if ($oArticleCategory) {
            if ($oArticleCategory->articleCategoryId != $iArticleCategoryId) {
                return true;
            }
        }
        return false;
    }

    /**
     * save ArticleCategory object
     * @param ArticleCategory $oArticleCategory
     */
    public static function saveArticleCategory(ArticleCategory $oArticleCategory) {

        $sGeneratedUrlPart = $oArticleCategory->generateUrlPart();
        $iT = 0;
        $sPartToCheck = $sGeneratedUrlPart;

        # while urlPath is not uaque, excluding this page, make uaque
        while (self::urlPartExists($sPartToCheck, $oArticleCategory->articleCategoryId)) {
            $iT++;
            $sPartToCheck = $sGeneratedUrlPart . "-$iT";
        }

        # part is last uaque part
        $sGeneratedUrlPart = $sPartToCheck;

        # save article item
        $sQuery = ' INSERT INTO `articleCategories` (
                        `articleCategoryId`,
                        `parentCategoryId`,
                        `parentPageId`,
                        `windowTitle`,
                        `metaKeywords`,
                        `metaDescription`,
                        `name`,
                        `urlPart`,
                        `urlPartText`,
                        `online`,
                        `order`,
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oArticleCategory->articleCategoryId) . ',
                        ' . db_int($oArticleCategory->parentCategoryId) . ',
                        ' . db_int($oArticleCategory->parentPageId) . ',
                        ' . db_str($oArticleCategory->windowTitle) . ',
                        ' . db_str($oArticleCategory->metaKeywords) . ',
                        ' . db_str($oArticleCategory->metaDescription) . ',
                        ' . db_str($oArticleCategory->name) . ',
                        ' . db_str($sGeneratedUrlPart) . ',
                        ' . db_str($oArticleCategory->getUrlPartText()) . ',
                        ' . db_int($oArticleCategory->online) . ',
                        ' . db_int($oArticleCategory->order) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `parentCategoryId`=VALUES(`parentCategoryId`),
                        `parentPageId`=VALUES(`parentPageId`),
                        `windowTitle`=VALUES(`windowTitle`),
                        `metaKeywords`=VALUES(`metaKeywords`),
                        `metaDescription`=VALUES(`metaDescription`),
                        `name`=VALUES(`name`),
                        `urlPart`=VALUES(`urlPart`),
                        `urlPartText`=VALUES(`urlPartText`),
                        `online`=VALUES(`online`),
                        `order`=VALUES(`order`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oArticleCategory->articleCategoryId === null)
            $oArticleCategory->articleCategoryId = $oDb->insert_id;
    }

    /**
     * delete article item and all media
     * @param ArticleCategory $oArticleCategory
     * @return Boolean
     */
    public static function deleteArticleCategory(ArticleCategory $oArticleCategory) {
        /* check if article item exists and is deletable */
        if ($oArticleCategory->isDeletable()) {
            $sQuery = "DELETE FROM `articleCategories` WHERE `articleCategoryId` = " . db_int($oArticleCategory->articleCategoryId) . ";";

            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online by article item id
     * @param int $bOnline
     * @param int $iArticleCategoryId
     * @return boolean
     */
    public static function updateOnlineByArticleCategoryId($bOnline, $iArticleCategoryId) {
        $sQuery = ' UPDATE
                        `articleCategories`
                    SET
                        `online` = ' . db_int($bOnline) . '
                    WHERE
                        `articleCategoryId` = ' . db_int($iArticleCategoryId) . '
                    ;';
        $oDb = DBConnections::get();

        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }

    /**
     * return articleCategories filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Module
     */
    public static function getArticleCategoriesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`ac`.`articleCategoryId`' => 'ASC', '`ac`.`name`' => 'ASC')) {
        $sWhere = '`ac`.`articleCategoryId` >= 0';
        
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`online` = 1';
        }

        if (!empty($aFilter['name'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`name` LIKE ' . db_str($aFilter['name'] . '%');
        }         

        if (!empty($aFilter['parentPageId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`parentPageId`  = '.db_int($aFilter['parentPageId']);
        }
        
        if (!empty($aFilter['onlyParent'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`parentCategoryId` IS NULL';
        }
        
        if (!empty($aFilter['onlySub'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '(`ac`.`parentCategoryId` IS NOT NULL OR (`ac`.`parentCategoryId` IS NULL AND `ac`.`articleCategoryId` NOT IN (SELECT DISTINCT `parentCategoryId` FROM `articleCategories` WHERE `parentCategoryId` IS NOT NULL)))';
        }  
        
        if (!empty($aFilter['hasNArticles'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . 
                      '(`ac`.`articleCategoryId` IN '
                    . '     ( SELECT `articleCategoryId`'
                    . '       FROM '
                    . '             ( SELECT `articleCategoryId`, COUNT(*) AS `articleAmount` '
                    . '               FROM `articles` '
                    . '               WHERE `status` = '.db_int(Article::ARTICLE_STATUS_PUBLISHED)
                    . '               GROUP BY `articleCategoryId`) AS `aux`'
                    . '       WHERE `articleAmount` >= '.db_int($aFilter['hasNArticles'])
                    . '     )'
                    . ' OR `ac`.`articleCategoryId` IN '
                    . '     ( SELECT DISTINCT `parentCategoryId` '
                    . '       FROM `articleCategories` '
                    . '       WHERE `parentCategoryId` IS NOT NULL '
                    . '             AND `articleCategoryId` IN '
                    . '                 ( SELECT DISTINCT `articleCategoryId` '
                    . '                   FROM `articles` '
                    . '                   WHERE `status` = '.db_int(Article::ARTICLE_STATUS_PUBLISHED)
                    . '                  )'
                    . '      )'
//                    . ' OR (`ac`.`parentCategoryId` IS NULL '
//                    . '      AND `ac`.`articleCategoryId` NOT IN '
//                    . '         ( SELECT DISTINCT `parentCategoryId` '
//                    . '             FROM `articleCategories` '
//                    . '             WHERE `parentCategoryId` IS NOT NULL'
//                    . '         )'
//                    . '     )'                    
                    . ')';            
        }
        

        # handle order by
        $sOrderBy = '';
        if (count($aOrderBy) > 0) {
            foreach ($aOrderBy AS $sColumn => $sOrder) {
                $sOrderBy .= ($sOrderBy !== '' ? ',' : '') . $sColumn . ' ' . $sOrder;
            }
        }
        $sOrderBy = ($sOrderBy !== '' ? 'ORDER BY ' : '') . $sOrderBy;

        # handle start,limit
        $sLimit = '';
        if (is_numeric($iLimit)) {
            $sLimit .= db_int($iLimit);
        }
        if ($sLimit !== '') {
            $sLimit = (is_numeric($iStart) ? db_int($iStart) . ',' : '0,') . $sLimit;
        }
        $sLimit = ($sLimit !== '' ? 'LIMIT ' : '') . $sLimit;

        $sQuery = ' SELECT ' . ($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '') . '
                        `ac`.*
                    FROM
                        `articleCategories` AS `ac`
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    GROUP BY
                        `ac`.`articleCategoryId`
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aArticleCategories = $oDb->query($sQuery, QRY_OBJECT, "ArticleCategory");
        
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }        

        return $aArticleCategories;
    }

    /**
     * get articles for category by filter
     * @param int $iArticleCategoryId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Article
     */
    public static function getArticlesByFilter($iArticleCategoryId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (!empty($aFilter['status'])) {
            $sWhere .= 'AND `a`.`status` = ' .db_int($aFilter['status']);
        }

        $sQuery = ' SELECT
                        `a`.*
                    FROM
                        `articles` AS `a`
                    WHERE
                        `a`.`articleCategoryId` = ' . db_int($iArticleCategoryId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `a`.`pubDate` DESC, `a`.`articleId` DESC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Article');
    }
}
?>