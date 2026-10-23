<?php

/**
 * Manages all the languages used in the admin panel
 */
class SystemLanguageManager {

    /**
     * 
     * @param string $iSystemLanguageId
     * @return SystemTranslation
     */
    public static function getLanguageById($iSystemLanguageId) {
        $sQuery = ' SELECT
                        `sl`.*
                    FROM
                        `systemLanguages` AS `sl`
                    WHERE
                        `sl`.`systemLanguageId` = ' . db_str($iSystemLanguageId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'SystemLanguage');
    }

    /**
     * return system languages filtered by a few options
     * @param array $aFilter filter properties (checkOnline)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array SystemLanguage 
     */
    public static function getLanguagesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`sl`.`systemLanguageId`' => 'ASC')) {
        $sFrom = '';
        $sWhere = '';
        $sGroupBy = '';

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
                        `sl`.*
                    FROM
                        `systemLanguages` AS `sl`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aSystemLanguages = $oDb->query($sQuery, QRY_OBJECT, "SystemLanguage");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aSystemLanguages;
    }

}
?>

