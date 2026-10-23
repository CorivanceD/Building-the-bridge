<?php

class NewsSourceLogoManager {
    
    
    /**
     * get a news source logo by id
     * @param int $iNewsSourceLogoId
     * @return NewsSourceLogo
     */
    public static function getNewsSourceLogoById($iNewsSourceLogoId) {
        $sQuery = 'SELECT *  FROM `newsSourceLogos` WHERE `newsSourceLogoId` = '.db_int($iNewsSourceLogoId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'NewsSourceLogo');
    }
    
    /**
     * get a news source logo by the news source name
     * @param string $sNewsSourceName
     * @return NewsSourceLogo
     */
    public static function getNewsSourceLogoByName($sNewsSourceName) {
        //$sQuery = 'SELECT * FROM `newsSourceLogos`WHERE `sourceName` RLIKE ' . db_str('[[:<:]]'.trim($sNewsSourceName).'[[:>:]]'). '' . ' LIMIT 1';
        $sQuery = 'SELECT * FROM `newsSourceLogos`WHERE `sourceName` LIKE ' . db_str($sNewsSourceName.'%') .' LIMIT 1';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'NewsSourceLogo');
    }
    
    /**
     * saves a NewsSourceLogo object
     * @param NewsSourceLogo $oNewsSourceLogo
     * @return int
     */
    public static function saveNewsSourceLogo(NewsSourceLogo $oNewsSourceLogo) {
        $sQuery = ' INSERT INTO `newsSourceLogos`
                    (
                        `newsSourceLogoId`,
                        `sourceName`,
                        `imageId` 
                    )
                    VALUES ('
                    .db_int($oNewsSourceLogo->newsSourceLogoId) .', '
                    .db_str($oNewsSourceLogo->sourceName).', '
                    .db_int($oNewsSourceLogo->imageId).'
                    )
                    ON DUPLICATE KEY UPDATE
                        `sourceName`=VALUES(`sourceName`),
                        `imageId`=VALUES(`imageId`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        if (empty($oNewsSourceLogo->newsSourceLogoId)) {
            $oNewsSourceLogo->newsSourceLogoId = $oDb->insert_id;
        }
        
        return $oDb->affected_rows;
    }
    
    /**
     * delete a NewsSourceLogo object by its id
     * @param int $iNewsSourceLogoId
     * @return int
     */
    public static function deleteNewsSourceLogo($iNewsSourceLogoId) {
        $sQuery = 'DELETE FROM `newsSourceLogos` WHERE `newsSourceLogoId` = '.db_int($iNewsSourceLogoId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        return $oDb->affected_rows;
    }
    
    /**
     * get a list of the different NewsSourceLogo objects
     * @param array $aFilter
     * @param int $iLimit
     * @param int $iStart
     * @param int $iFoundRows
     * @param array $aOrderBy
     * @return array NewsSourceLogo
     */
    public static function getNewsSourceLogosByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`nsl`.`sourceName`' => 'ASC')) {

        $sWhere = '';
        $sFrom = '';
        
        # search by source name
        if (!empty($aFilter['sourceName'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`nsl`.`sourceName` LIKE '.db_str('%'.$aFilter['sourceName'].'%');
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

        $sQuery = ' SELECT '.($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '').'
                        `nsl`.*
                    FROM
                        `newsSourceLogos` AS `nsl`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aProducts = $oDb->query($sQuery, QRY_OBJECT, "NewsSourceLogo");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
        return $aProducts;
    }
}

?>
