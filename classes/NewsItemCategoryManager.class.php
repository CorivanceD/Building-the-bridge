<?php

/* Models and managers used by the NewsItemCategory model */
require_once 'NewsItemCategory.class.php';

class NewsItemCategoryManager {

    /**
     * get the full NewsItemCategory object by id
     * @param int $iNewsItemCategoryId
     * @return NewsItemCategory
     */
    public static function getNewsItemCategoryById($iNewsItemCategoryId) {
        $sQuery = ' SELECT
                        `nic`.*
                    FROM
                        `newsItemCategories` `nic`
                    WHERE
                        `nic`.`newsItemCategoryId` = ' . db_int($iNewsItemCategoryId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "NewsItemCategory");
    }

    /**
     * get the full NewsItemCategory object by urlPart
     * @param string $sUrlPart
     * @return NewsItemCategory
     */
    public static function getNewsItemCategoryByUrlPart($sUrlPart) {
        $sQuery = ' SELECT
                        `nic`.*
                    FROM
                        `newsItemCategories` `nic`
                    WHERE
                        `nic`.`urlPart` = ' . db_str($sUrlPart) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "NewsItemCategory");
    }

    /**
     * check if urlPart exists excluding given NewsItemCategory
     * @param string $sPartToCheck
     * @param int $iNewsItemCategoryId
     * @return bool
     */
    private static function urlPartExists($sPartToCheck, $iNewsItemCategoryId) {
        $oNewsItemCategory = self::getNewsItemCategoryByUrlPart($sPartToCheck);
        if ($oNewsItemCategory) {
            if ($oNewsItemCategory->newsItemCategoryId != $iNewsItemCategoryId) {
                return true;
            }
        }
        return false;
    }

    /**
     * save NewsItemCategory object
     * @param NewsItemCategory $oNewsItemCategory
     */
    public static function saveNewsItemCategory(NewsItemCategory $oNewsItemCategory) {

        $sGeneratedUrlPart = $oNewsItemCategory->generateUrlPart();
        $iT = 0;
        $sPartToCheck = $sGeneratedUrlPart;

        # while urlPath is not unique, excluding this page, make unique
        while (self::urlPartExists($sPartToCheck, $oNewsItemCategory->newsItemCategoryId)) {
            $iT++;
            $sPartToCheck = $sGeneratedUrlPart . "-$iT";
        }

        # part is last unique part
        $sGeneratedUrlPart = $sPartToCheck;

        # save newsItem item
        $sQuery = ' INSERT INTO `newsItemCategories` (
                        `newsItemCategoryId`,
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
                        ' . db_int($oNewsItemCategory->newsItemCategoryId) . ',
                        ' . db_str($oNewsItemCategory->windowTitle) . ',
                        ' . db_str($oNewsItemCategory->metaKeywords) . ',
                        ' . db_str($oNewsItemCategory->metaDescription) . ',
                        ' . db_str($oNewsItemCategory->name) . ',
                        ' . db_str($sGeneratedUrlPart) . ',
                        ' . db_str($oNewsItemCategory->getUrlPartText()) . ',
                        ' . db_int($oNewsItemCategory->online) . ',
                        ' . db_int($oNewsItemCategory->order) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
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

        if ($oNewsItemCategory->newsItemCategoryId === null)
            $oNewsItemCategory->newsItemCategoryId = $oDb->insert_id;
    }

    /**
     * delete newsItem item and all media
     * @param NewsItemCategory $oNewsItemCategory
     * @return Boolean
     */
    public static function deleteNewsItemCategory(NewsItemCategory $oNewsItemCategory) {
        /* check if newsItem item exists and is deletable */
        if ($oNewsItemCategory->isDeletable()) {
            $sQuery = "DELETE FROM `newsItemCategories` WHERE `newsItemCategoryId` = " . db_int($oNewsItemCategory->newsItemCategoryId) . ";";

            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online by newsItem item id
     * @param int $bOnline
     * @param int $iNewsItemCategoryId
     * @return boolean
     */
    public static function updateOnlineByNewsItemCategoryId($bOnline, $iNewsItemCategoryId) {
        $sQuery = ' UPDATE
                        `newsItemCategories`
                    SET
                        `online` = ' . db_int($bOnline) . '
                    WHERE
                        `newsItemCategoryId` = ' . db_int($iNewsItemCategoryId) . '
                    ;';
        $oDb = DBConnections::get();

        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }

    /**
     * return newsItemCategories filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Module
     */
    public static function getNewsItemCategoriesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`nic`.`order`' => 'ASC', '`nic`.`newsItemCategoryId`' => 'ASC')) {
        $sFrom = '';
        $sWhere = '';

        if (empty($aFilter['showAll'])) {
            $sFrom .= 'JOIN `newsItemCategoriesNewsItems` AS `nicni` USING(`newsItemCategoryId`)';
            $sFrom .= 'JOIN `newsItems` AS `ni` USING(`newsItemId`)';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`nic`.`online` = 1';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '
                            `ni`.`online` = 1
                        AND
                            `ni`.`onlineFrom` <= NOW()
                        AND
                            (`ni`.`onlineTo` >= NOW() OR `ni`.`onlineTo` IS NULL)
                        ';
        }

        # get newsitemcategories that changed last hour
        if (isset($aFilter['lastHourOnly'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . 'IFNULL(`nic`.`modified`, `nic`.`created`) > DATE_ADD(NOW(), INTERVAL -1 HOUR)';
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
                        `nic`.*
                    FROM
                        `newsItemCategories` AS `nic`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    GROUP BY
                        `nic`.`newsItemCategoryId`
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aNewsItemCategories = $oDb->query($sQuery, QRY_OBJECT, "NewsItemCategory");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aNewsItemCategories;
    }

    /**
     * get newsItems for category by filter
     * @param int $iNewsItemCategoryId
     * @param array $aFilter
     * @param int $iLimit
     * @return array NewsItem
     */
    public static function getNewsItemsByFilter($iNewsItemCategoryId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= 'AND
                            `ni`.`online` = 1
                        AND
                            `ni`.`onlineFrom` <= NOW()
                        AND
                            (`ni`.`onlineTo` >= NOW() OR `ni`.`onlineTo` IS NULL)
                        ';
        }

        $sQuery = ' SELECT
                        `ni`.*
                    FROM
                        `newsItems` AS `ni`
                    JOIN
                        `newsItemCategoriesNewsItems` AS `nicni` USING (`newsItemId`)
                    WHERE
                        `nicni`.`newsItemCategoryId` = ' . db_int($iNewsItemCategoryId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `ni`.`date` DESC, `ni`.`newsItemId` DESC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'NewsItem');
    }

}

?>