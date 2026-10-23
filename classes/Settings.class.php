<?php

class Settings {

    private static $aSettings = null;

    /**
     * get setting, get all if not yet done
     * @param string $sName
     * @return mixed
     */
    public static function get($sName, $bForceQuery = false) {
        if (self::$aSettings === null || $bForceQuery) {
            $aSettings = array();
            foreach (SettingManager::getAllSettings() AS $oSetting) {
                $aSettings[$oSetting->name] = $oSetting->value;
            }
            self::$aSettings = $aSettings;
        }

        if (array_key_exists($sName, self::$aSettings)) {
            return self::$aSettings[$sName];
        }
        die('unknown setting: `' . $sName . '`');
    }

}

?>