<?

/* Models and managers used by the FormBackup model */
require_once 'FormBackup.class.php';

class FormBackupManager {

    /**
     * Get all FormBackup objects
     * @return Array of FormBackup objects
     */
    public static function getAllFormBackups() {
        $sQuery = ' SELECT
                        `fb`.*
                    FROM
                        `formBackups` AS `fb`
                    ;';

        $oDb = DBConnections::get();
        $aFormBackups = $oDb->query($sQuery, QRY_OBJECT, "FormBackup");
        return $aFormBackups;
    }

    /**
     * get FormBackup by formBackupId
     * @param int $iFormBackupId
     * @return FormBackup
     */
    public static function getFormBackupById($iFormBackupId) {
        $sQuery = ' SELECT 
                        `fb`.*
                    FROM
                        `formBackups` AS `fb`
                    WHERE
                        `fb`.`formBackupId` = ' . db_int($iFormBackupId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "FormBackup");
    }

    /**
     * make a form backup and save this
     * @param string $sName form name (reference name)
     * @param array $aPOST post array with form data
     * @param string $sExtraInfo (optional) extra info for backup
     */
    public static function makeFormBackup($sName, array $aPOST, $sExtraInfo = null) {
        $oFormBackup = new FormBackup();
        $oFormBackup->name = $sName;
        $oFormBackup->serializedFormData = serialize($aPOST);
        $oFormBackup->extraInfo = $sExtraInfo;
        self::saveFormBackup($oFormBackup);
    }

    /**
     * save FormBackup object
     * @param FormBackup $oFormBackup
     */
    private static function saveFormBackup(FormBackup $oFormBackup) {

        $sQuery = ' INSERT INTO `formBackups` (
                        `formBackupId`,
                        `name`,
                        `serializedFormData`,
                        `extraInfo`,
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oFormBackup->formBackupId) . ',
                        ' . db_str($oFormBackup->name) . ',
                        ' . db_str($oFormBackup->serializedFormData) . ',
                        ' . db_str($oFormBackup->extraInfo) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `name`=VALUES(`name`),
                        `serializedFormData`=VALUES(`serializedFormData`),
                        `extraInfo`=VALUES(`extraInfo`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oFormBackup->formBackupId === null)
            $oFormBackup->formBackupId = $oDb->insert_id;
    }

    /**
     * return formBackups filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Module
     */
    public static function getFormBackupsByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`fb`.`formBackupId`' => 'DESC')) {
        $sFrom = '';
        $sWhere = '';

        // search for term
        if (!empty($aFilter['q'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '(
                    `fb`.`name` LIKE ' . db_str('%' . $aFilter['q'] . '%') . '
                OR 
                    `fb`.`serializedFormData` LIKE ' . db_str('%' . $aFilter['q'] . '%') . '
                OR
                    `fb`.`extraInfo` LIKE ' . db_str('%' . $aFilter['q'] . '%') . ')';
        }

        // search for date
        if (!empty($aFilter['created'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`fb`.`created` LIKE ' . db_str(Date::strToDate($aFilter['created'])->format('%Y-%m-%d') . '%');
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
                        `fb`.*
                    FROM
                        `formBackups` AS `fb`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aFormBackups = $oDb->query($sQuery, QRY_OBJECT, "FormBackup");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aFormBackups;
    }

}

?>