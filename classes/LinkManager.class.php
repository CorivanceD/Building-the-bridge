<?php

/* Models and managers used by the LinkManager */
require_once 'Link.class.php';

class LinkManager {

    /**
     * save Link object
     * @param Link $oLink
     */
    public static function saveLink(Link $oLink) {

        $oDb = DBConnections::get();

        # new image
        $sQuery = ' INSERT INTO
                        `media`
                        (
                            `mediaId`,
                            `link`,
                            `title`,
                            `type`,
                            `online`,
                            `order`,
                            `created`
                        )
                        VALUES (
                            ' . db_int($oLink->mediaId) . ',
                            ' . db_str(addHttp($oLink->link)) . ',
                            ' . db_str($oLink->title) . ',
                            ' . db_str($oLink->type) . ',
                            ' . db_int($oLink->online) . ',
                            ' . db_int($oLink->order) . ',
                            NOW()
                        )
                        ON DUPLICATE KEY UPDATE
                            `link`=VALUES(`link`),
                            `title`=VALUES(`title`),
                            `type`=VALUES(`type`),
                            `online`=VALUES(`online`),
                            `order`=VALUES(`order`)
                    ;';

        $oDb->query($sQuery, QRY_NORESULT);
        if ($oLink->mediaId === null)
            $oLink->mediaId = $oDb->insert_id;
    }

    /**
     * get a Link by mediaId
     * @param int $iMediaId
     * @return Link 
     */
    public static function getLinkById($iMediaId) {
        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    WHERE
                        `m`.`mediaId` = ' . db_int($iMediaId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Link');
    }

    /**
     * delete a Link
     * @param Link $oLink
     * @return boolean
     */
    public static function deleteLink(Link $oLink) {

        if ($oLink->isDeletable()) {
            $oDb = DBConnections::get();

            $sQuery = 'DELETE FROM `media` WHERE `mediaId` = ' . db_int($oLink->mediaId);
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update media Order
     * @param array $aMediaIds 
     */
    public static function updateLinkOrder(array $aMediaIds) {

        # start query, uses on duplicate key
        $sQuery = "";

        # add all mediaIds and values
        $iT = 1; //counter
        $sValues = '';
        foreach ($aMediaIds AS $iMediaId) {
            $sValues .= ($sValues == '' ? '' : ',') . '(' . db_int($iMediaId) . ', ' . db_int($iT) . ')';
            $iT++;
        }
        $sQuery .= 'INSERT INTO
                        `media`
                    (
                        `mediaId`,
                        `order`
                    )
                    VALUES
                    ' . $sValues . '
                    ON DUPLICATE KEY UPDATE
                        `order`=VALUES(`order`)
            ;';

        if (count($aMediaIds) > 0) {
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

}

?>
