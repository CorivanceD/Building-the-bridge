<?php

/* Models and managers used by the uploadManager */
require_once 'YoutubeLink.class.php';

class YoutubeLinkManager {

    /**
     * save YoutubeLink object
     * @param YoutubeLink $oYoutubeLink
     */
    public static function saveYoutubeLink(YoutubeLink $oYoutubeLink) {

        $oDb = DBConnections::get();

        # new youtube link
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
                            ' . db_int($oYoutubeLink->mediaId) . ',
                            ' . db_str(addHttp($oYoutubeLink->link)) . ',
                            ' . db_str($oYoutubeLink->title) . ',
                            ' . db_str($oYoutubeLink->type) . ',
                            ' . db_int($oYoutubeLink->online) . ',
                            ' . db_int($oYoutubeLink->order) . ',
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
        if ($oYoutubeLink->mediaId === null)
            $oYoutubeLink->mediaId = $oDb->insert_id;
    }

    /**
     * get a YoutubeLink by mediaId
     * @param int $iMediaId
     * @return YoutubeLink 
     */
    public static function getYoutubeLinkById($iMediaId) {
        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `media` AS `m`
                    WHERE
                        `m`.`mediaId` = ' . db_int($iMediaId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'YoutubeLink');
    }

    /**
     * delete a YoutubeLink
     * @param YoutubeLink $oYoutubeLink
     * @return boolean
     */
    public static function deleteYoutubeLink(YoutubeLink $oYoutubeLink) {

        if ($oYoutubeLink->isDeletable()) {
            $oDb = DBConnections::get();

            $sQuery = 'DELETE FROM `media` WHERE `mediaId` = ' . db_int($oYoutubeLink->mediaId);
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update media Order
     * @param array $aMediaIds 
     */
    public static function updateYoutubeLinkOrder(array $aMediaIds) {

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
