<?php

// Models and managers used by this class
require_once 'CommunicationItemLinkRedirectStat.class.php';

class CommunicationItemLinkRedirectStatManager {

   
    /**
     * get CommunicationItemLinkRedirectStats by customerId
     * @param int $iCustomerId
     * @param int $iCommunicationItemId
     * @return CommunicationItemLinkRedirectStat
     */
    public static function getCommunicationItemLinkRedirectStatsByCustomer($iCustomerId, $iCommunicationItemId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemLinkRedirectStats`
                    WHERE
                        `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    AND
                        `customerId` = ' . db_int($iCustomerId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CommunicationItemLinkRedirectStat");
    }
    
    /**
     * get CommunicationItemLinkRedirectStats by customerId
     * @param int $iCommunicationItemId
     * @param int $iCommunicationItemLinkRedirectId
     * @return array amount
     */
    public static function getUniqueAmountOfClicksOfRedirectStat($iCommunicationItemId, $iCommunicationItemLinkRedirectId) {
        $sQuery = ' SELECT
                        COUNT(*) AS `amount`
                    FROM
                        `communicationItemLinkRedirectStats`
                    WHERE
                        `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    AND
                        `communicationItemLinkRedirectId` = ' . db_int($iCommunicationItemLinkRedirectId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT);
    }
    
    
    /**
     * get active customers which have clicked on a link
     * @param int $iCommunicationItemId
     * @param int $iCommunicationItemLinkRedirectId
     * @return array amount
     */
    public static function getCustomersOfClickedRedirectLink($iCommunicationItemId, $iCommunicationItemLinkRedirectId) {
        $sQuery = ' SELECT
                        `cilrs`.`communicationItemId` as `communicationItemId`,
                        `cilrs`.`name` as `name`,
                        `cilrs`.`email` as `email`,
                        `c`.`created` as `clientCreated`
                    FROM
                        `communicationItemLinkRedirectStats` AS `cilrs`
                    LEFT JOIN
                        `customers` AS `c`
                    USING
                        (`customerId`)
                    WHERE
                        `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    AND
                        `communicationItemLinkRedirectId` = ' . db_int($iCommunicationItemLinkRedirectId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }


    /**
     * save a CommunicationItemLinkRedirectStat
     * @param CommunicationItemLinkRedirectStat $oCommunicationItemLinkRedirectStat
     */
    public static function saveCommunicationItemLinkRedirectStat(CommunicationItemLinkRedirectStat $oCommunicationItemLinkRedirectStat) {
        $sQuery = ' INSERT INTO `communicationItemLinkRedirectStats`(
                        `communicationItemId`,
                        `customerId`,
                        `name`,
                        `email`,
                        `communicationItemLinkRedirectId`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCommunicationItemLinkRedirectStat->communicationItemId) . ',
                        ' . db_int($oCommunicationItemLinkRedirectStat->customerId) . ',
                        ' . db_str($oCommunicationItemLinkRedirectStat->name) . ',
                        ' . db_str($oCommunicationItemLinkRedirectStat->email) . ',
                        ' . db_int($oCommunicationItemLinkRedirectStat->communicationItemLinkRedirectId) . ',
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        `communicationItemId`=VALUES(`communicationItemId`),
                        `customerId`=VALUES(`customerId`),
                        `name`=VALUES(`name`),
                        `email`=VALUES(`email`),
                        `communicationItemLinkRedirectId`=VALUES(`communicationItemLinkRedirectId`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

}

?>