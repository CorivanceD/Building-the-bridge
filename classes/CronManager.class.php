<?php

class CronManager {

    const CRONJOBS_FOLDER = '/admin/cronjobs';
    const LOCK_FOLDER = '/admin/cronjobs/cronlocks';
    const LOG_FOLDER = '/admin/cronjobs/cronlogs';
    const FILE_FOLDER = '/admin/cronjobs/cronfiles';

    /**
     * set cron lock
     * @param string $sLockRef
     * @return boolean
     */
    public static function setCronLock($sLockRef) {
        if (!self::isLocked($sLockRef))
            return touch(self::getLockLocation($sLockRef));
    }

    /**
     * unset cron lock
     * @param string $sLockRef
     * @return boolean
     */
    public static function unsetCronLock($sLockRef) {
        if (self::isLocked($sLockRef))
            return unlink(self::getLockLocation($sLockRef));
    }

    /**
     * check if lock exists
     * @param string $sLockRef
     * @return boolean
     */
    public static function isLocked($sLockRef) {
        if (file_exists(self::getLockLocation($sLockRef))) {
            if (Date::strToDate(filemtime(self::getLockLocation($sLockRef)))->lowerThan(Date::strToDate('now')->addHours(-6))) {
                $oMailMgr = new MailManager();
                $oMailMgr->sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' cronlock te lang actief', 'Cronlock voor: ' . $sLockRef . ' (' . self::getLockLocation($sLockRef) . ') te lang actief');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * generate lock location
     * @param string $sLockRef
     * @return string
     */
    public static function getLockLocation($sLockRef) {
        return DOCUMENT_ROOT . self::LOCK_FOLDER . '/' . md5($sLockRef) . '.lock';
    }

    /**
     * log one line in cron log
     * @param string $sContents 
     */
    public static function log($sLine, $sFileName = 'cron.log') {
        $sFile = DOCUMENT_ROOT . self::LOG_FOLDER . '/' . $sFileName;
        if (file_exists($sFile)) {
            // limit log file to 1000 lines
            $iMaxLines = 5000;
            $aLines = file($sFile);
            if (count($aLines) > $iMaxLines) {
                file_put_contents($sFile, implode('', array_slice($aLines, -$iMaxLines, $iMaxLines)));
            }
        }

        // folder is writable
        if (is_writable(DOCUMENT_ROOT . self::LOG_FOLDER)) {
            $rFile = @fopen($sFile, 'a+');
            $sLine = strftime('%c - ') . $sLine . PHP_EOL;
            @fwrite($rFile, $sLine);
            @fclose($rFile);

            // folder is OK but file already exists and is not writable
            if (file_exists($sFile) && !is_writable($sFile)) {
                Debug::logError('0', 'CronManager kan error niet in log file wegschrijven', __FILE__, __LINE__, 'Vermoedelijk rechten goed zetten: `' . $sFile . '`', Debug::LOG_IN_EMAIL);
            }
        } else {
            Debug::logError('0', 'CronManager kan error niet in log folder wegschrijven', __FILE__, __LINE__, 'Vermoedelijk rechten goed zetten: `' . DOCUMENT_ROOT . self::LOG_FOLDER . '`', Debug::LOG_IN_EMAIL);
        }
    }

}

?>