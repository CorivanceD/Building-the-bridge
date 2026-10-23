<?php

class RssFeedManager {

    /**
     * Get a rss feed by id
     * 
     * @param int $iRssItemId
     * @return RssFeed
     */
    public static function getRssFeedById($iRssItemId) {
        $sQuery = ' SELECT `rssFeeds`.*
                    FROM `rssFeeds`
                    WHERE `rssFeeds`.`rssFeedId` = ' . db_int($iRssItemId) . ';';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'RssFeed');
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
    public static function getRssFeedsByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`title`' => 'ASC')) {
        $sWhere = '';
        $sJoin = '';
        
        if (!empty($aFilter['pageId'])) {
            $sJoin = ' JOIN `pagesRssFeeds` USING (`rssFeedId`) ';
            $sWhere = ' `pagesRssFeeds`.`pageId` = ' . db_int($aFilter['pageId']) . ' ';                        
        }
        
        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '')
                    . '(`rssFeeds`.`title` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeeds`.`link` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeeds`.`description` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ' OR `rssFeeds`.`category` LIKE ' . db_str('%' . $aFilter['q'] . '%')
                    . ')';
        }

        if (!empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`rssFeeds`.`online` = 1';                      
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
                        `rssFeeds`.*'.(empty($aFilter['pageId']) ? '' : ', `pagesRssFeeds`.`filter`,  `pagesRssFeeds`.`logicOperator`, `pagesRssFeeds`.`negative`').'
                    FROM
                        `rssFeeds`
                    ' . $sJoin . '
                    ' . ($sWhere != '' ? ' WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aRssFeeds = $oDb->query($sQuery, QRY_OBJECT, "RssFeed");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aRssFeeds;
    }

    /**
     * 
     * @param RssFeed $oRssFeed
     */
    public static function saveRssFeed($oRssFeed) {
        $sQuery = ' INSERT INTO `rssFeeds` (
                        `rssFeedId`,
                        `online`,
                        `title`,
                        `link`,
                        `description`,
                        `language`,
                        `copyright`,
                        `managingEditor`,
                        `webMaster`,
                        `pubDate`,
                        `lastBuildDate`,
                        `category`,
                        `generator`,
                        `docsUrl`,
                        `ttl`,
                        `imageUrl`,
                        `imageTitle`,
                        `imageLink`,
                        `imageHeight`,
                        `imageWidth`,
                        `skipHours`,
                        `skipDays`,
                        `updateInterval`,
                        `created`)
                    VALUES ('
                . db_int($oRssFeed->rssFeedId) . ', '
                . db_int($oRssFeed->online) . ', '
                . db_str($oRssFeed->title) . ', '
                . db_str($oRssFeed->link) . ', '
                . db_str($oRssFeed->description) . ', '
                . db_str($oRssFeed->language) . ', '
                . db_str($oRssFeed->copyright) . ', '
                . db_str($oRssFeed->managingEditor) . ', '
                . db_str($oRssFeed->webMaster) . ', '
                . db_date($oRssFeed->pubDate) . ', '
                . db_date($oRssFeed->lastBuildDate) . ', '
                . db_str($oRssFeed->category) . ', '
                . db_str($oRssFeed->generator) . ', '
                . db_str($oRssFeed->docsUrl) . ', '
                . db_int($oRssFeed->ttl) . ', '
                . db_str($oRssFeed->imageUrl) . ', '
                . db_str($oRssFeed->imageTitle) . ', '
                . db_str($oRssFeed->imageLink) . ', '
                . db_int($oRssFeed->imageHeight) . ', '
                . db_int($oRssFeed->imageWidth) . ', '
                . db_int($oRssFeed->skipHours) . ', '
                . db_int($oRssFeed->skipDays) . ', '
                . db_int($oRssFeed->updateInterval) . ', '
                . 'NOW()) ON DUPLICATE KEY UPDATE 
                    `title`=VALUES(`title`),
                    `online`=VALUES(`online`),
                    `link`=VALUES(`link`),
                    `description`=VALUES(`description`),
                    `language`=VALUES(`language`),
                    `copyright`=VALUES(`copyright`),
                    `managingEditor`=VALUES(`managingEditor`),
                    `webMaster`=VALUES(`webMaster`),
                    `pubDate`=VALUES(`pubDate`),
                    `lastBuildDate`=VALUES(`lastBuildDate`),
                    `category`=VALUES(`category`),
                    `generator`=VALUES(`generator`),
                    `docsUrl`=VALUES(`docsUrl`),
                    `generator`=VALUES(`generator`),
                    `docsUrl`=VALUES(`docsUrl`),
                    `ttl`=VALUES(`ttl`),
                    `imageUrl`=VALUES(`imageUrl`),
                    `imageTitle`=VALUES(`imageTitle`),
                    `imageLink`=VALUES(`imageLink`),
                    `imageHeight`=VALUES(`imageHeight`),
                    `imageWidth`=VALUES(`imageWidth`),
                    `skipHours`=VALUES(`skipHours`),
                    `skipDays`=VALUES(`skipDays`),
                    `updateInterval`=VALUES(`updateInterval`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
       
        if (empty($oRssFeed->rssFeedId)) {
            $oRssFeed->rssFeedId = $oDb->insert_id;
        }
    }

    /**
     * 
     * @param int $iRssFeedId
     */
    public static function deleteRssFeed($iRssFeedId) {
        $sQuery = "DELETE FROM `rssFeeds` WHERE `rssFeedId` = " . db_int($iRssFeedId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }

    /**
     * Gets the Content of a Rss feed a stores it into the db
     * 
     * @param RssFeed $oRssFeed
     * @return boolean
     */
    public static function loadRssFeed($oRssFeed) {
                        
        if (empty($oRssFeed->link)) {
            return false;
        } else {
            try {
                $sFeedContent = file_get_contents($oRssFeed->link);
                
                if (substr_count($sFeedcontent, "under construction")>0) {
                    return false;
                }
                
                $oXml = new SimpleXMLElement($sFeedContent, LIBXML_NOCDATA);
            } catch (Exception $ex) {
                return false;
            }

            ## This is only to check if its NewsNow.co.uk feed using the XML namespaces
            $bNewsNowFeed = false;
            $aNamespaces = $oXml->getNamespaces(true);
            
            foreach ($aNamespaces AS $sNamespace => $sNamespaceUrl) {
                if ($sNamespaceUrl === 'http://www.NewsNow.co.uk/rss/elements/1.0.xml') {
                    $bNewsNowFeed = true;
                    break;
                }
            }
            
            ## first delete the old news items
            foreach ($oXml->channel->item AS $oXmlItem) {
                $aLinks[] = db_str($oXmlItem->link);
            }

            $sLinks = '(' . implode(', ', $aLinks) . ')';

            $sQuery = 'DELETE FROM `rssFeedItems` WHERE `rssFeedId` = ' . db_int($oRssFeed->rssFeedId) . ' AND `link` NOT IN ' . $sLinks;
            $oDb = DBconnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            $oRssFeed->title = empty($oRssFeed->title) ? isset($oXml->channel->title) ? $oXml->channel->title : null : $oRssFeed->title;
            $oRssFeed->description = empty($oRssFeed->description) ? (isset($oXml->channel->description) ? $oXml->channel->description : null) : $oRssFeed->description;
            $oRssFeed->language = isset($oXml->channel->language) ? $oXml->channel->language : null;
            $oRssFeed->copyright = isset($oXml->channel->copyright) ? $oXml->channel->copyright : null;
            $oRssFeed->managingEditor = isset($oXml->channel->managingEditor) ? $oXml->channel->managingEditor : null;
            $oRssFeed->webMaster = isset($oXml->channel->webMaster) ? $oXml->channel->webMaster : null;
            $oRssFeed->lastBuildDate = isset($oXml->channel->lastBuildDate) ? new Date(strtotime($oXml->channel->lastBuildDate)) : null;
            $oRssFeed->lastBuildDate = empty($oRssFeed->lastBuildDate) ? null : $oRssFeed->lastBuildDate->format('%Y-%m-%d %H:%M');
            $oRssFeed->pubDate = isset($oXml->channel->pubDate) ? new Date(strtotime($oXml->channel->pubDate)) : null;
            $oRssFeed->pubDate = empty($oRssFeed->pubDate) ? $oRssFeed->lastBuildDate : $oRssFeed->pubDate->format('%Y-%m-%d %H:%M');


            foreach ($oXml->channel->category AS $oCategory) {
                empty($oRssFeed->category) ? $oRssFeed->category = (string) $oCategory : $oRssFeed->category .= ',' . (string) $oCategory;
            }

            $oRssFeed->generator = isset($oXml->channel->generator) ? $oXml->channel->generator : null;
            $oRssFeed->docsUrls = isset($oXml->channel->docs) ? $oXml->channel->docs : null;
            $oRssFeed->ttl = isset($oXml->channel->ttl) ? intval($oXml->channel->ttl) : null;
            $oRssFeed->imageUrl = isset($oXml->channel->image->url) ? $oXml->channel->image->url : null;
            $oRssFeed->imageTitle = isset($oXml->channel->image->title) ? $oXml->channel->image->title : null;
            $oRssFeed->imageLink = isset($oXml->channel->image->link) ? $oXml->channel->image->link : null;
            $oRssFeed->imageWidth = empty($oXml->channel->image->width) ? RssFeed::DEFAULT_IMAGE_WIDTH : intval($oXml->channel->image->width);
            $oRssFeed->imageHeight = isset($oXml->channel->image->height) ? intval($oXml->channel->image->height) : null;
            $oRssFeed->skipHours = isset($oXml->channel->skipHours) ? intval($oXml->channel->skipHours) : null;
            $oRssFeed->skipDays = isset($oXml->channel->skipHours) ? intval($oXml->channel->skipDays) : null;            

            if ($oRssFeed->isValid()) {
                self::saveRssFeed($oRssFeed);

                foreach ($oXml->channel->item AS $oXmlItem) {
                    if (isset($oXmlItem->link)) {
                        $oRssFeedItem = RssFeedItemManager::getRssFeedItemByLink($oXmlItem->link);

                        if (empty($oRssFeedItem)) {
                            $oRssFeedItem = new RssFeedItem();
                            $oRssFeedItem->link = $oXmlItem->link;
                        }

                        $oRssFeedItem->rssFeedId = $oRssFeed->rssFeedId;
                        $oRssFeedItem->title = isset($oXmlItem->title) ? $oXmlItem->title : null;
                        $oRssFeedItem->description = isset($oXmlItem->description) ? str_replace('1&para;', '', str_replace('h&para;', '', $oXmlItem->description)) : null;
                        $oRssFeedItem->author = isset($oXmlItem->author) ? $oXmlItem->author : null;

                        foreach ($oXmlItem->category AS $oCategory) {
                            empty($oRssFeedItem->category) ? $oRssFeedItem->category = (string) $oCategory : $oRssFeedItem->category .= ',' . (string) $oCategory;
                        }

                        $oRssFeedItem->commentsUrl = isset($oXmlItem->comments) ? $oXmlItem->comments : null;
                        $oRssFeedItem->enclosureUrl = isset($oXmlItem->enclosure) ? (isset($oXmlItem->enclosure->attributes()->url) ? $oXmlItem->enclosure->attributes()->url : null) : null;
                        $oRssFeedItem->enclosureLength = isset($oXmlItem->enclosure) ? (isset($oXmlItem->enclosure->attributes()->length) ? intval($oXmlItem->enclosure->attributes()->length) : 0) : 0;
                        $oRssFeedItem->enclosureType = isset($oXmlItem->enclosure) ? (isset($oXmlItem->enclosure->attributes()->type) ? $oXmlItem->enclosure->attributes()->type : null) : null;
                        $oRssFeedItem->guidUrl = isset($oXmlItem->guid) ? $oXmlItem->guid : null;
                        $oRssFeedItem->guidIsPermaLink = isset($oXmlItem->guid) ? (isset($oXmlItem->guid->attributes()->isPermaLink) ? ($oXmlItem->guid->attributes()->isPermaLink === 'false' ? 1: 0) : 0) : 0;
                        $oRssFeedItem->pubDate = isset($oXmlItem->pubDate) ? new Date(strtotime($oXmlItem->pubDate)) : null;
                        $oRssFeedItem->pubDate = empty($oRssFeedItem->pubDate) ? null : $oRssFeedItem->pubDate->format('%Y-%m-%d %H:%M');
                        $oRssFeedItem->sourceUrl = isset($oXmlItem->source) ? $oXmlItem->source->attributes()->url : null;
                        $oRssFeedItem->sourceTitle = isset($oXmlItem->source) ? $oXmlItem->source : null;
                        
                        
                        ## if it's a NewsNow feed we can fetch the source from the nn namespace
                        
                        if ($bNewsNowFeed) {
                            $oNewsNowProps = $oXmlItem->children($aNamespaces['nn']);
                            $oRssFeedItem->sourceTitle = $oNewsNowProps->source;
                        }
                        

                        if ($oRssFeedItem->isValid()) {
                            RssFeedItemManager::saveRssFeedItem($oRssFeedItem);
                        }
                    }
                }
                
                $sQuery = 'UPDATE `rssFeeds` SET `lastUpdate` = NOW(), `updateInterval` = '.$oRssFeed->updateInterval.' WHERE `rssFeedId` = '.db_int($oRssFeed->rssFeedId);
                $oDb->query($sQuery, QRY_NORESULT);
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * 
     * @param int $iPageId
     * @param int $iRssFeedId
     * @param string $sLogicOperator
     * @param boolean $bNegative
     * @param string $sFilter
     */
    public static function savePageRssFeedRelation($iPageId, $iRssFeedId, $sFilter = null, $sLogicOperator = null, $bNegative = null) {
        $sQuery = 'INSERT INTO `pagesRssFeeds` (`pageId`, `rssFeedId`'.(empty($sFilter) ? '' : ', `filter`'). (empty($sLogicOperator) ? '' : ', `logicOperator`').(empty($bNegative) ? '' : ', `negative`').') '
                . 'VALUES ('.db_int($iPageId).', '.db_int($iRssFeedId) . (empty($sFilter) ? '' : ', '. db_str($sFilter)) . (empty($sLogicOperator) ? '' : ', '. db_str($sLogicOperator)) . (empty($bNegative) ? '' : ', '. db_str($bNegative)) .') '
                . 'ON DUPLICATE KEY UPDATE '
                . '`pageId`=VALUES(`pageId`), `rssFeedId`=VALUES(`rssFeedId`)'.(empty($sFilter) ? '' : ', `filter`=VALUES(`filter`)').(empty($sLogicOperator) ? '' : ', `logicOperator`=VALUES(`logicOperator`)').(empty($bNegative) ? '' : ', `negative`=VALUES(`negative`)');
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * 
     * @param int $iPageId
     * @param int $iRssFeedId
     * @return boolean
     */
    public static function existsPageRssFeedRelation($iPageId, $iRssFeedId) {
        $sQuery = 'SELECT COUNT(*) AS `exists` FROM `pagesRssFeeds` WHERE `pageId` = ' . db_int($iPageId) . ' AND `rssFeedId` = ' . db_int($iRssFeedId);
        $oDb = DBconnections::get();
        $oResult = $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'stdClass');
        return intval($oResult->exists) > 0;
    }
    
    /**
     * 
     * @param int $iPageId
     */
    public static function deletePageRssFeedRelations($iPageId) {
        $sQuery = 'DELETE FROM `pagesRssFeeds` WHERE `pageId` = '.db_int($iPageId);
        $oDb = DBconnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
}
?>