<?php

/* Models and managers used by the Page model */
require_once 'Page.class.php';
require_once 'Image.class.php';

class PageManager {

    /**
     * get the full page object by id
     * @param int $iPageId
     * @return Page
     */
    public static function getPageById($iPageId) {
        $sQuery = ' SELECT
                        `p`.*
                    FROM
                        `pages` AS `p`
                    WHERE
                        `p`.`pageId` = ' . db_int($iPageId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Page");
    }

    /**
     * get the controller by searching for url part
     * @param string $sUrlPath relative path of the url
     * @return string
     */
    public static function getControllerPathByUrlPath($sUrlPath) {
        $sQuery = ' SELECT
                        `p`.`controllerPath`
                    FROM
                        `pages` AS `p`
                    WHERE
                        `p`.`urlPath` = ' . db_str($sUrlPath) . '
                    AND
                        `p`.`online` = 1
                    ;';

        $oDb = DBConnections::get();
        $oPage = $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Page");
        if ($oPage)
            return $oPage->getControllerPath();
        return null;
    }

    /**
     * get the full page object by searching for url path
     * @param string $sUrlPath relative path of the url to search for
     * @param bool $bCheckOnline only get online page?
     * @return Page
     */
    public static function getPageByUrlPath($sUrlPath, $bCheckOnline = true) {
        $sQuery = ' SELECT
                        `p`.*
                    FROM
                        `pages` AS `p`
                    WHERE
                        `p`.`urlPath` = ' . db_str($sUrlPath) . '
                    ' . ($bCheckOnline ? 'AND `p`.`online` = 1' : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Page");
    }


    /**
     * get redirectUrlPath by urlPath
     * @param string $sUrlPath relative path of the url to search for
     * @return object
     */
    public static function getRedirectUrlPathByUrlPath($sUrlPath) {
        $sQuery = ' SELECT
                        `p`.*
                    FROM
                        `pages` AS `p`
                    JOIN
                        `pageRedirects` AS `pr` USING(`pageId`)
                    WHERE
                        `pr`.`urlPath` = ' . db_str($sUrlPath) . '
                    ORDER BY
                        `pr`.`created` DESC
                    ;';

        $oDb = DBConnections::get();
        $aPages = $oDb->query($sQuery, QRY_OBJECT, "Page");
        if (count($aPages) > 0)
            return $aPages[0]->getUrlPath();
        return null;
    }

    /**
     * check if urlPath exists excluding given page
     * @param string $sPathToCheck
     * @param int $iPageId
     * @return boolean
     */
    private static function urlPathExists($sPathToCheck, $iPageId) {
        $oPage = self::getPageByUrlPath($sPathToCheck, false);
        if ($oPage) {
            if ($oPage->pageId != $iPageId) {
                return true;
            }
        }
        return false;
    }


    /**
     * save Page object
     * @param Page $oPage
     */
    public static function savePage(Page $oPage) {

        $bSaveSubs = false; //do not save all subs
        if (!$oPage->getLockUrlPath() || $oPage->pageId === null) {
            # get 'new' pageUrlPath
            $sGeneratedUrlPath = $oPage->generateUrlPath();

            # set path to check to generatedPath
            $sPathToCheck = $sGeneratedUrlPath;
            $iT = 0;

            # while urlPath is not unique, excluding this page, make unique
            while (self::urlPathExists($sPathToCheck, $oPage->pageId)) {
                $iT++;
                $sPathToCheck = $sGeneratedUrlPath . "-$iT";
            }

            # path is last unique path
            $sGeneratedUrlPath = $sPathToCheck;

            # check 'new' url path with existing
            if ($oPage->pageId !== null && $oPage->getUrlPath(false) != $sGeneratedUrlPath) {
                self::savePageRedirect($oPage->pageId, $oPage->getUrlPath(false)); //save redirect record
                $bSaveSubs = true; //do save all subs after saving parent
            }

            $oPage->setUrlPath(); // generate path en set in object
        } else {
            // $sGeneratedUrlPath is `old` path (may not change with title, take current)
            $sGeneratedUrlPath = $oPage->getUrlPath(false);
        }

        $oPage->setLevel(); // recalculate level to page to be sure

        $sQuery = ' INSERT INTO `pages` (
                        `pageId`,
                        `windowTitle`,
                        `metaKeywords`,
                        `metaDescription`,
                        `title`,
                        `content`,
                        `shortTitle`,
                        `urlPath`,
                        `urlPart`,
                        `urlParameters`,
                        `controllerPath`,
                        `parentPageId`,
                        `online`,
                        `order`,
                        `onlineChangeable`,
                        `editable`,
                        `deletable`,
                        `inMenu`,
                        `level`,
                        `mayHaveSub`,
                        `lockUrlPath`,
                        `lockParent`,
                        `hideImageManagement`,
                        `hideFileManagement`,
                        `hideLinkManagement`,
                        `hideYoutubeLinkManagement`,
                        `hideRssManagement`,
                        `hasArticles`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oPage->pageId) . ',
                        ' . db_str($oPage->windowTitle) . ',
                        ' . db_str($oPage->metaKeywords) . ',
                        ' . db_str($oPage->metaDescription) . ',
                        ' . db_str($oPage->title) . ',
                        ' . db_str($oPage->content) . ',
                        ' . db_str($oPage->shortTitle) . ',
                        ' . db_str($sGeneratedUrlPath) . ',
                        ' . db_str($oPage->getUrlPart()) . ',
                        ' . db_str($oPage->getUrlParameters()) . ',
                        ' . db_str($oPage->getControllerPath()) . ',
                        ' . db_str($oPage->parentPageId) . ',
                        ' . db_int($oPage->online) . ',
                        ' . db_int($oPage->order) . ',
                        ' . db_int($oPage->getOnlineChangeable()) . ',
                        ' . db_int($oPage->getEditable()) . ',
                        ' . db_int($oPage->getDeletable()) . ',
                        ' . db_int($oPage->getInMenu()) . ',
                        ' . db_int($oPage->level) . ',
                        ' . db_int($oPage->getMayHaveSub()) . ',
                        ' . db_int($oPage->getLockUrlPath()) . ',
                        ' . db_int($oPage->getLockParent()) . ',
                        ' . db_int($oPage->getHideImageManagement()) . ',
                        ' . db_int($oPage->getHideFileManagement()) . ',
                        ' . db_int($oPage->getHideLinkManagement()) . ',
                        ' . db_int($oPage->getHideYoutubeLinkManagement()) . ',
                        ' . db_int($oPage->getHideRssManagement()) . ',
                        ' . db_int($oPage->hasArticles) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `windowTitle`=VALUES(`windowTitle`),
                        `metaKeywords`=VALUES(`metaKeywords`),
                        `metaDescription`=VALUES(`metaDescription`),
                        `title`=VALUES(`title`),
                        `content`=VALUES(`content`),
                        `shortTitle`=VALUES(`shortTitle`),
                        `urlPath`=VALUES(`urlPath`),
                        `urlPart`=VALUES(`urlPart`),
                        `urlParameters`=VALUES(`urlParameters`),
                        `controllerPath`=VALUES(`controllerPath`),
                        `parentPageId`=VALUES(`parentPageId`),
                        `online`=VALUES(`online`),
                        `order`=VALUES(`order`),
                        `onlineChangeable`=VALUES(`onlineChangeable`),
                        `editable`=VALUES(`editable`),
                        `deletable`=VALUES(`deletable`),
                        `inMenu`=VALUES(`inMenu`),
                        `level`=VALUES(`level`),
                        `mayHaveSub`=VALUES(`mayHaveSub`),
                        `lockUrlPath`=VALUES(`lockUrlPath`),
                        `lockParent`=VALUES(`lockParent`),
                        `hideImageManagement`=VALUES(`hideImageManagement`),
                        `hideFileManagement`=VALUES(`hideFileManagement`),
                        `hideLinkManagement`=VALUES(`hideLinkManagement`),
                        `hideYoutubeLinkManagement`=VALUES(`hideYoutubeLinkManagement`),
                        `hideRssManagement`=VALUES(`hideRssManagement`),
                        `hasArticles`=VALUES(`hasArticles`)
                    ;';

        $oDb = new DBConnection();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oPage->pageId === null)
            $oPage->pageId = $oDb->insert_id;

        if ($bSaveSubs && $oPage->hasSubPages()) {
            # also save all subs and their subs etc
            foreach ($oPage->getSubPages() AS $oSubPage) {
                self::savePage($oSubPage);
            }
        }
    }

    /**
     * save page redirect
     * @param int $iPageId
     * @param string $sUrlPath
     */
    public static function savePageRedirect($iPageId, $sUrlPath) {
        $sQuery = ' INSERT INTO `pageRedirects` (
                        `pageId`,
                        `urlPath`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($iPageId) . ',
                        ' . db_str($sUrlPath) . ',
                        ' . 'NOW()' . '
                    )
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * delete page and all media
     * @param Page $oPage
     * @return boolean
     */
    public static function deletePage(Page $oPage) {

        $oDb = DBConnections::get();

        /* check if page exists and is deletable */
        if ($oPage->isDeletable()) {

            # get and delete images
            foreach ($oPage->getImages('all') AS $oImage) {
                ImageManager::deleteImage($oImage);
            }

            # get and delete files
            foreach ($oPage->getFiles('all') AS $oFile) {
                FileManager::deleteFile($oFile);
            }

            # get and delete links
            foreach ($oPage->getLinks('all') AS $oLink) {
                LinkManager::deleteLink($oLink);
            }

            # get and delete youtube links
            foreach ($oPage->getYoutubeLinks('all') AS $oYoutubeLink) {
                YoutubeLinkManager::deleteYoutubeLink($oYoutubeLink);
            }

            $sQuery = "DELETE FROM `pages` WHERE `pageId` = " . db_int($oPage->pageId) . ";";
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online by page id
     * @param int $bOnline
     * @param Page $oPage
     * @return boolean
     */
    public static function updateOnlineByPage($bOnline, Page $oPage) {
        if ($oPage->isOnlineChangeable()) {
            $sQuery = ' UPDATE
                            `pages`
                        SET
                            `online` = ' . db_int($bOnline) . '
                        WHERE
                            `pageId` = ' . db_int($oPage->pageId) . '
                        ;';
            $oDb = DBConnections::get();

            $oDb->query($sQuery, QRY_NORESULT);

            # check if somethinf happened
            return $oDb->affected_rows > 0;
        } else {
            return false;
        }
    }

    /**
     * return pages filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Page
     */
    public static function getPagesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`p`.`order`' => 'ASC', '`p`.`pageId`' => 'ASC')) {

        $sWhere = '';
        $sJoin = '';
        
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`online` = 1';
            
            if (empty($aFilter['hasArticles'])) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`inMenu` = 1';
            }
        }

        // get pages with specific level
        if (!empty($aFilter['level'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`level` = ' . db_int($aFilter['level']);
        }

        // get pages with parenPageId
        if (!empty($aFilter['parentPageId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`parentPageId` = ' . db_int($aFilter['parentPageId']);
        }

        // get pages with inMenu
        if (isset($aFilter['inMenu'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`inMenu` = ' . db_int($aFilter['inMenu']);
        }

        // get pages with online
        if (isset($aFilter['online'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`online` = ' . db_int($aFilter['online']);
        }

        // get pages with that changed last hour
        if (isset($aFilter['lastHourOnly'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . 'IFNULL(`p`.`modified`, `p`.`created`) > DATE_ADD(NOW(), INTERVAL -1 HOUR)';
        }
        
        // get pages with online
        if (isset($aFilter['hasArticles'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`p`.`hasArticles` = 1';
        }    
        
        if (isset($aFilter['hasRssFeeds'])) {
            $sJoin = ' JOIN  `pagesRssFeeds` AS `prss` USING(`pageId`) ';            
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

        $sQuery = ' SELECT DISTINCT ' . ($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '') . '
                        `p`.*
                    FROM
                        `pages` AS `p`
                    ' . $sJoin
                      . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aPages = $oDb->query($sQuery, QRY_OBJECT, "Page");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aPages;
    }

    /**
     * save connection between a page and an image
     * @param int $iPageId
     * @param int $iImageId
     */
    public static function savePageImageRelation($iPageId, $iImageId) {
        $sQuery = ' INSERT IGNORE INTO
                        `pagesImages`
                    (
                        `pageId`,
                        `imageId`
                    )
                    VALUES
                    (
                        ' . db_int($iPageId) . ',
                        ' . db_int($iImageId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get images for page by filter
     * @param int $iPageId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Image
     */
    public static function getImagesByFilter($iPageId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `i`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `i`.*
                    FROM
                        `images` AS `i`
                    JOIN
                        `pagesImages` AS `pi` USING (`imageId`)
                    WHERE
                        `pi`.`pageId` = ' . db_int($iPageId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `i`.`order` ASC, `i`.`imageId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Image');
    }

    /**
     * save connection between a page and a file
     * @param int $iPageId
     * @param int $iMediaId
     */
    public static function savePageFileRelation($iPageId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `pagesFiles`
                    (
                        `pageId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iPageId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get files for page by filter
     * @param int $iPageId
     * @param array $aFilter
     * @param int $iLimit
     * @return array File
     */
    public static function getFilesByFilter($iPageId, array $aFilter = array(), $iLimit = null) {

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
                        `pagesFiles` AS `pf` USING (`mediaId`)
                    JOIN
                        `media` AS `m` USING (`mediaId`)
                    WHERE
                        `pf`.`pageId` = ' . db_int($iPageId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'File');
    }

    /**
     * save connection between a page and a link
     * @param int $iPageId
     * @param int $iMediaId
     */
    public static function savePageLinkRelation($iPageId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `pagesLinks`
                    (
                        `pageId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iPageId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get links for page by filter
     * @param int $iPageId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Link
     */
    public static function getLinksByFilter($iPageId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `pagesLinks` AS `pl` USING (`mediaId`)
                    WHERE
                        `pl`.`pageId` = ' . db_int($iPageId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Link');
    }

    /**
     * save connection between a page and a YoutubeLink
     * @param int $iPageId
     * @param int $iMediaId
     */
    public static function savePageYoutubeLinkRelation($iPageId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `pagesYoutubeLinks`
                    (
                        `pageId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iPageId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get youtube links for page by filter
     * @param int $iPageId
     * @param array $aFilter
     * @param int $iLimit
     * @return array YoutubeLink
     */
    public static function getYoutubeLinksByFilter($iPageId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `pagesYoutubeLinks` AS `py` USING (`mediaId`)
                    WHERE
                        `py`.`pageId` = ' . db_int($iPageId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'YoutubeLink');
    }

    /**
     * 
     * @param int $iRssFeedId
     * @return Page
     */
    public static function getPageWithRssFeed($iRssFeedId) {
        $sQuery = ' SELECT `p`.*
                    FROM `pages` AS `p`
                    JOIN `pagesRssFeeds` AS `prssf` USING(`pageId`)
                    WHERE `prssf`.`rssFeedId` = '.db_int($iRssFeedId).
                  ' ORDER BY `p`.`modified`, `p`.`created`
                    LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Page');
    }
}
?>