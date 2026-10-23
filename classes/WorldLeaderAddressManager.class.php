<?php
class WorldLeaderAddressManager {
    
    /**
     * Get a world leader address by id
     * @param int $iWorldLeaderAddressId
     * @return WorldLeaderAddress
     */
    public static function getWorldLeaderAddressById($iWorldLeaderAddressId) {
        $sQuery = ' SELECT *
                    FROM `worldLeaderAddresses`
                    WHERE `worldLeaderAddressId` = ' . db_int($iWorldLeaderAddressId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'WorldLeaderAddress');
    }
    
    /**
     * Save a WorldLeaderAddress object into the db
     * @param WorldLeaderAddress $oWorldLeaderAddress
     * @return boolean
     */
    public static function saveWorldLeaderAddress(WorldLeaderAddress $oWorldLeaderAddress) {
        $sQuery = ' INSERT INTO `worldLeaderAddresses` (
                        `worldLeaderAddressId`,
                        `worldLeaderId`,
                        `name`,
                        `address`,
                        `postalCode`,
                        `city`,
                        `addressCountryId`,
                        `regionId`,
                        `countryId`,
                        `order`,
                        `created`
                    ) VALUES ('
                        . db_int($oWorldLeaderAddress->worldLeaderAddressId) . ', '
                        . db_int($oWorldLeaderAddress->worldLeaderId) . ', '
                        . db_str($oWorldLeaderAddress->name) . ', '
                        . db_str($oWorldLeaderAddress->address) . ', '
                        . db_str($oWorldLeaderAddress->postalCode) . ', '
                        . db_str($oWorldLeaderAddress->city) . ', '
                        . db_int($oWorldLeaderAddress->addressCountryId) . ', '
                        . db_int($oWorldLeaderAddress->regionId) . ', '
                        . db_int($oWorldLeaderAddress->countryId) . ', '  
                        . db_int($oWorldLeaderAddress->order) . ', 
                        NOW()
                    ) ON DUPLICATE KEY UPDATE
                        `worldLeaderId`=VALUES(`worldLeaderId`),
                        `name`=VALUES(`name`),
                        `address`=VALUES(`address`),
                        `postalCode`=VALUES(`postalCode`),
                        `city`=VALUES(`city`),
                        `addressCountryId`=VALUES(`addressCountryId`),
                        `regionId`=VALUES(`regionId`),
                        `countryId`=VALUES(`countryId`),
                        `order`=VALUES(`order`);';
        $oDb = DBConnections::get();
        $oDb->query($sQuery);
        
        if (empty($oWorldLeaderAddress->worldLeaderAddressId)) {
            $oWorldLeaderAddress->worldLeaderAddressId = $oDb->insert_id;
        }
        
        return $oDb->affected_rows > 0;
    }
    
    /**
     * Get addresses of a world leader
     * @param int $iWorldLeaderId
     * @param int $iRegionId
     * @param int $iCountryId
     * @return array WorldLeaderAddress
     */
    public static function getWorldLeaderAddressesByLeaderId($iWorldLeaderId, $iRegionId = null, $iCountryId = null) {
        $sQuery = ' SELECT *
                    FROM `worldLeaderAddresses`
                    WHERE `worldLeaderId` = ' .db_int($iWorldLeaderId);
        if (!empty($iRegionId)) {
             $sQuery .= ' AND `regionId` = ' . db_int($iRegionId);
        }    
        if (!empty($iCountryId)) {
             $sQuery .= ' AND `countryId` = ' . db_int($iCountryId);
        }        

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'WorldLeaderAddress');
    }
    
    /**
     * Delete a world leader address from the database
     * @param int $iWorldLeaderAddressId
     * @return boolean
     */
    public static function deleteWorldLeaderAddressById($iWorldLeaderAddressId) {
        $sQuery = ' DELETE FROM `worldLeaderAddresses` WHERE `worldLeaderAddressId` = ' . db_int($iWorldLeaderAddressId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery);
        
        return $oDb->affected_rows > 0;
    }    
}
?>