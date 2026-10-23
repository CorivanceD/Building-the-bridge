<?php
class WorldLeaderManager {
    
    /**
     * Get a WorldLeader by id
     * 
     * @param int $iWorldLeaderId
     * @return WorldLeader
     */
    public static function getWorldLeaderById($iWorldLeaderId) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaders` 
                    WHERE `worldLeaderId` = ' .db_int($iWorldLeaderId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'WorldLeader');
    }
    
    /**
     * Get a WorldLeader by name
     * 
     * @param string $sWorldLeaderName
     * @return WorldLeader
     */
    public static function getWorldLeaderByName($sWorldLeaderName) {
        $sQuery = ' SELECT * 
                    FROM `worldLeaders` 
                    WHERE `name` 
                    LIKE ' .db_str('%' . $sWorldLeaderName . '%') . '
                    LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'WorldLeader');
    }
        
    
    /**
     * Save a WorldLeader object into the db
     * 
     * @param WorldLeader $oWorldLeader
     * @return boolean
     */
    public static function saveWorldLeader(WorldLeader $oWorldLeader) {
        $sQuery = ' INSERT INTO `worldLeaders` (
                        `worldLeaderId`,
                        `prefix`,
                        `name`, 
                        `email`,
                        `position`,
                        `company`,
                        `department`,
                        `created`
                    ) VALUES ('
                        . db_int($oWorldLeader->worldLeaderId) . ', '
                        . db_str($oWorldLeader->prefix) . ', '
                        . db_str($oWorldLeader->name) . ', '
                        . db_str($oWorldLeader->email) . ', '
                        . db_str($oWorldLeader->position) . ', '
                        . db_str($oWorldLeader->company) . ', '
                        . db_str($oWorldLeader->department) . ', 
                        NOW()
                    ) ON DUPLICATE KEY UPDATE
                        `prefix`=VALUES(`prefix`),
                        `name`=VALUES(`name`),                        
                        `email`=VALUES(`email`),
                        `position`=VALUES(`position`),
                        `company`=VALUES(`company`),
                        `department`=VALUES(`department`);';
        $oDb = DBConnections::get();                
        $oDb->query($sQuery);
        
        if (empty($oWorldLeader->worldLeaderId)) {
            $oWorldLeader->worldLeaderId = $oDb->insert_id;                    
        }
        
        return $oDb->affected_rows > 1;
    }
    
    /**
     * return worldleaders filtered by a few options
     * 
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Article 
     */
    public static function getWorldLeadersByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`wl`.`name`' => 'ASC')) {
        $sFrom = '';
        $sWhere = ''; 
        $sGroupBy = '';

        // check by name
        if (!empty($aFilter['name'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`wl`.`name` LIKE ' . db_str('%' .$aFilter['name'] . '%');
        }
        
        // check for country in filter
        if (!empty($aFilter['countryId'])) {
            $sFrom .= empty($sFrom) ? 'JOIN `worldLeaderAddresses` AS `wla` USING (`worldLeaderId`)' : '';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`wla`.`countryId` = ' . db_int($aFilter['countryId']);
        }
        
        // check for region in filter
        if (!empty($aFilter['regionId'])) {
            $sFrom .= empty($sFrom) ? 'JOIN `worldLeaderAddresses` AS `wla` USING (`worldLeaderId`)' : '';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`wla`.`regionId` = ' . db_int($aFilter['regionId']);
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
                        `wl`.*
                    FROM
                        `worldLeaders` AS `wl`
                    ' . $sFrom . '                       
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';
        $oDb = DBConnections::get();
        $aWorldLeaders = $oDb->query($sQuery, QRY_OBJECT, "WorldLeader");
        
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
        
        return $aWorldLeaders;
    }
    
    /**
     * Delete a world leader from the database
     * @param int $iWorldLeaderId
     * @return boolean
     */
    public static function deleteWorldLeaderById($iWorldLeaderId) {
        $sQuery = ' DELETE FROM `worldLeaders` WHERE `worldLeaderId` = ' . db_int($iWorldLeaderId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery);
        
        return $oDb->affected_rows > 0;
    }
}
?>