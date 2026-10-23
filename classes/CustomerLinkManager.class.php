<?php
class CustomerLinkManager {
    
    /**
     * Get a customerLink by id
     * 
     * @param int $iCustomerLinkId
     * @return CustomerLink
     */
    public static function getCustomerLinkById($iCustomerLinkId) {
        $sQuery = ' SELECT * 
                    FROM `customerLinks`
                    WHERE `customerLinkId` = ' . db_int($iCustomerLinkId) . '
                    LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'CustomerLink');
    }
     
    /**
     * Save a Single Link into the db
     * 
     * @param CustomerLink $oCustomerLink
     */
    public static function saveCustomerLink(CustomerLink $oCustomerLink) {
        $sQuery = ' INSERT INTO `customerLinks` (
                        `customerLinkId`,
                        `online`,
                        `sender`,                        
                        `name`,
                        `url`,
                        `returnUrl`,
                        `message`,
                        `created`
                    ) VALUES ('
                    . db_int($oCustomerLink->customerLinkId) . ', '
                    . db_int($oCustomerLink->online) . ', '
                    . db_str($oCustomerLink->sender) . ', '
                    . db_str($oCustomerLink->name) . ', '
                    . db_str($oCustomerLink->url) . ', '
                    . db_str($oCustomerLink->returnUrl) . ', '                
                    . db_str($oCustomerLink->message) . ', ' 
                    . 'NOW()
                    ) ON DUPLICATE KEY UPDATE
                        `online`=VALUES(`online`),
                        `sender`=VALUES(`sender`),                        
                        `name`=VALUES(`name`),
                        `url`=VALUES(`url`),
                        `returnUrl`=VALUES(`returnUrl`),
                        `message`=VALUES(`message`)
                        ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        if (empty($oCustomerLink->customerLinkId)) {
            $oCustomerLink->customerLinkId = $oDb->insert_id;
        }
        
        self::updateCustomerLinkCategories($oCustomerLink);
    }
    
    /**
     * Update the categories of the link
     * 
     * @param CustomerLink $oCustomerLink     
     */
    public static function updateCustomerLinkCategories(CustomerLink $oCustomerLink) {
        if (empty($oCustomerLink->customerLinkId)) {
            return;
        }
        
        $aCategories = $oCustomerLink->getCategories(false);
        $sQuery = ' DELETE FROM `customerLinkCustomerLinkCategories` 
                    WHERE `customerLinkId` = ' . db_int($oCustomerLink->customerLinkId) . ';';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        foreach ($aCategories as $oCategory) {
            $sQuery = ' INSERT IGNORE INTO `customerLinkCustomerLinkCategories` (
                            `customerLinkId`,
                            `customerLinkCategoryId`
                        ) VALUES ('
                        . db_int($oCustomerLink->customerLinkId) . ', '
                        . db_int($oCategory->customerLinkCategoryId) . ');';
            $oDb->query($sQuery, QRY_NORESULT);                                
        }                            
    }
    
    /**
     * 
     * @param CustomerLink $oCustomerLink
     * @return boolean
     */
    public static function updateLinkOnlineStatus(CustomerLink $oCustomerLink) {
        $sQuery = ' UPDATE `customerLinks`
                    SET `online` = ' .$oCustomerLink->online . '
                    WHERE `customerLinkId` = ' . db_int($oCustomerLink->customerLinkId) . ';';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
    }
    
    /**
     * Delete a CustomerLink from the db
     * 
     * @param CustomerLink $oCustomerLink
     * @return boolean
     */
    public static function deleteCustomerLink($oCustomerLink) {
        $sQuery = ' DELETE 
                    FROM `customerLinks`
                    WHERE `customerLinkId` = ' . db_int($oCustomerLink->customerLinkId) .';';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;        
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
    public static function getCustomerLinksByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`sl`.`customerLinkId`' => 'ASC')) {

        $sWhere = '';
        $sJoin = '';
        
        # get only online links
        if (empty($aFilter['showAll'])) {            
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`sl`.`online` = 1';           
        }        
        
        # get links by search text
        if (!empty($aFilter['q'])) {            
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`sl`.`name` LIKE '.db_str('%'.$aFilter['q'].'%'). ' OR `sl`.`sender` LIKE '.db_str('%'.$aFilter['q'].'%');           
        }
        
       # get links of an specific category 
        if (!empty($aFilter['linkSubCategoryId'])) {
            $sJoin = 'JOIN `customerLinkCustomerLinkCategories` as `slc` USING (`customerLinkId`) ';
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slc`.`customerLinkCategoryId` = ' . db_int($aFilter['linkSubCategoryId']);             
        }        
        elseif (!empty($aFilter['linkCategoryId'])) {
            $sJoin = 'JOIN `customerLinkCustomerLinkCategories` as `slc` USING (`customerLinkId`) ';
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slc`.`customerLinkCategoryId` = ' . db_int($aFilter['linkCategoryId']);             
        }
        
        # get links of the specified categories
        if (!empty($aFilter['categoryIds']) && is_array($aFilter['categoryIds'])) {
            $sJoin = 'JOIN `customerLinkCustomerLinkCategories` as `slc` USING (`customerLinkId`) ';
            $sWhere .= (empty($sWhere) ? '' : ' AND ') . '`slc`.`customerLinkCategoryId` IN (';
            
            foreach ($aFilter['categoryIds'] as $iCategoryId) {
                $sWhere .= db_int($iCategoryId) . ',';
            }
            
            $sWhere = substr($sWhere, 0, strlen($sWhere) - 1) . ')';
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
                        `sl`.*
                    FROM
                        `customerLinks` AS `sl`
                    ' . $sJoin
                      . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aCustomerLinks = $oDb->query($sQuery, QRY_OBJECT, "CustomerLink");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aCustomerLinks;
    }

}
?>