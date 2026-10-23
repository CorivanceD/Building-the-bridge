<?php
class WorldLeaderLetterManager {
    
    /**
     * Get a letter by its id
     * @param int $iWorldLeaderLetterId
     * @return WorldLeaderLetter
     */
    public static function getWorldLeaderLetterById($iWorldLeaderLetterId) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaderLetter`
                    WHERE `worldLeaderLetterId` = ' . db_int($iWorldLeaderLetterId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'WorldLeaderLetter');
    }
    
    /**    
     * Get a letter by the article id
     * @param int $iArticleId
     * @return WorldLeaderLetter
     */
    public static function getWorldLeaderLetterByArticleId($iArticleId) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaderLetters`
                    WHERE `articleId` = ' . db_int($iArticleId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'WorldLeaderLetter');
    }   
    
    /**    
     * Get a letter by the leader id
     * @param int $iLeaderId
     * @return array WorldLeaderLetter
     */
    public static function getWorldLeaderLettersByLeaderId($iLeaderId) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaderLetters`
                    WHERE `worldLeaderId` = ' . db_int($iLeaderId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'WorldLeaderLetter');
    }   
    
    /**    
     * Get a letter by the address id
     * @param int $iAddressId
     * @return Warray orldLeaderLetter
     */
    public static function getWorldLeaderLettersByAddressId($iAddressId) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaderLetters`
                    WHERE `worldLeaderAddressId` = ' . db_int($iAddressId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'WorldLeaderLetter');
    }    
    
    /**
     * Save a WorlLeaderLetter object into the db
     * @param WorldLeaderLetter $oWorldLeaderLetter
     * @return boolean
     */
    public static function saveWorldLeaderLetter(WorldLeaderLetter $oWorldLeaderLetter) {
        $sQuery = ' INSERT INTO `worldLeaderLetters` (
                        `worldLeaderLetterId`,
                        `articleId`,
                        `worldLeaderId`,
                        `worldLeaderAddressId`,
                        `created`
                    ) VALUES ('
                        . db_int($oWorldLeaderLetter->worldLeaderLetterId) . ', '
                        . db_int($oWorldLeaderLetter->articleId) . ', '
                        . db_int($oWorldLeaderLetter->worldLeaderId) . ', '
                        . db_int($oWorldLeaderLetter->worldLeaderAddressId) . ',
                        NOW()
                    ) ON DUPLICATE KEY UPDATE
                        `articleId`=VALUES(`articleId`),
                        `worldLeaderId`=VALUES(`worldLeaderId`),
                        `worldLeaderAddressId`=VALUES(`worldLeaderAddressId`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery);
        
        if (empty($oWorldLeaderLetter->worldLeaderLetterId)) {
            $oWorldLeaderLetter->worldLeaderLetterId = $oDb->insert_id;
        }
        
        return $oDb->affected_rows > 0;
    }
    
    /**
     * return letters filtered by a few options
     * 
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Article 
     */
    public static function getWorldLeaderLettersByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`a`.`order`' => 'ASC', '`a`.`pubDate`' => 'DESC')) {
        $aFilter['articleCategoryId'] = ArticleCategory::WORLD_LEADER_LETTERS_CATEGORY;
        return ArticleManager::getArticlesByFilter($aFilter, $iLimit, $iStart, $iFoundRows, $aOrderBy);
    }
    
    /**
     * Delete a letter
     * 
     * @param WorldLeaderLetter $oWorldLeaderLetter
     * @return boolean
     */
    public static function deleteWorldLeaderLetter(WorldLeaderLetter $oWorldLeaderLetter) {
        $sQuery = ' DELETE FROM `worldLeadeLetters` 
                    WHERE `worldLeaderLetterId` = ' . db_int($oWorldLeaderLetter->worldLeaderLetterId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery);
        return $oDb->affected_rows > 0;
    }
}
?>