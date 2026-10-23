<?php

/* Models and managers used by the SettingManager model */
require_once 'Setting.class.php';

class SettingManager {

    const IMAGES_PATH = '/images/settings';

    /**
     * get a setting by settingId
     * @param int $iSettingId
     * @return Setting
     */
    public static function getSettingById($iSettingId) {
        $sQuery = " SELECT
                        `s`.*
                    FROM
                        `settings` AS `s`
                    WHERE `s`.`settingId` = " . db_int($iSettingId) . "
                ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Setting");
    }

    /**
     * get a setting by name
     * @param string $sName
     * @return Setting
     */
    public static function getSettingByName($sName) {
        $sQuery = " SELECT
                        `s`.*
                    FROM
                        `settings` AS `s`
                    WHERE `s`.`name` = " . db_str($sName) . "
                ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Setting");
    }

    /**
     * Get all settings
     * @return Array of Setting objects
     */
    public static function getAllSettings() {
        $sQuery = " SELECT
                        `s`.*
                    FROM
                        `settings` AS `s`
                    ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Setting");
    }

    /**
     * save Setting object
     * @param Setting $oSetting
     */
    public static function saveSetting(Setting $oSetting) {

        $sQuery = ' INSERT INTO `settings` (
                        `settingId`,
                        `name`,
                        `value`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oSetting->settingId) . ',
                        ' . db_str($oSetting->name) . ',
                        ' . db_str($oSetting->value) . ',
                        ' . 'NOW()' . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `value`=VALUES(`value`)
                    ;';

        $oDb = new DBConnection();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oSetting->settingId === null)
            $oSetting->settingId = $oDb->insert_id;
    }

    /**
     * delete setting
     * @param Setting $oSetting
     * @return boolean
     */
    public static function deleteSetting(Setting $oSetting) {

        $oDb = DBConnections::get();

        $sQuery = "DELETE FROM `settings` WHERE `settingId` = " . db_int($oSetting->settingId) . ";";
        $oDb->query($sQuery, QRY_NORESULT);

        return true;
    }

}

?>