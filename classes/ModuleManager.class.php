<?php

/* Models and managers used by the ModuleManager model */
require_once 'Module.class.php';

class ModuleManager {

    /**
     * get module by moduleId
     * @param int $iModuleId
     * @return Module
     */
    public static function getModuleById($iModuleId) {
        $sQuery = ' SELECT 
                        `m`.*
                    FROM
                        `modules` AS `m`
                    WHERE
                        `m`.`moduleId` = ' . db_int($iModuleId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Module");
    }
    
    /**
     * 
     * @param string $sModuleName
     * @return Module
     */
    public static function getModuleByName($sModuleName) {
        $sQuery = ' SELECT 
                        `m`.*
                    FROM
                        `modules` AS `m`
                    WHERE
                        `m`.`name` = ' . db_str($sModuleName) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Module");        
    }

    /**
     * save Module object
     * @param Module $oModule
     */
    public static function saveModule(Module $oModule) {

        $sQuery = ' INSERT INTO `modules` (
                        `moduleId`,
                        `name`,
                        `linkName`,
                        `showInMenu`,
                        `parentModuleId`,
                        `order`,
                        `created`
                    ) 
                    VALUES (
                        ' . db_int($oModule->moduleId) . ',
                        ' . db_str($oModule->name) . ',
                        ' . db_str($oModule->linkName) . ',
                        ' . db_str($oModule->showInMenu) . ',
                        ' . db_int($oModule->parentModuleId) . ',
                        ' . db_int($oModule->order) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `name`=VALUES(`name`),
                        `linkName`=VALUES(`linkName`),
                        `showInMenu`=VALUES(`showInMenu`),
                        `parentModuleId`=VALUES(`parentModuleId`),
                        `order`=VALUES(`order`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oModule->moduleId === null)
            $oModule->moduleId = $oDb->insert_id;
    }

    /**
     * delete module
     * @param Module $oModule
     * @return Boolean
     * 
     */
    public static function deleteModule(Module $oModule) {
        $oDb = DBConnections::get();

        $sQuery = 'DELETE FROM `modules` WHERE `moduleId` = ' . db_int($oModule->moduleId) . ';';
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }

    /**
     * return modules filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Module
     */
    public static function getModulesByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`m`.`order`' => 'ASC', '`m`.`moduleId`' => 'ASC')) {
        global $oCurrentUser;
        $sFrom = '';
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`active` = 1';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`showInMenu` = 1';
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`um`.`userId` = ' . db_int($oCurrentUser->userId);
            $sFrom .= 'JOIN `usersModules` AS `um` ON `um`.`moduleId` = `m`.`moduleId`';
        }

        // get modules with parenModuleId
        if (isset($aFilter['parentModuleId'])) {
            if ($aFilter['parentModuleId'] === -1) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`parentModuleId` IS NULL';
            } else {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`parentModuleId` = ' . db_int($aFilter['parentModuleId']);
            }
        }

        // get modules with showInMenu
        if (isset($aFilter['showInMenu'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`showInMenu` = ' . db_int($aFilter['showInMenu']);
        }

        // get modules with checkRights
        if (isset($aFilter['checkRights'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`um`.`userId` = ' . db_int($oCurrentUser->userId);
            $sFrom .= 'JOIN `usersModules` AS `um` ON `um`.`moduleId` = `m`.`moduleId`';
        }

        // get modules with checkRights
        if (isset($aFilter['userId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`um`.`userId` = ' . db_int($aFilter['userId']);
            $sFrom .= 'JOIN `usersModules` AS `um` ON `um`.`moduleId` = `m`.`moduleId`';
        }

        // get modules with active
        if (isset($aFilter['active'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`m`.`active` = ' . db_int($aFilter['active']);
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
                        `m`.*
                    FROM
                        `modules` AS `m`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aModules = $oDb->query($sQuery, QRY_OBJECT, "Module");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aModules;
    }

    /**
     * check if module is active
     * @param type $sName
     * @return Module
     */
    public static function isActive($sName) {
        $sQuery = ' SELECT
                        `m`.*
                    FROM
                        `modules` AS `m`
                    WHERE
                        `m`.`name` = ' . db_str($sName) . '
                    AND
                        `m`.`active` = 1
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Module") != null;
    }

    /**
     * update active  by module names
     * @param array $aNames
     * @param type $bActive
     */
    public static function setActiveByNames(array $aNames = array(), $bActive = true) {

        $sNames = '';
        foreach ($aNames AS $sName) {
            $sNames .= ($sNames != '' ? ',' : '') . db_str($sName);
        }

        if (!empty($sNames)) {
            $sQuery = ' UPDATE
                        `modules`
                    SET
                        `active` = ' . db_int($bActive) . '
                    WHERE
                        `name` IN (' . $sNames . ')
                    ;';
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

}

?>