<?php

/* Models and managers used by the Debug model */
require_once 'MailManager.class.php';

/* class for helping with debugging and error handling */

class Debug {

    const LOG_IN_DATABASE = 1; //use database as log media
    const LOG_IN_EMAIL = 2; //use email as log media
    const LOG_IN_FILE = 3; //use file as log media
    const ERROR_OCCURRENCES_LIMIT = 10; // max number of occurrences per error number

    private static $aErrorOccurrences = array(); // array with occured errors in this process. key = error number => value = number of occurrences

    /**
     * log an error to the specified media
     * @param int $iErrorno (error number)
     * @param string $sError (error description)
     * @param string $sFile (error file)
     * @param string $iLine (error on line)
     * @param string $sExtraInfo (extra info)
     * @param int $iHowToLog 
     */

    public static function logError($iErrorno, $sError, $sFile, $iLine, $sExtraInfo = null, $iHowToLog = self::LOG_IN_DATABASE) {
        $iLogMode = self::LOG_IN_DATABASE;
        if (!isset(self::$aErrorOccurrences[$iErrorno])) {
            self::$aErrorOccurrences[$iErrorno] = 0;
        }
        self::$aErrorOccurrences[$iErrorno] ++;


        $sBackTrace = '';

        $iT = -1;
        foreach (debug_backtrace() AS $aTrace) {
            //do not write first line, this is the logError function
            if ($iT == -1) {
                $iT++;
                continue;
            }

            /* add line to backtrace */
            $sBackTrace .= "#" . $iT . " " . (isset($aTrace['class']) ? $aTrace['class'] : '') . (isset($aTrace['type']) ? $aTrace['type'] : '') . (isset($aTrace['function']) ? $aTrace['function'] : '') . "(";

            if (!empty($aTrace['args'])) {
                $iT2 = 0;
                foreach ($aTrace['args'] AS $sArg) {
                    if (is_string($sArg)) {
                        $sBackTrace .= "'$sArg'";
                    } elseif (is_bool($sArg)) {
                        $sBackTrace .= $sArg ? "TRUE" : "FALSE";
                    } elseif (is_numeric($sArg)) {
                        $sBackTrace .= $sArg;
                    } elseif (is_null($sArg)) {
                        $sBackTrace .= "NULL";
                    } elseif (is_array($sArg)) {
                        $sBackTrace .= "Array()";
                    } elseif (is_object($sArg)) {
                        $sBackTrace .= "Object()";
                    } else {
                        $sBackTrace .= $sArg;
                    }
                    if ($iT2 < count($aTrace['args']) - 1) {
                        $sBackTrace .= ", ";
                    }
                    $iT2++;
                }
            }
            $sBackTrace .= ") called at [" . (isset($aTrace['file']) ? $aTrace['file'] : '') . ":" . (isset($aTrace['line']) ? $aTrace['line'] : '') . "]<br />\r\n";
            $iT++;
        }

        if (self::$aErrorOccurrences[$iErrorno] <= self::ERROR_OCCURRENCES_LIMIT) {
            switch ($iLogMode) {
                case self::LOG_IN_DATABASE:
                    self::writeErrorToDatabase($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo);
                    break;
                case self::LOG_IN_EMAIL:
                    self::emailError($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo);
                    break;
                case self::LOG_IN_FILE:
                    self::writeErrorToFile($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo);
                    break;
            }
        }
    }

    /**
     * save an error in the database
     * @param int $iErrorno (error number)
     * @param string $sError (error description)
     * @param string $sFile (file name)
     * @param string $iLine (line number)
     * @param string $sBackTrace (backtrace of the error)
     * @param string $sExtraInfo (extra info)* 
     */
    private static function writeErrorToDatabase($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo) {

        $oDb = DBConnections::get();

        /* not yet a logging table?? make one */
        $sQuery = "CREATE TABLE IF NOT EXISTS error_logging (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            errno INT NULL ,
            error VARCHAR( 255 ) NOT NULL ,
            extraInfo TEXT NULL ,
            file VARCHAR( 255 ) NOT NULL ,
            line INT NOT NULL ,
            timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            backTrace TEXT NOT NULL ,
            serverVariables TEXT NOT NULL
            ) ENGINE = InnoDB;";

        /* save error in database if possible */
        $oDb->query($sQuery, QRY_NORESULT, "stdClass", true);

        // maximize errors to 1000 records
        $sQuery = ' DELETE FROM `error_logging`
                        WHERE `id` NOT IN (
                          SELECT `id`
                          FROM (
                            SELECT `id`
                            FROM `error_logging`
                            ORDER BY `id` DESC
                            LIMIT 9999
                          ) `el`
                        );
                    ;';
        $oDb->query($sQuery, QRY_NORESULT, "stdClass", true);

        /* save error details in database */
        $sQuery = " INSERT INTO `error_logging` ( `errno`, `error`, `extraInfo`, `file`, `line`, `backTrace`, `serverVariables`, `timestamp` )
                        VALUES (
                        " . db_int($iErrorno) . ",
                        " . db_str($sError) . ",
                        " . db_str($sExtraInfo) . ",
                        " . db_str($sFile) . ",
                        " . db_int($iLine) . ",
                        " . db_str($sBackTrace) . ",
                        " . db_str(($_SERVER ? _d($_SERVER, true, true) : '')) . ",
                        NOW()
            );";

        $oDb->query($sQuery, QRY_NORESULT, null, true, true); // skip all errors true and skip all error logging false
    }

    /**
     * send error per email
     * @param int $iErrorno (error number)
     * @param string $sError (error description)
     * @param string $sFile (file name)
     * @param string $iLine (line number)
     * @param string $sBackTrace (backtrace of the error)
     * @param string $sExtraInfo (extra info)* 
     */
    private static function emailError($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo) {

        $sMailBody = "<p>Developer: " . DEVELOPER_NAME . "</p>";
        $sMailBody .= "<p>Errorno: " . $iErrorno . "</p>";
        $sMailBody .= "<p>Error: " . $sError . "</p>";
        $sMailBody .= "<p>File: " . $sFile . "</p>";
        $sMailBody .= "<p>Line: " . $iLine . "</p>";
        $sMailBody .= "<p>Timestamp: " . legacy_strftime("%c") . "</p>";
        $sMailBody .= "<p>Backtrace: <br />\n" . $sBackTrace . "</p>";
        $sMailBody .= "<p>Extra info: <br />\n" . $sExtraInfo . "</p>";

        // add server vars
        $sMailBody .= "<p>Server Vars: <br />\n";
        $sMailBody .= ($_SERVER ? _d($_SERVER, true, true) : '');

        // put all post vars in a variable$sMailBody .= "</p>";
        $sMailBody .= "<p>Post Vars: <br />\n";
        $sMailBody .= ($_POST ? _d($_POST, true, true) : '');
        $sMailBody .= "</p>";

        $bResult = MailManager::sendMail(DEFAULT_ERROR_EMAIL, "Error " . $iErrorno . " @ " . CLIENT_URL . ' || ' . DEVELOPER_NAME, $sMailBody, null, null, null, null, null, null, false); //do not log email error in database

        if (!$bResult) {
            self::writeErrorToFile("", "Error erroremail could not be send", __FILE__, __LINE__, "", $sMailBody);
        }
    }

    /**
     * log error in file
     * @param int $iErrorno (error number)
     * @param string $sError (error description)
     * @param string $sFile (file name)
     * @param string $iLine (line number)
     * @param string $sBackTrace (backtrace of the error)
     * @param string $sExtraInfo (extra info)* 
     */
    private static function writeErrorToFile($iErrorno, $sError, $sFile, $iLine, $sBackTrace, $sExtraInfo) {
        $sContents = '';
        $sContents .= "[" . legacy_strftime("%c") . "] [" . $iErrorno . "] " . $sError . " \r\n";
        $sContents .= "File: " . $sFile . ":" . $iLine . "\r\n";

        if ($sExtraInfo) {
            $sContents .= "## BEGIN Extra info: ##\r\n" . $sExtraInfo . "\r\n";
            $sContents .= "## END Extra info ##\r\n";
        }

        if ($sBackTrace) {
            $sContents .= "## BEGIN BackTrace: ##\r\n" . $sBackTrace;
            $sContents .= "## END Backtrace ##";
        }
        self::writeToFile($sContents, 'errorLog.txt');
    }

    /**
     * log some text in file
     * @param int $iErrorno (error number)
     * @param string $sError (error description)
     * @param string $sFile (file name)
     * @param string $iLine (line number)
     * @param string $sBackTrace (backtrace of the error)
     * @param string $sExtraInfo (extra info)* 
     */
    public static function writeToFile($sContents, $sFileName = 'defaultLog.txt', $sLogFolder = ASIDE_LOG_FOLDER) {
        $sFile = $sLogFolder . "/" . $sFileName;
        if (file_exists($sFile)) {
            // limit log file to 1000 lines
            $iMaxLines = 10000;
            $aLines = file($sFile);
            if (is_array($aLines) && count($aLines) > $iMaxLines) {
                file_put_contents($sFile, implode('', array_slice($aLines, -$iMaxLines, $iMaxLines)));
            }
        }

        // folder is writable
        if (is_writable($sLogFolder)) {
            $rFile = @fopen($sFile, 'a+');
            $sContents .= "\r\n#-----------------#\r\n";
            @fwrite($rFile, $sContents);
            @fclose($rFile);

            // folder is OK but file already exists and is not writable
            if (file_exists($sFile) && !is_writable($sFile)) {
                self::emailError('0', 'Debug kan error niet in log file wegschrijven', __FILE__, __LINE__, '', 'Vermoedelijk rechten goed zetten: `' . $sFile . '`');
            }
        } else {
            self::emailError('0', 'Debug kan error niet in log folder wegschrijven', __FILE__, __LINE__, '', 'Vermoedelijk rechten goed zetten: `' . $sLogFolder . '`');
        }
    }

}

?>