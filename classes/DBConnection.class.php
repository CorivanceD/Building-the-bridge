<?php

/* Models and managers used by the DBConnection model */
require_once 'Debug.class.php';

/* define db variabelen */
define("QRY_ARRAY", 1); //return array with arrays
define("QRY_ASSOC", 2); //return unqiue array with associative arrays
define("QRY_OBJECT", 3); //return unique object
define("QRY_UNIQUE_OBJECT", 4); //return unique object
define("QRY_UNIQUE_ARRAY", 5); //return unique array
define("QRY_NORESULT", 6); //return nothing

/*
 * DBConnection extends mysql standard PHP class mysqli
 */

class DBConnection extends mysqli {

    /**
     * constructor
     * @param string $sHost
     * @param string $sUser
     * @param string $sPass
     * @param string $sDatabase
     */
    function __construct($sHost = DB_HOST, $sUser = DB_USER, $sPass = DB_PASS, $sDatabase = DB_DATABASE) {
        mysqli_report(MYSQLI_REPORT_OFF); // keep manual error checks working (PHP 8.1+ throws by default)
        @parent::__construct($sHost, $sUser, $sPass, $sDatabase);

        /* error in connection, send email to error address */
        if ($this->connect_errno) {
            Debug::logError($this->connect_errno, $this->connect_error, __FILE__, __LINE__, null, Debug::LOG_IN_EMAIL);
        } else {
            $this->set_charset('utf8');
        }
    }

    /**
     * execute a query with and return specified result
     * @param string $sQuery
     * @param string $sReturnformat (QRY_OBJECT, QRY_ARRAY, QRY_ASSOC, QRY_UNIQUE, QRY_NORESULT)
     * @param boolean $sClassName (class name for object returning)
     * @param boolean $sSkipErrorMessage (true for all errors, false for no errors, array for some errors)
     * @param boolean $bSkipErrorLogging (true for all errors, false for no errors, array for some errors)
     * @return mixed 1 array, 1 array with arrays, 1 array with objects, 1 object, mysqlresult, nothing
     */
    #[\ReturnTypeWillChange]
    function query($sQuery, $sReturnformat = null, $sClassName = "stdClass", $sSkipErrorMessage = false, $bSkipErrorLogging = false) {

        $iQSMT = microtime(true);
        $oResult = parent::query($sQuery);
        $iQEMT = microtime(true);

        if (DEBUG && $this->error) {
            _d($this->error);
            _d($sQuery);
            _d("Query time: " . ($iQEMT - $iQSMT) . " sec");
        } elseif (DEBUG && DEBUG_QUERIES) {
            _d($sQuery);
            _d("Query time: " . ($iQEMT - $iQSMT) . " sec");
        }

        if ($this->error) {
            # check for skipping errors (all or selection by array)
            if (((is_array($bSkipErrorLogging) && !in_array($this->errno, $bSkipErrorLogging)) || $bSkipErrorLogging === false) && !DEBUG) {

                /* log error in database and email */
                Debug::logError($this->errno, $this->error, __FILE__, __LINE__, $sQuery, Debug::LOG_IN_DATABASE);
                Debug::logError($this->errno, $this->error, __FILE__, __LINE__, $sQuery, Debug::LOG_IN_EMAIL);
            }

            # check for displaying error message
            if ((is_array($sSkipErrorMessage) && !in_array($this->errno, $sSkipErrorMessage)) || $sSkipErrorMessage === false)
                die("Error DQF"); // database query faillure
            return null;
        }

        switch ($sReturnformat) {
            # geeft object terug als standaard class
            case QRY_OBJECT:
                $aArr = array();
                while ($oRow = $oResult->fetch_object($sClassName)) {
                    $aArr[] = $oRow;
                }
                return $aArr;
                break;

            # returns associated array $key => $value
            case QRY_ASSOC:
                $aArr = array();
                while ($aRow = $oResult->fetch_assoc()) {
                    $aArr[] = $aRow;
                }
                return $aArr;
                break;

            # returns an array with result like $key => $value AND $index => $value
            case QRY_ARRAY:
                $aArr = array();
                while ($aRow = $oResult->fetch_array()) {
                    $aArr[] = $aRow;
                }
                return $aArr;
                break;

            # returns 1 unique object
            case QRY_UNIQUE_OBJECT:
                if ($oResult) {
                    if ($oResult->num_rows == 0) {
                        return null;
                    }
                    if ($oResult->num_rows == 1) {
                        return $oResult->fetch_object($sClassName);
                    }
                    Debug::logError('', 'Query returned more than 1 result for unique object', __FILE__, __LINE__, $sQuery);
                    Debug::logError('', 'Query returned more than 1 result for unique object', __FILE__, __LINE__, $sQuery, Debug::LOG_IN_EMAIL);
                die("Query gaf meer dan 1 resultaat terug");
                break;
                }else{
                    Debug::logError('', 'No Result found', __FILE__, __LINE__, $sQuery);
                }
            # returns 1 unique array
            case QRY_UNIQUE_ARRAY:
                if ($oResult->num_rows == 0)
                    return null;
                if ($oResult->num_rows == 1)
                    return $oResult->fetch_assoc();
                Debug::logError('', 'Query returned more than 1 result for unique array', __FILE__, __LINE__, $sQuery);
                Debug::logError('', 'Query returned more than 1 result for unique array', __FILE__, __LINE__, $sQuery, Debug::LOG_IN_EMAIL);
                die("Query gaf meer dan 1 resultaat terug");
                break;

            # returns null
            case QRY_NORESULT:
                return null;
                break;
        }

        return $oResult;
    }

    public function __destruct() {
        $this->close();
    }

}

?>