<?php

/* Models and managers used by the NewsItem model */
require_once 'NewsItem.class.php';
require_once 'Image.class.php';

class NewsItemManager {

    /**
     * get the full NewsItem object by id
     * @param int $iNewsItemId
     * @return NewsItem
     */
    public static function getNewsItemById($iNewsItemId) {
        $sQuery = ' SELECT
                        `ni`.*
                    FROM
                        `newsItems` AS `ni`
                    WHERE
                        `ni`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "NewsItem");
    }

    /**
     * save NewsItem object
     * @param NewsItem $oNewsItem
     */
    public static function saveNewsItem(NewsItem $oNewsItem) {
        # save news item
        $sQuery = ' INSERT INTO `newsItems` (
                        `newsItemId`,
                        `windowTitle`,
                        `metaKeywords`,
                        `metaDescription`,
                        `title`,
                        `intro`,
                        `content`,
                        `shortTitle`,
                        `date`,
                        `source`,
                        `onlineFrom`,
                        `onlineTo`,
                        `online`,
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oNewsItem->newsItemId) . ',
                        ' . db_str($oNewsItem->windowTitle) . ',
                        ' . db_str($oNewsItem->metaKeywords) . ',
                        ' . db_str($oNewsItem->metaDescription) . ',
                        ' . db_str($oNewsItem->title) . ',
                        ' . db_str($oNewsItem->intro) . ',
                        ' . db_str($oNewsItem->content) . ',
                        ' . db_str($oNewsItem->shortTitle) . ',
                        ' . db_date($oNewsItem->date) . ',
                        ' . db_str($oNewsItem->source) . ',
                        ' . db_date($oNewsItem->onlineFrom) . ',
                        ' . db_date($oNewsItem->onlineTo) . ',
                        ' . db_int($oNewsItem->online) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `windowTitle`=VALUES(`windowTitle`),
                        `metaKeywords`=VALUES(`metaKeywords`),
                        `metaDescription`=VALUES(`metaDescription`),
                        `title`=VALUES(`title`),
                        `intro`=VALUES(`intro`),
                        `content`=VALUES(`content`),
                        `shortTitle`=VALUES(`shortTitle`),
                        `date`=VALUES(`date`),
                        `source`=VALUES(`source`),
                        `onlineFrom`=VALUES(`onlineFrom`),
                        `onlineTo`=VALUES(`onlineTo`),
                        `online`=VALUES(`online`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oNewsItem->newsItemId === null)
            $oNewsItem->newsItemId = $oDb->insert_id;

        // save categories
        self::saveNewsItemCategoryRelations($oNewsItem);
    }

    /**
     * save NewsItem's relations with NewsItemCategory objects
     * @param NewsItem $oNewsItem
     */
    private static function saveNewsItemCategoryRelations(NewsItem $oNewsItem) {
        $oDb = DBConnections::get();

        # define the NOT IT and VALUES part of the DELETE and INSERT query
        $sDeleteNotIn = '';
        $sInsertValues = '';
        foreach ($oNewsItem->getCategories('all') as $oNewsItemCategory) {
            $sDeleteNotIn .= ($sDeleteNotIn == '' ? '' : ',') . db_int($oNewsItemCategory->newsItemCategoryId);
            $sInsertValues .= ($sInsertValues == '' ? '' : ', ') . '(' . db_int($oNewsItem->newsItemId) . ', ' . db_int($oNewsItemCategory->newsItemCategoryId) . ')';
        }

        # delete the old objects
        $sQuery = ' DELETE FROM
                        `newsItemCategoriesNewsItems`
                    WHERE
                        `newsItemId` = ' . db_int($oNewsItem->newsItemId) . '
                        ' . ($sDeleteNotIn != '' ? 'AND `newsItemCategoryId` NOT IN (' . $sDeleteNotIn . ')' : '') . '
                    ;';
        $oDb->query($sQuery, QRY_NORESULT);

        if (!empty($sInsertValues)) {
            # save the objects
            $sQuery = ' INSERT IGNORE INTO `newsItemCategoriesNewsItems`(
                        `newsItemId`,
                        `newsItemCategoryId`
                    )
                    VALUES ' . $sInsertValues . '
                    ;';

            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

    /**
     * delete news item and all media
     * @param NewsItem $oNewsItem
     * @return Boolean
     */
    public static function deleteNewsItem(NewsItem $oNewsItem) {
        $oDb = DBConnections::get();

        /* check if news item exists and is deletable */
        if ($oNewsItem->isDeletable()) {

            # get and delete images
            foreach ($oNewsItem->getImages('all') AS $oImage) {
                ImageManager::deleteImage($oImage);
            }

            # get and delete files
            foreach ($oNewsItem->getFiles('all') AS $oFile) {
                FileManager::deleteFile($oFile);
            }

            # get and delete links
            foreach ($oNewsItem->getLinks('all') AS $oLink) {
                LinkManager::deleteLink($oLink);
            }

            # get and delete youtube links
            foreach ($oNewsItem->getYoutubeLinks('all') AS $oYoutubeLink) {
                YoutubeLinkManager::deleteYoutubeLink($oYoutubeLink);
            }

            $sQuery = "DELETE FROM `newsItems` WHERE `newsItemId` = " . db_int($oNewsItem->newsItemId) . ";";
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online by news item id
     * @param int $bOnline
     * @param int $iNewsItemId
     * @return boolean
     */
    public static function updateOnlineByNewsItemId($bOnline, $iNewsItemId) {
        $sQuery = ' UPDATE
                        `newsItems`
                    SET
                        `online` = ' . db_int($bOnline) . '
                    WHERE
                        `newsItemId` = ' . db_int($iNewsItemId) . '
                    ;';
        $oDb = DBConnections::get();

        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }

    /**
     * return news items filtered by a few options
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array NewsItem 
     */
    public static function getNewsItemsByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`ni`.`date`' => 'DESC', '`ni`.`newsItemId`' => 'DESC')) {
        $sFrom = '';
        $sWhere = '';
        $sGroupBy = '';

        if (class_exists('NewsItemCategoryManager')) {
            // with news categories so join
            $sFrom .= 'LEFT JOIN `newsItemCategoriesNewsItems` AS `nicni` ON `nicni`.`newsItemId` = `ni`.`newsItemId`';
            $sGroupBy .= '`ni`.`newsItemId`'; // when join, also group by
            // check for categoryId in filter
            if (!empty($aFilter['newsItemCategoryId'])) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`nicni`.`newsItemCategoryId` = ' . db_int($aFilter['newsItemCategoryId']);
            }
        }

        // no show all? only show online items
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '
                            `ni`.`online` = 1
                        AND
                            `ni`.`onlineFrom` <= NOW()
                        AND
                            (`ni`.`onlineTo` >= NOW() OR `ni`.`onlineTo` IS NULL)
                        ';

            // check if newsitems have at least 1 online category
            if (class_exists('NewsItemCategoryManager')) {
                $sFrom .= 'JOIN `newsItemCategories` AS `nic` ON `nic`.`newsItemCategoryId` = `nicni`.`newsItemCategoryId`';
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`nic`.`online` = 1';
            }
        }

        # search for q
        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '(`ni`.`title` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ' OR `ni`.`intro` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ' OR `ni`.`content` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ')';
        }


        # get newsitems with that changed last hour
        if (isset($aFilter['lastHourOnly'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . 'IFNULL(`ni`.`modified`, `ni`.`created`) > DATE_ADD(NOW(), INTERVAL -1 HOUR)';
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
                        `ni`.*
                    FROM
                        `newsItems` AS `ni`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aNewsItems = $oDb->query($sQuery, QRY_OBJECT, "NewsItem");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aNewsItems;
    }

    /**
     * save connection between a newsItem and an image
     * @param int $iNewsItemId
     * @param int $iImageId
     */
    public static function saveNewsItemImageRelation($iNewsItemId, $iImageId) {
        $sQuery = ' INSERT IGNORE INTO
                        `newsItemsImages`
                    (
                        `newsItemId`,
                        `imageId`
                    )
                    VALUES
                    (
                        ' . db_int($iNewsItemId) . ',
                        ' . db_int($iImageId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get images for newsItem by filter
     * @param int $iNewsItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Image
     */
    public static function getImagesByFilter($iNewsItemId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `i`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `i`.*
                    FROM
                        `images` AS `i`
                    JOIN
                        `newsItemsImages` AS `pi` USING (`imageId`)
                    WHERE
                        `pi`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `i`.`order` ASC, `i`.`imageId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Image');
    }

    /**
     * save connection between a newsItem and a file
     * @param int $iNewsItemId
     * @param int $iMediaId
     */
    public static function saveNewsItemFileRelation($iNewsItemId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `newsItemsFiles`
                    (
                        `newsItemId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iNewsItemId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get files for newsItem by filter
     * @param int $iNewsItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array File
     */
    public static function getFilesByFilter($iNewsItemId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*,
                        `f`.*
                    FROM
                        `files` AS `f`
                    JOIN
                        `newsItemsFiles` AS `pf` USING (`mediaId`)
                    JOIN
                        `media` AS `m` USING (`mediaId`)
                    WHERE
                        `pf`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'File');
    }

    /**
     * save connection between a newsItem and a link
     * @param int $iNewsItemId
     * @param int $iMediaId
     */
    public static function saveNewsItemLinkRelation($iNewsItemId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `newsItemsLinks`
                    (
                        `newsItemId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iNewsItemId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get links for newsItem by filter
     * @param int $iNewsItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Link
     */
    public static function getLinksByFilter($iNewsItemId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `newsItemsLinks` AS `pl` USING (`mediaId`)
                    WHERE
                        `pl`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Link');
    }

    /**
     * save connection between a newsItem and a YoutubeLink
     * @param int $iNewsItemId
     * @param int $iMediaId
     */
    public static function saveNewsItemYoutubeLinkRelation($iNewsItemId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `newsItemsYoutubeLinks`
                    (
                        `newsItemId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iNewsItemId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get youtube links for newsItem by filter
     * @param int $iNewsItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array YoutubeLink
     */
    public static function getYoutubeLinksByFilter($iNewsItemId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `newsItemsYoutubeLinks` AS `py` USING (`mediaId`)
                    WHERE
                        `py`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'YoutubeLink');
    }

    /**
     * get categories for newsItem by filter
     * @param int $iNewsItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array NewsItemCategory
     */
    public static function getCategoriesByFilter($iNewsItemId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `nic`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `nic`.*
                    FROM
                        `newsItemCategories` AS `nic`
                    JOIN
                        `newsItemCategoriesNewsItems` AS `nicni` USING (`newsItemCategoryId`)
                    WHERE
                        `nicni`.`newsItemId` = ' . db_int($iNewsItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `nic`.`order` ASC, `nic`.`newsItemCategoryId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'NewsItemCategory');
    }

}

?>
