<?php

/* Models and managers used by the Article model */
require_once 'Article.class.php';
require_once 'Image.class.php';

class ArticleManager {

    /**
     * get the full Article object by id
     * @param int $iArticleId
     * @return Article
     */
    public static function getArticleById($iArticleId) {
        $sQuery = ' SELECT
                        `a`.*
                    FROM
                        `articles` AS `a`
                    WHERE
                        `a`.`articleId` = ' . db_int($iArticleId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Article");
    }
    
    /**
     * 
     * @param int $iArticleCommentId
     * @return ArticleComment
     */
    public static function getArticleCommentById($iArticleCommentId) {
        $sQuery = ' SELECT
                        `ac`.*
                    FROM
                        `articleComments` AS `ac`
                    WHERE
                        `ac`.`articleCommentId` = ' . db_int($iArticleCommentId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "ArticleComment");
    }    

    /**
     * save Article object
     * @param Article $oArticle
     */
    public static function saveArticle(Article $oArticle) {
        # save news item
        $sQuery = ' INSERT INTO `articles` (
                        `articleId`,
                        `alias`,
                        `authorEmail`,
                        `parentPageId`,
                        `articleCategoryId`,
                        `windowTitle`,
                        `metaKeywords`,
                        `metaDescription`,
                        `title`,
                        `intro`,
                        `content`,
                        `shortTitle`,
                        `pubDate`,
                        `authorId`,
                        `status`,
                        `isLuminary`,
                        `order`,                        
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oArticle->articleId) . ',
                        ' . db_str($oArticle->alias) . ',
                        ' . db_str($oArticle->authorEmail) . ',
                        ' . db_int($oArticle->parentPageId) . ',                            
                        ' . db_int($oArticle->articleCategoryId) . ',
                        ' . db_str($oArticle->windowTitle) . ',
                        ' . db_str($oArticle->metaKeywords) . ',
                        ' . db_str($oArticle->metaDescription) . ',
                        ' . db_str($oArticle->title) . ',
                        ' . db_str($oArticle->intro) . ',
                        ' . db_str($oArticle->content) . ',
                        ' . db_str($oArticle->shortTitle) . ',
                        ' . db_date($oArticle->pubDate) . ',
                        ' . db_int($oArticle->authorId) . ',
                        ' . db_int($oArticle->status) . ',
                        ' . db_int($oArticle->isLuminary) . ',
                        ' . db_int($oArticle->order) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `alias`=VALUES(`alias`),
                        `authorEmail`=VALUES(`authorEmail`),
                        `parentPageId`=VALUES(`parentPageId`),
                        `articleCategoryId`=VALUES(`articleCategoryId`),
                        `windowTitle`=VALUES(`windowTitle`),
                        `metaKeywords`=VALUES(`metaKeywords`),
                        `metaDescription`=VALUES(`metaDescription`),
                        `title`=VALUES(`title`),
                        `intro`=VALUES(`intro`),
                        `content`=VALUES(`content`),
                        `shortTitle`=VALUES(`shortTitle`),
                        `pubDate`=VALUES(`pubDate`),
                        `authorId`=VALUES(`authorId`),
                        `status`=VALUES(`status`),
                        `isLuminary`=VALUES(`isLuminary`),
                        `order`=VALUES(`order`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if (empty($oArticle->articleId))
            $oArticle->articleId = $oDb->insert_id;
    }
    
    /**
     * 
     * @param ArticleComment $oArticleComment
     */
    public static function saveArticleComment($oArticleComment) {
        $sQuery = ' INSERT INTO `articleComments` (
                        `articleCommentId`,
                        `authorId`,
                        `authorName`,
                        `authorEmail`,
                        `articleId`,
                        `comment`,
                        `confirmCode`,
                        `postedOn`,
                        `inappropriate`,
                        `online`)
                    VALUES ('                
                        .db_int($oArticleComment->articleCommentId).', '
                        .db_int($oArticleComment->authorId).', '
                        .db_str($oArticleComment->authorName).', '
                        .db_str($oArticleComment->authorEmail).', '
                        .db_int($oArticleComment->articleId).', '
                        .db_str($oArticleComment->comment).', '
                        .db_str($oArticleComment->confirmCode).',
                        NOW(), '
                        .db_int($oArticleComment->inappropriate) .', '
                        .db_int($oArticleComment->online) .'
                    ) ON DUPLICATE KEY UPDATE
                        `authorId`=VALUES(`authorId`),
                        `authorId`=VALUES(`authorId`),
                        `authorName`=VALUES(`authorName`),
                        `authorEmail`=VALUES(`authorEmail`),
                        `articleId`=VALUES(`articleId`),
                        `comment`=VALUES(`comment`),
                        `confirmCode`=VALUES(`confirmCode`),
                        `postedOn`=VALUES(`postedOn`),
                        `inappropriate`=VALUES(`inappropriate`),
                        `online`=VALUES(`online`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        if (empty($oArticleComment->articleCommentId)) {
            $oArticleComment->articleCommentId = $oDb->insert_id;
        }
    }
    
    /**
     * 
     * @param string $sEmail
     * @param string $sConfirmCode
     * @return int
     */
    public static function confirmComment($sEmail, $sConfirmCode) {
        $oDb = DBConnections::get();
        $sQuery = 'SELECT `articleCommentId` FROM `articleComments` WHERE `authorEmail` = '.db_str($sEmail).' AND `confirmCode` = '.db_str($sConfirmCode);
        $oResult = $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'stdClass');
        
        if (empty($oResult)) {
            return null;
        }
        
        $sQuery = 'UPDATE `articleComments`
                   SET `confirmCode` = NULL 
                   WHERE `authorEmail` = '.db_str($sEmail).'
                        AND `confirmCode` = '.db_str($sConfirmCode);
        
        $oDb->query($sQuery, QRY_NORESULT);
        
        return $oResult->articleCommentId;
    }
    
    /**     
     * 
     * @param int $iArticleCommentId
     * @param int $bOnline
     * @return boolean
     */
    public static function updateArticleCommentOnlineStatus($iArticleCommentId, $bOnline) {
        $sQuery = 'UPDATE `articleComments` SET `online` = '.db_int($bOnline).' WHERE `articleCommentId` = '.db_int($iArticleCommentId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
    }

    /**
     * 
     * @param int $iArticleCommentId
     */
    public static function deleteArticleComment($iArticleCommentId) {
        $sQuery = 'DELETE FROM `articleComments` WHERE `articleCommentId` = '.db_int($iArticleCommentId);
        $oDb = DBconnections::get();
        $oDb->query($sQuery, QRY_NORESULT);        
    }
    
    /**
     * delete news item and all media
     * @param Article $oArticle
     * @return Boolean
     */
    public static function deleteArticle(Article $oArticle) {
        $oDb = DBConnections::get();

        /* check if news item exists and is deletable */
        if ($oArticle->isDeletable()) {

            # get and delete images
            foreach ($oArticle->getImages('all') AS $oImage) {
                ImageManager::deleteImage($oImage);
            }

            # get and delete files
            foreach ($oArticle->getFiles('all') AS $oFile) {
                FileManager::deleteFile($oFile);
            }

            # get and delete links
            foreach ($oArticle->getLinks('all') AS $oLink) {
                LinkManager::deleteLink($oLink);
            }

            # get and delete youtube links
            foreach ($oArticle->getYoutubeLinks('all') AS $oYoutubeLink) {
                YoutubeLinkManager::deleteYoutubeLink($oYoutubeLink);
            }

            $sQuery = "DELETE FROM `articles` WHERE `articleId` = " . db_int($oArticle->articleId) . ";";
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online by news item id
     * @param int $iStatus
     * @param int $iArticleId
     * @return boolean
     */
    public static function updateStatusByArticleId($iStatus, $iArticleId) {
        $sQuery = ' UPDATE
                        `articles`
                    SET
                        `status` = ' . db_int($iStatus);
        
        if ($iStatus === Article::ARTICLE_STATUS_PUBLISHED) {
            $sQuery .= ', `pubDate` = NOW()';
        }
        
        $sQuery .=  ' WHERE `articleId` = ' . db_int($iArticleId);
        
        $oDb = DBConnections::get();

        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }

    /**
     * return articles filtered by a few options
     * 
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Article 
     */
    public static function getArticlesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC')) {
        $sFrom = '';
        $sJoin = '';
        $sWhere = ''; 
        $sGroupBy = '';

        // check for categoryId in filter
        if (!empty($aFilter['articleCategoryId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleCategoryId` = ' . db_int($aFilter['articleCategoryId']);
        }
                
        
        if (!empty($aFilter['onlyLetters'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleCategoryId` = ' . db_int(ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY);
        }         
        
        // check for categoryId in filter
        if (!empty($aFilter['onlyLuminaries'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`isLuminary` = 1';
        }        

        // check for status
        if (!empty($aFilter['status'])) {
            
            if (is_array($aFilter['status'])) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`status` IN ('.implode(', ', $aFilter['status']) . ')';
            }
            else {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`status` = '. db_int($aFilter['status']);
            }
        }
             
        
        // get info about articles included in a communication item              
        if (!empty($aFilter['communicationItemId'])) {
            
            if (!empty($aFilter['onlyIncluded'])) {
                $sJoin = ' JOIN `communicationItemArticles` AS `cia` USING (`articleId`)';
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`cia`.`communicationItemId` = '. db_int($aFilter['communicationItemId']);                 
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`cia`.`included` = 1';
            }            
            elseif (!empty($aFilter['usedBefore'])) {                
                if ($aFilter['usedBefore'] == Article::ARTICLE_USED_BEFORE) {
                    $sJoin = ' JOIN `communicationItemArticles` AS `cia` USING (`articleId`)';
                    $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`cia`.`alreadySent` = 1';
                }
                else {
                    $sWhere .= ($sWhere != '' ? ' AND ' : '') 
                            . '( 
                                    (
                                        ( `a`.`articleId` NOT IN ( SELECT DISTINCT `articleId` FROM `communicationItemArticles` WHERE `alreadySent` = 1))
                                        AND
                                        ( `a`.`articleId` IN ( SELECT DISTINCT `articleId` FROM `communicationItemArticles` WHERE `alreadySent` = 0))     
                                    )
                                   
                                    OR ( `a`.`articleId` NOT IN ( SELECT DISTINCT `articleId` FROM `communicationItemArticles`))                                         
                                )';
                }
                
            }
            else {
                $sJoin = ' LEFT JOIN `communicationItemArticles` AS `cia` USING (`articleId`)';
                /*$sWhere .= ($sWhere != '' ? ' AND ' : '') . '(`cia`.`communicationItemId` = '. db_int($aFilter['communicationItemId']);        
                $sWhere .= ' OR `a`.`articleId` NOT IN (SELECT `cia`.`articleId` FROM `communicationItemArticles` AS `cia` WHERE `cia`.`communicationItemId` = '.db_int($aFilter['communicationItemId']).'))';*/
            }
        }        
        
        //check not pending ones
        if (!empty($aFilter['notDev'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`status` <> '. db_int(Article::ARTICLE_STATUS_PENDING);                        
        }

        # search for q
        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '(`a`.`title` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ' OR `a`.`intro` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ' OR `a`.`content` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ')';                        
        }
        
        # search by author
        if (!empty($aFilter['authorId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`authorId` = '. db_int($aFilter['authorId']);
        }
        
        if (!empty($aFilter['editorId'])) {
            $sJoin = ' JOIN `users` AS `u1` ON `a`.`authorId` = `u1`.`userId`';
            $sJoin .= ' JOIN `users` AS `u2` ON `u1`.`editorId` = `u2`.`userId`';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`u2`.`editorId` = '. db_int($aFilter['editorId']);            
        }        
        
        # search by author        
        
        if (!empty($aFilter['articleId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleId` = ' . db_int($aFilter['articleId']);
        }

        # search by parent page
        
        if (!empty($aFilter['parentPageId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`parentPageId` = ' . db_int($aFilter['parentPageId']);
        }
        
        
        if (!empty($aFilter['notParentPageId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`parentPageId` <> ' . db_int($aFilter['notParentPageId']);
        }
        
        
        # get only comments that aren't letters
        if (!empty($aFilter['notLetters'])) {            
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleCategoryId` <> ' . db_int(ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY);
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
                        `a`.*
                    FROM
                        `articles` AS `a`
                    ' . $sFrom . '
                    ' . $sJoin . '                        
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aArticles = $oDb->query($sQuery, QRY_OBJECT, "Article");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
      
        return $aArticles;
    }
    
    /**
     * return article comments filtered by a few options
     * 
     * @param array $aFilter
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Article 
     */
    public static function getArticleCommentsByFilter($aFilter, $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`ac`.`postedOn`' => 'DESC')) {
        $sFrom = '';
        $sJoin = '';
        $sWhere = '`ac`.`confirmCode` IS NULL'; 
        $sGroupBy = '';

        // check for articleId in filter
        if (!empty($aFilter['articleId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`articleId` = ' . db_int($aFilter['articleId']);
        }
        
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`online` = 1';
        }        
        
        # search for q
        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`ac`.`comment` LIKE ' . db_str('%' . $aFilter['q'] . '%');
        }
       
        
        if (!empty($aFilter['articleCategoryId']) || !empty($aFilter['notLetters'])) {
            $sJoin .= ' JOIN `articles` AS `a` USING( `articleId`) ';   
        }
        
        # get only comments of an specific article category
        if (!empty($aFilter['articleCategoryId'])) {            
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleCategoryId` = ' . db_int($aFilter['articleCategoryId']);
        }
        
        # get only comments that aren't letters
        if (!empty($aFilter['notLetters'])) {            
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`a`.`articleCategoryId` <> ' . db_int(ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY);
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
                        `articleComments` AS `ac`
                    ' . $sFrom . '
                    ' . $sJoin . '    
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aArticleComments = $oDb->query($sQuery, QRY_OBJECT, "ArticleComment");
        
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aArticleComments;
    }    

    /**
     * save connection between a article and an image
     * @param int $iArticleId
     * @param int $iImageId
     */
    public static function saveArticleImageRelation($iArticleId, $iImageId) {
        $sQuery = ' INSERT IGNORE INTO
                        `articlesImages`
                    (
                        `articleId`,
                        `imageId`
                    )
                    VALUES
                    (
                        ' . db_int($iArticleId) . ',
                        ' . db_int($iImageId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get images for article by filter
     * @param int $iArticleId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Image
     */
    public static function getImagesByFilter($iArticleId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `i`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `i`.*
                    FROM
                        `images` AS `i`
                    JOIN
                        `articlesImages` AS `pi` USING (`imageId`)
                    WHERE
                        `pi`.`articleId` = ' . db_int($iArticleId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `i`.`order` ASC, `i`.`imageId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Image');
    }

    /**
     * save connection between a article and a file
     * @param int $iArticleId
     * @param int $iMediaId
     */
    public static function saveArticleFileRelation($iArticleId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `articlesFiles`
                    (
                        `articleId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iArticleId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * get files for article by filter
     * @param int $iArticleId
     * @param array $aFilter
     * @param int $iLimit
     * @return array File
     */
    public static function getFilesByFilter($iArticleId, array $aFilter = array(), $iLimit = null) {

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
                        `articlesFiles` AS `pf` USING (`mediaId`)
                    JOIN
                        `media` AS `m` USING (`mediaId`)
                    WHERE
                        `pf`.`articleId` = ' . db_int($iArticleId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'File');
    }

    /**
     * save connection between a article and a link
     * @param int $iArticleId
     * @param int $iMediaId
     */
    public static function saveArticleLinkRelation($iArticleId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `articlesLinks`
                    (
                        `articleId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iArticleId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get links for article by filter
     * @param int $iArticleId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Link
     */
    public static function getLinksByFilter($iArticleId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `articlesLinks` AS `pl` USING (`mediaId`)
                    WHERE
                        `pl`.`articleId` = ' . db_int($iArticleId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `m`.`order` ASC, `m`.`mediaId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Link');
    }

    /**
     * save connection between a article and a YoutubeLink
     * @param int $iArticleId
     * @param int $iMediaId
     */
    public static function saveArticleYoutubeLinkRelation($iArticleId, $iMediaId) {
        $sQuery = ' INSERT IGNORE INTO
                        `articlesYoutubeLinks`
                    (
                        `articleId`,
                        `mediaId`
                    )
                    VALUES
                    (
                        ' . db_int($iArticleId) . ',
                        ' . db_int($iMediaId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get youtube links for article by filter
     * @param int $iArticleId
     * @param array $aFilter
     * @param int $iLimit
     * @return array YoutubeLink
     */
    public static function getYoutubeLinksByFilter($iArticleId, array $aFilter = array(), $iLimit = null) {

        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `m`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    JOIN
                        `articlesYoutubeLinks` AS `py` USING (`mediaId`)
                    WHERE
                        `py`.`articleId` = ' . db_int($iArticleId) . '
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
     * @param int $iArticleId
     * @param int $iLimit
     * @return array article
     */
    public static function getRelatedArticles($iArticleId, $iLimit = 5) {
        $sQuery = ' SELECT * 
                    FROM `articles`
                    WHERE   `status` = '.db_int(Article::ARTICLE_STATUS_PUBLISHED).'
                        AND `articleId` <> '.db_int($iArticleId).'
                        AND `articleCategoryId` = (SELECT `articleCategoryId` FROM `articles` WHERE `articleId` = '.db_int($iArticleId).' LIMIT 1)
                    LIMIT '.db_int($iLimit);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Article');
    }
    
    /**
     * Checks if the article was already sent in a newsletter email
     * 
     * @param int $iArticleId
     * @return boolean
     */
    public static function wasArticleAlreadySent($iArticleId) {
        $sQuery = ' SELECT *
                    FROM `communicationItemArticles`
                    WHERE `articleId` = ' . db_int($iArticleId) . '
                        AND `alreadySent` = 1';
        $oDb = DBConnections::get();
        $aResults = $oDb->query($sQuery, QRY_OBJECT, 'Article');
        return count($aResults) > 0;
}
    
    /**
     * Checks if the article is included in a newsletter
     * 
     * @param int $iArticleId
     * @param int $iCommunicationItemId
     * @return boolean
     */
    public static function isIncludedInNewsLetter($iArticleId, $iCommunicationItemId) {
        $sQuery = ' SELECT *
                    FROM `communicationItemArticles`
                    WHERE `articleId` = ' . db_int($iArticleId) . '
                        AND `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                        AND `included` = 1';
                
        $oDb = DBConnections::get();
        $aResults = $oDb->query($sQuery, QRY_OBJECT, 'Article');
        return count($aResults) > 0;        
    }
}
?>