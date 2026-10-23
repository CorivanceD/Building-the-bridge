<?

/* Models and managers used by the TemplateManager model */
require_once 'Template.class.php';

class TemplateManager {

    /**
     * Get all templateGroups
     * @return Array with arrays with templateGroup Data
     */
    public static function getAllTemplateGroups() {
        $sQuery = ' SELECT
                        `tg`.*
                    FROM
                        `templateGroups` AS `tg`
                    ORDER BY
                        `tg`.`templateGroupName` ASC                     
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'TemplateGroup');
    }

    /**
     * get template by templateId
     * @param int $iTemplateId
     * @return Template
     */
    public static function getTemplateById($iTemplateId) {
        $sQuery = ' SELECT 
                        `t`.*
                    FROM
                        `templates` AS `t`
                    WHERE
                        `t`.`templateId` = ' . db_int($iTemplateId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Template");
    }

    /**
     * get template by name
     * @param int $sName
     * @return Template
     */
    public static function getTemplateByName($sName) {
        $sQuery = ' SELECT 
                        `t`.*
                    FROM
                        `templates` AS `t`
                    WHERE
                        `t`.`name` = ' . db_str($sName) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Template");
    }

    /**
     * get unique templateTypes
     * @return Template
     */
    public static function getUniqueTemplateTypes() {
        $sQuery = ' SELECT DISTINCT
                        `t`.`type`
                    FROM
                        `templates` AS `t`
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Template");
    }

    
    /**
     * get templateGroup by templateGroupId
     * @param int $iTemplateGroupId
     * @return TemplateGroup
     */
    public static function getTemplateGroupById($iTemplateGroupId) {
        $sQuery = ' SELECT 
                        `tg`.*
                    FROM
                        `templateGroups` AS `tg`
                    WHERE
                        `tg`.`templateGroupId` = ' . db_int($iTemplateGroupId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "TemplateGroup");
    }

    /**
     * return templates filtered by a few options
     * @param array $aFilter filter properties (description)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Template objects 
     */
    public static function getTemplatesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`t`.`templateGroupId`' => 'ASC', '`t`.`type`' => 'ASC', '`t`.`description`' => 'ASC')) {

        $sWhere = '';
        $sFrom = '';
        # search for description
        if (!empty($aFilter['description'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`t`.`description` LIKE ' . db_str('%' . $aFilter['description'] . '%');
        }
        
        # search by type
        if (!empty($aFilter['type'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`t`.`type` = ' . db_str($aFilter['type']);
        }
        
        # search by templateGroupId
        if (!empty($aFilter['templateGroupId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`t`.`templateGroupId` = ' . db_int($aFilter['templateGroupId']);
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

        $sQuery = ' SELECT SQL_CALC_FOUND_ROWS
                        `t`.*
                    FROM
                        `templates` AS `t`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aTemplates = $oDb->query($sQuery, QRY_OBJECT, "Template");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
        return $aTemplates;
    }

    /**
     * save Template object
     * @param Template $oTemplate
     */
    public static function saveTemplate(Template $oTemplate) {

        $sQuery = ' INSERT INTO `templates` (
                        `templateId`,
                        `description`,
                        `name`,
                        `templateGroupId`,
                        `type`,
                        `subject`,
                        `template`,
                        `editable`,
                        `deletable`,
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oTemplate->templateId) . ',
                        ' . db_str($oTemplate->description) . ',
                        ' . db_str($oTemplate->name) . ',
                        ' . db_int($oTemplate->templateGroupId) . ',
                        ' . db_str($oTemplate->type) . ',
                        ' . db_str($oTemplate->subject) . ',
                        ' . db_str($oTemplate->template) . ',
                        ' . db_int($oTemplate->getEditable()) . ',
                        ' . db_int($oTemplate->getDeletable()) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `description`=VALUES(`description`),
                        `name`=VALUES(`name`),
                        `templateGroupId`=VALUES(`templateGroupId`),
                        `type`=VALUES(`type`),
                        `subject`=VALUES(`subject`),
                        `template`=VALUES(`template`),
                        `editable`=VALUES(`editable`),
                        `deletable`=VALUES(`deletable`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oTemplate->templateId === null)
            $oTemplate->templateId = $oDb->insert_id;
    }

    /**
     * delete template
     * @param Template $oTemplate
     * @return Boolean
     */
    public static function deleteTemplate(Template $oTemplate) {
        if ($oTemplate->isDeletable()) {
            $sQuery = 'DELETE FROM `templates` WHERE `templateId` = ' . db_int($oTemplate->templateId) . ';';
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        } else
            return false;
    }

}

?>