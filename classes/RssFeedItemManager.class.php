<?php

class RssFeedItemManager {

    /**
     * Get a rss feed item by id
     * 
     * @param int $iRssFeedItemId
     * @return RssFeedItem     
     */
    public static function getRssFeedItemById($iRssFeedItemId) {
        $sQuery = 'SELECT `rssFeedItems`.* FROM `rssFeedItems` WHERE `rssFeedItems`.`rssFeedItemId` = ' . db_int($iRssFeedItemId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'RssFeedItem');
    }

    /**
     * Get a rss feed item by link
     * 
     * @param string $sLink
     * @return RssFeedItem     
     */
    public static function getRssFeedItemByLink($sLink) {
        $sQuery = 'SELECT `rssFeedItems`.* FROM `rssFeedItems` WHERE `rssFeedItems`.`link` = ' . db_str($sLink) . ' LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'RssFeedItem');
    }

    /**
     * 
     * @param array $aFilter
     * @param int $iLimit
     * @param int $iStart
     * @param int $iFoundRows
     * @param array $aOrderBy
     * @return array RssFeed
     */
    public static function getRssFeedItemsByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`rssFeedItems`.`pubDate`' => 'DESC')) {
        $sWhere = '';

        if (!empty($aFilter['rssFeedId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`rssFeedItems`.`rssFeedId` = ' . db_int($aFilter['rssFeedId']);
        }

        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '')
                    . '(`rssFeedItems`.`title` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeedItems`.`link` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeedItems`.`description` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeedItems`.`category` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeedItems`.`author` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ')';
        }

        if (!empty($aFilter['keywords'])) {           
            $sWhere .= ($sWhere != '' ? ' AND ' . (empty($aFilter['negative']) ? '' : ' NOT ') . ' (' : (empty($aFilter['negative']) ? '' : ' NOT ') . ' (');
            
            for ($i = 0; $i < count($aFilter['keywords']); $i++) {
                if (!empty($aFilter['keywords'][$i])) {
                    $sWhere .= 'LOWER(`rssFeedItems`.`description`) RLIKE ' . db_str('[[:<:]]'.trim(strtolower($aFilter['keywords'][$i])).'[[:>:]]'). '';
                
                    if (($i + 1) < count($aFilter['keywords'])) {
                        $sWhere .= empty($aFilter['logicOperator']) ? ' AND ' : ' ' . $aFilter['logicOperator'] . ' ';
                    }
                }
            }            

            $sWhere .= ')';
        }

        if (!empty($aFilter['fromDate'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`rssFeedItems`.`pubDate` >= ' . db_date($aFilter['fromDate']);
        }

        if (!empty($aFilter['toDate'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`rssFeedItems`.`pubDate` <= ' . db_date($aFilter['toDate']);
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
                        `rssFeedItems`.*
                    FROM `rssFeedItems`'
                . ($sWhere != '' ? ' WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aRssFeeds = $oDb->query($sQuery, QRY_OBJECT, "RssFeedItem");

        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
 
        
        return $aRssFeeds;
    }

    /**
     * Saves a Rss item into the db
     * 
     * @param RssFeedItem $oRssFeedItem
     */
    public static function saveRssFeedItem($oRssFeedItem) {
        $sQuery = ' INSERT INTO `rssFeedItems` (
                        `rssFeedItemId`,
                        `rssFeedId`,
                        `title`,
                        `link`,
                        `description`,
                        `author`,
                        `category`,
                        `commentsUrl`,
                        `enclosureUrl`,
                        `enclosureLength`,
                        `enclosureType`,
                        `guidUrl`,
                        `guidIsPermalink`,
                        `pubDate`,
                        `sourceUrl`,
                        `sourceTitle`,
                        `created`)
                    VALUES ('
                . db_int($oRssFeedItem->rssFeedItemId) . ', '
                . db_int($oRssFeedItem->rssFeedId) . ', '
                . db_str($oRssFeedItem->title) . ', '
                . db_str($oRssFeedItem->link) . ', '
                . db_str($oRssFeedItem->description) . ', '
                . db_str($oRssFeedItem->author) . ', '
                . db_str($oRssFeedItem->category) . ', '
                . db_str($oRssFeedItem->commentsUrl) . ', '
                . db_str($oRssFeedItem->enclosureUrl) . ', '
                . db_int($oRssFeedItem->enclosureLength) . ', '
                . db_str($oRssFeedItem->enclosureType) . ', '
                . db_str($oRssFeedItem->guidUrl) . ', '
                . db_str($oRssFeedItem->guidIsPermalink) . ', '
                . db_date($oRssFeedItem->pubDate) . ', '
                . db_str($oRssFeedItem->sourceUrl) . ', '
                . db_str($oRssFeedItem->sourceTitle) . ', '
                . 'NOW()) 
                    ON DUPLICATE KEY UPDATE
                        `rssFeedId`=VALUES(`rssFeedId`),
                        `title`=VALUES(`title`),
                        `link`=VALUES(`link`),
                        `description`=VALUES(`description`),
                        `author`=VALUES(`author`),
                        `category`=VALUES(`category`),
                        `commentsUrl`=VALUES(`commentsUrl`),
                        `enclosureUrl`=VALUES(`enclosureUrl`),
                        `enclosureLength`=VALUES(`enclosureLength`),
                        `enclosureType`=VALUES(`enclosureType`),
                        `guidUrl`=VALUES(`guidUrl`),
                        `guidIsPermalink`=VALUES(`guidIsPermalink`),
                        `pubDate`=VALUES(`pubDate`),
                        `sourceUrl`=VALUES(`sourceUrl`),
                        `sourceTitle`=VALUES(`sourceTitle`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if (empty($oRssFeedItem->rssFeedItemId)) {
            $oRssFeedItem->rssFeedItemId = $oDb->insert_id;
        }
    }

    /**
     * Delete a rss feed item by its id
     * 
     * @param int $iRssFeedItemId
     */
    public static function deleteRssFeedItemById($iRssFeedItemId) {
        $sQuery = 'DELETE FROM `rssFeedItem` WHERE `rssFeedItemId` = ' . db_int($iRssFeedItemId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

}

?>