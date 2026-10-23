<?php
class CustomerLinkCategoryManager {
    
    /**
     * Get a CustomerLinkCategory by id
     * 
     * @param int $iCustomerLinkCategoryId
     * @return CustomerLinkCategory
     */
    public static function getCustomerLinkCategoryById($iCustomerLinkCategoryId) {
        $sQuery = ' SELECT * 
                    FROM `customerLinkCategories`
                    WHERE `customerLinkCategoryId` = ' . db_int($iCustomerLinkCategoryId) . '
                    LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'CustomerLinkCategory');
    }
     
    /**
     * Save a CustomerLinkCategory into the db
     * 
     * @param CustomerLinkCategory $oCustomerLinkCategory
     */
    public static function saveCustomerLinkCategory(CustomerLinkCategory $oCustomerLinkCategory) {
        $sQuery = ' INSERT INTO `customerLinkCategories` (
                        `customerLinkCategoryId`,
                        `name`,
                        `parentId`,
                        `created`
                    ) VALUES ('
                    . db_int($oCustomerLinkCategory->customerLinkCategoryId) . ', '
                    . db_str($oCustomerLinkCategory->name) . ', '
                    . db_int($oCustomerLinkCategory->parentId) . ', '
                    . 'NOW()
                    ) ON DUPLICATE KEY UPDATE
                        `name`=VALUES(`name`),
                        `parentId`=VALUES(`parentId`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        if (empty($oCustomerLinkCategory->customerLinkCategoryId)) {
            $oCustomerLinkCategory->customerLinkCategoryId = $oDb->insert_id;
        }        
    }
    
    /**
     * return links filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array CustomerLink
     */
    public static function getCustomerLinkCategoriesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`slc`.`customerLinkCategoryId`' => 'ASC')) {

        $sWhere = '';
        $sJoin = '';
        
        # get only parent categories
        if (!empty($aFilter['onlyParent'])) {
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slc`.`parentId` IS NULL';            
        }        
        
        # search by parent category id
        if (!empty($aFilter['parentId'])) {
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slc`.`parentId` = ' . db_int($aFilter['parentId']);            
        }
        
        
        # get categries of an specific link
        if (!empty($aFilter['customerLinkId'])) {
            $sJoin = 'JOIN `customerLinkCustomerLinkCategories` AS `slslc` USING(`customerLinkCategoryId`) ';
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slslc`.`customerLinkId` = ' . db_int($aFilter['customerLinkId']);            
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
                        `slc`.*
                    FROM
                        `customerLinkCategories` AS `slc`
                    ' . $sJoin
                      . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aCustomerLinks = $oDb->query($sQuery, QRY_OBJECT, "CustomerLinkCategory");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aCustomerLinks;
    }
}
?>