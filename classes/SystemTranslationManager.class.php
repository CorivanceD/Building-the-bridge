<?php

/**
 * Manages all the text labels used in the admin panel
 */
class SystemTranslationManager {

    /**
     * 
     * @param string $iSystemLanguageId
     * @param string $sLabel
     * @return SystemTranslation
     */
    public static function getTranslationByLabel($iSystemLanguageId, $sLabel) {
        $sQuery = ' SELECT
                        `st`.*
                    FROM
                        `systemTranslations` AS `st`
                    WHERE
                        `st`.`systemLanguageId` = ' . db_str($iSystemLanguageId) . '
                    AND
                        `st`.`label` = ' . db_str($sLabel) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'SystemTranslation');
    }

    /**
     * 
     * @param string $iSystemTranslationId
     * @return SystemTranslation
     */
    public static function getTranslationById($iSystemTranslationId) {
        $sQuery = ' SELECT
                        `st`.*
                    FROM
                        `systemTranslations` AS `st`
                    WHERE
                        `st`.`systemTranslationId` = ' . db_str($iSystemTranslationId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'SystemTranslation');
    }

    /**
     * 
     * @param string $iSystemLanguageId
     * @param string $sLabel
     * @param string $sText
     */
    public static function saveTranslation(SystemTranslation $oSystemTranslation) {
        $oDb = DBConnections::get();

        if (empty($oSystemTranslation->systemTranslationId)) {
            $sQuery = ' INSERT INTO `systemTranslations` (
                        `systemLanguageId`,
                        `label`,
                        `text`
                    ) VALUES (
                        ' . db_int($oSystemTranslation->systemLanguageId) . ',
                        ' . db_str($oSystemTranslation->label) . ',
                        ' . db_str($oSystemTranslation->text) . '
                    );';
            $oDb->query($sQuery, QRY_NORESULT);
            $oSystemTranslation->systemTranslationId = $oDb->insert_id;
        } else {
            $sQuery = 'UPDATE
                            `systemTranslations`
                        SET
                            `systemLanguageId` = ' . db_int($oSystemTranslation->systemLanguageId) . ',
                            `label` = ' . db_str($oSystemTranslation->label) . ',
                            `text` = ' . db_str($oSystemTranslation->text) . '
                        WHERE
                            `systemTranslationId` = ' . db_int($oSystemTranslation->systemTranslationId) . '
                        ;';
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

    /**
     * return system translations filtered by a few options
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array SystemTranslation 
     */
    public static function getTranslationsByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`st`.`systemTranslationId`' => 'DESC')) {
        $sFrom = '';
        $sWhere = '';
        $sGroupBy = '';

        // search for systemLanguageId
        if (!empty($aFilter['systemLanguageId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`st`.`systemLanguageId` = ' . db_int($aFilter['systemLanguageId']);
        }

        // search for prefix
        if (!empty($aFilter['prefix'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`st`.`label` LIKE ' . db_str($aFilter['prefix'] . '%');
        }

        // search for label
        if (!empty($aFilter['label'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`st`.`label` LIKE ' . db_str('%' . $aFilter['label'] . '%');
        }

        // search for text
        if (!empty($aFilter['text'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`st`.`text` LIKE ' . db_str('%' . $aFilter['text'] . '%');
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

        $sQuery = ' SELECT ' . ($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '') . '
                        `st`.*
                    FROM
                        `systemTranslations` AS `st`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aSystemTranslations = $oDb->query($sQuery, QRY_OBJECT, "SystemTranslation");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aSystemTranslations;
    }

    /**
     * get all system translations existing and non existing based on the total of unique labels
     * @param int $iSystemLanguageId
     * @param boolean $bShowEmptyOnly
     * @return array
     */
    public static function getFullTranslationForLanguage($iSystemLanguageId, $bShowEmptyOnly = false) {
        $sQuery = ' SELECT
                        DISTINCT `st`.`label`,
                        `stL`.`systemTranslationId`,
                        ' . db_int($iSystemLanguageId) . ' AS `systemLanguageId`,
                        `stL`.`text`
                    FROM
                        `systemTranslations` AS `st`
                    LEFT JOIN
                        (
                            SELECT
                                *
                            FROM
                                `systemTranslations`
                            WHERE
                                `systemLanguageId` = ' . db_int($iSystemLanguageId) . '
                        ) AS `stL` ON `stL`.`label` = `st`.`label`
                    ' . ($bShowEmptyOnly ? 'WHERE `stL`.`text` IS NULL' : '') . '
                    ORDER BY
                        IF(`stL`.`text` IS NULL, 0, 1) ASC,
                        `st`.`label` ASC
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "SystemTranslation");
    }

    /**
     * delete system translation
     * @param SystemTranslation $oSystemTranslation
     * @return boolean
     */
    public static function deleteTranslation(SystemTranslation $oSystemTranslation) {
        $oDb = DBConnections::get();

        $sQuery = "DELETE FROM `systemTranslations` WHERE `systemTranslationId` = " . db_int($oSystemTranslation->systemTranslationId) . ";";
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }

}
?>

