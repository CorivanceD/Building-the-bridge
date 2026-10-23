<?php

// Models and managers used by this class
require_once 'CustomerGroup.class.php';

class CustomerGroupManager {

    /**
     * get a CustomerGroup by id
     * @param int $iCustomerGroupId
     * @return CustomerGroup
     */
    public static function getCustomerGroupById($iCustomerGroupId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerGroups`
                    WHERE
                        `customerGroupId` = ' . db_int($iCustomerGroupId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "CustomerGroup");
    }

    /**
     * get CustomerGroups by customerId
     * @param int $iCustomerId
     * @return CustomerGroup
     */
    public static function getCustomerGroupsByCustomerId($iCustomerId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerGroupRelations`
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CustomerGroup");
    }
    
    /**
     * get CustomerGroups by communicationId
     * @param int $iCommunicationId
     * @return CustomerGroup
     */
    public static function getCustomerGroupsByCommunicationItemId($iCommunicationId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemCustomerGroupRelation` as `cicgr`
                    JOIN
                        `customerGroups` as `cg`
                    USING
                        (`customerGroupId`)
                    WHERE
                        `cicgr`.`communicationItemId` = ' . db_int($iCommunicationId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CustomerGroup");
    }

    
    /**
     * get all CustomerGroup objects
     * @return array CustomerGroup
     */
    public static function getAllCustomerGroups() {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerGroups`
                    ORDER BY
                        `created` DESC
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CustomerGroup");
    }

    /**
     * save a CustomerGroup
     * @param Customer $oCustomerGroup
     */
    public static function saveCustomerGroup(CustomerGroup $oCustomerGroup) {
        $sQuery = ' INSERT INTO `customerGroups`(
                        `customerGroupId`,
                        `title`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCustomerGroup->customerGroupId) . ',
                        ' . db_str($oCustomerGroup->title) . ',
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        `title`=VALUES(`title`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oCustomerGroup->customerGroupId === null)
            $oCustomerGroup->customerGroupId = $oDb->insert_id;
    }

    /**
     * delete a CustomerGroup
     * @param CustomerGroup $oCustomerGroup
     * @return bool true
     */
    public static function deleteCustomerGroup(CustomerGroup $oCustomerGroup) {
        $sQuery = ' DELETE FROM
                        `customerGroups`
                    WHERE
                        `customerGroupId` = ' . db_int($oCustomerGroup->customerGroupId) . '
                    LIMIT
                        1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }
    
    /**
     * delete a Customer out of a CustomerGroup
     * @param CustomerGroup $oCustomerGroup
     * @return bool true
     */
    public static function deleteCustomerFromCustomerGroup($iCustomerGroupId, $iCustomerId) {
        $sQuery = ' DELETE FROM
                        `customerGroupRelations`
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    AND
                        `customerGroupId` = ' . db_int($iCustomerGroupId) . '
                    LIMIT
                        1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }

}

?>