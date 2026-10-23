<?php

/* Models and managers used by the FileManager */
require_once 'File.class.php';

class FileManager {

    /**
     * save File object
     * @param File $oFile
     */
    public static function saveFile(File $oFile) {

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
                            ' . db_int($oFile->mediaId) . ',
                            ' . db_str($oFile->link) . ',
                            ' . db_str($oFile->title) . ',
                            ' . db_str($oFile->type) . ',
                            ' . db_int($oFile->online) . ',
                            ' . db_int($oFile->order) . ',
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
        if ($oFile->mediaId === null)
            $oFile->mediaId = $oDb->insert_id;


        # new image
        $sQuery = ' INSERT INTO
                        `files`
                        (
                            `mediaId`,
                            `name`,
                            `mimeType`,
                            `size`
                        )
                        VALUES (
                            ' . db_int($oFile->mediaId) . ',
                            ' . db_str($oFile->name) . ',
                            ' . db_str($oFile->mimeType) . ',
                            ' . db_int($oFile->size) . '
                        )
                        ON DUPLICATE KEY UPDATE
                            `name`=VALUES(`name`),
                            `mimeType`=VALUES(`mimeType`),
                            `size`=VALUES(`size`)
                    ;';

        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * get a File by mediaId
     * @param int $iFileId
     * @return File 
     */
    public static function getFileById($iMediaId) {
        $sQuery = ' SELECT
                        `f`.*,
                        `m`.*
                    FROM
                        `files` AS `f`
                    JOIN
                        `media` AS `m` USING(`mediaId`)
                    WHERE
                        `f`.`mediaId` = ' . db_int($iMediaId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'File');
    }

    /**
     * delete a file
     * @param File $oFile 
     * @return boolean
     */
    public static function deleteFile(File $oFile) {
        
        if ($oFile->isDeletable()) {
            $oDb = DBConnections::get();

            # delete all imageFiles
            if (!empty($oFile->link)) {
                @unlink(DOCUMENT_ROOT . $oFile->link);
            }

            $sQuery = 'DELETE FROM `media` WHERE `mediaId` = ' . db_int($oFile->mediaId);
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update fileOrder
     * @param array $aMediaIds 
     */
    public static function updateFileOrder(array $aMediaIds) {

        # start query, uses on duplicate key
        $sQuery = "";

        # add all fileIds and values
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
