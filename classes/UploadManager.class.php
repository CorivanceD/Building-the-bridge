<?php

/* Models and managers used by the uploadManager */

class UploadManager {

    /**
     * check if the mime-type extension combination is valid
     * @param string $sMime_type
     * @param string $sExtension
     * @return bool 
     */
    public static function isValidMimeExtCombi($sMime_type, $sExtension) {
        $sQuery = ' SELECT
                        COUNT(`me`.`mime_type`) > 0 AS `exists`
                    FROM
                        `mime_extensions` AS me
                    WHERE
                        `mime_type` = ' . db_str($sMime_type) . '
                    AND
                        `extension` = ' . db_str($sExtension) . '
                    LIMIT 0,1
                    ;';
        $oDb = DBConnections::get();

        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT)->exists;
    }

}

?>
