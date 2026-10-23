<?php

// Models and managers used by this class
require_once 'CommunicationItemLinkRedirect.class.php';

class CommunicationItemLinkRedirectManager {

    /**
     * get a communicationItemLinkRedirects by id
     * @param int $iCommunicationItemLinkRedirectId
     * @return CommunicationItemLinkRedirect
     */
    public static function getCommunicationItemLinkRedirectByLinkRedirectId($iCommunicationItemLinkRedirectId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemLinkRedirects`
                    WHERE
                        `communicationItemLinkRedirectId` = ' . db_int($iCommunicationItemLinkRedirectId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "CommunicationItemLinkRedirect");
    }
    
    /**
     * get a communicationItemLinkRedirects by hash and communicationItemId
     * @param str $sCommunicationItemLinkRedirectHash
     * @param int $iCommunicationItemId
     * @return CommunicationItemLinkRedirect
     */
    public static function getCommunicationItemLinkRedirectByHashAndCommunicationItemId($sCommunicationItemLinkRedirectHash, $iCommunicationItemId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemLinkRedirects`
                    WHERE
                        `redirectHash` = ' . db_str($sCommunicationItemLinkRedirectHash) . '
                    AND
                        `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    LIMIT
                        1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "CommunicationItemLinkRedirect");
    }

    /**
     * get CommunicationItemLinkRedirect objects
     * @param int $iCommunicationItemId
     * @return array CommunicationItem
     */
    public static function getCommunicationItemLinkRedirectsByCommunicationItemId($iCommunicationItemId) {
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemLinkRedirects` AS `cilr`
                    WHERE
                        `cilr`.`communicationItemId` = ' . $iCommunicationItemId . '
                    ORDER BY
                        `cilr`.`communicationItemLinkRedirectId` ASC
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CommunicationItemLinkRedirect");
    }

    /**
     * save a CommunicationItem
     * @param CommunicationItem $oCommunicationItem
     */
    public static function saveCommunicationItemLinkRedirect(CommunicationItemLinkRedirect $oCommunicationItemLinkRedirect) {
        $sQuery = ' INSERT INTO `communicationItemLinkRedirects`(
                        `communicationItemLinkRedirectId`,
                        `communicationItemId`,
                        `originalUrl`,
                        `redirectHash`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCommunicationItemLinkRedirect->communicationItemLinkRedirectId) . ',
                        ' . db_int($oCommunicationItemLinkRedirect->communicationItemId) . ',
                        ' . db_str($oCommunicationItemLinkRedirect->originalUrl) . ',
                        ' . db_str($oCommunicationItemLinkRedirect->redirectHash) . ',
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        `communicationItemLinkRedirectId`=VALUES(`communicationItemLinkRedirectId`),
                        `communicationItemId`=VALUES(`communicationItemId`),
                        `originalUrl`=VALUES(`originalUrl`),
                        `redirectHash`=VALUES(`redirectHash`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oCommunicationItemLinkRedirect->communicationItemLinkRedirectId === null)
            $oCommunicationItemLinkRedirect->communicationItemLinkRedirectId = $oDb->insert_id;

    }

    /**
     * delete a deleteCommunicationItemLinkRedirect
     * @param CommunicationItemLinkRedirect $oCommunicationItemLinkRedirect
     * @return bool true
     */
    public static function deleteCommunicationItemLinkRedirect(CommunicationItemLinkRedirect $oCommunicationItemLinkRedirect) {
        if ($oCommunicationItemLinkRedirect->isDeletable()) {

            # delete object
            $sQuery = '
                    DELETE FROM
                        `communicationItemLinkRedirects`
                    WHERE
                        `communicationItemLinkRedirectId` = ' . db_int($oCommunicationItemLinkRedirect->communicationItemLinkRedirectId) . '
                    LIMIT
                        1
                    ;';
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        } else {
            return false;
        }
    }

}

?>