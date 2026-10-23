<?php

class sysTranslations {

    private static $systemTranslations = null;
    private static $systemLanguageId = null;

    /**
     * Get the translation for a text
     * 
     * @param string $sLabel
     * @return string
     */
    public static function get($sLabel, $sList = 'all') {

        /** start language stuff * */
        // system language not set, try to get from session
        if (empty(self::$systemLanguageId)) {
            self::$systemLanguageId = http_session('systemLanguageId', null);
        }

        // system language still not set, get from user
        if (empty(self::$systemLanguageId)) {
            $oCurrentUser = http_session('oCurrentUser', false);
            if ($oCurrentUser)
                self::$systemLanguageId = $oCurrentUser->systemLanguageId;
            else
                self::$systemLanguageId = SystemLanguage::default_languageId; // default languageId

            $_SESSION['systemLanguageId'] = self::$systemLanguageId; // set in session for next request
        }

        /** start translations stuff * */
        // system translations not set, do set
        if (empty(self::$systemTranslations[$sList])) {
            self::setSystemTranslations($sList);
        }

        // translations should be set by now so try to get text by label
        if (array_key_exists($sLabel, self::$systemTranslations[$sList]))
            return self::$systemTranslations[$sList][$sLabel];

        return '[' . $sLabel . ']';
    }

    /**
     * set system translations, first from session and otherwise from database
     * @param string $sList
     */
    private static function setSystemTranslations($sList = 'all') {
        // system translations are not set, get from session
        self::$systemTranslations = http_session('systemTranslations', null);

        // system translations still not set, get from database
        if (empty(self::$systemTranslations[$sList])) {
            if (empty(self::$systemTranslations)) {
                self::$systemTranslations = array();
            }
            self::$systemTranslations[$sList] = array();

            switch ($sList) {
                case 'all':
                    $aSystemTranslations = SystemTranslationManager::getTranslationsByFilter(array('systemLanguageId' => self::$systemLanguageId));
                    break;
                case 'js':
                    $aSystemTranslations = SystemTranslationManager::getTranslationsByFilter(array('systemLanguageId' => self::$systemLanguageId, 'prefix' => 'js_'));
                    break;
                default:
                    $aSystemTranslations = array();
                    break;
            }

            foreach ($aSystemTranslations AS $oSystemTranslation) {
                self::$systemTranslations[$sList][$oSystemTranslation->label] = $oSystemTranslation->text;
            }
            $_SESSION['systemTranslations'] = self::$systemTranslations;
        }
    }

    /**
     * used for javascript translations
     * @param string $sPrefix
     */
    public static function getTranslations($sList) {
        if (empty(self::$systemTranslations[$sList]))
            self::setSystemTranslations($sList);
        return self::$systemTranslations[$sList];
    }
    
    /**
     * reset system translations to get new ones from database
     */
    public static function reset() {
        self::$systemLanguageId = null;
        self::$systemTranslations = null;
        unset($_SESSION['systemLanguageId']);
        unset($_SESSION['systemTranslations']);
    }

}

?>