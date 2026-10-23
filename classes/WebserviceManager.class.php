<?php

abstract class WebserviceManager {

    const FILES_PATH = '/private_files/webservices';
    const CACHE_PATH = '/private_files/webservices/cache';

    protected static $name = null; // name of the API (see reference API module)
    protected static $websiteId = null; // website id
    protected static $token = null; // key for creating hash
    protected static $url = null; // url of the webservice
    protected static $data = null; // request data
    protected static $call = null; // requested call
    protected static $timestamp = null; // request timestamp
    protected static $responseData = null; // response data

    /**
     * do data call to get or post data
     * @param string $sCall
     * @param string $sData
     * @param array $aValidHTTPCodes
     * @return boolean
     */

    private static function doCall($sCall, $sData = null) {
        // check if API name is filled
        if (empty(static::$name)) {
            self::triggerError('API name is missing');
            return self::triggerError('API name is missing');
        }

        // check and set website id
        if (empty(static::$websiteId)) {
            static::$websiteId = Settings::get('webservices_websiteId');
            if (empty(static::$websiteId)) {
                return self::triggerError('website id missing');
            }
        }

        // check and set token
        if (empty(static::$token)) {
            self::$token = Settings::get('webservices_token');
            if (empty(static::$token)) {
                return self::triggerError('token missing');
            }
        }

        // set call
        static::$call = $sCall;
        // check call
        if (empty(static::$call)) {
            return self::triggerError('call missing');
        }

        // check and set url
        if (empty(static::$url)) {
            self::$url = Settings::get('webservices_url');
            if (empty(static::$url)) {
                return self::triggerError('URL missing');
            }
        }

        // set timestamp
        static::$timestamp = time();

        // set data
        static::$data = json_encode($sData);
        if (static::$data === false) {
            return self::triggerError('invalid data input');
        }

        // set full request url
        $sURL = static::$url . static::$name . '/' . $sCall;
        
        $rCURL = curl_init($sURL);
        curl_setopt($rCURL, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($rCURL, CURLOPT_POST, TRUE);
        curl_setopt($rCURL, CURLOPT_POSTFIELDS, static::$data);
        curl_setopt($rCURL, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authentication: ' . static::$websiteId . ':' . self::generateHash(static::$timestamp, static::$data),
            'Timestamp: ' . static::$timestamp,
        ));

        // Return response instead of printing.
        curl_setopt($rCURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rCURL, CURLOPT_VERBOSE, 1);
        curl_setopt($rCURL, CURLOPT_HEADER, 1);

        // Send request.
        $sResult = curl_exec($rCURL);
        $iHTTPCode = curl_getinfo($rCURL, CURLINFO_HTTP_CODE);
        
        // check if HTTP code is right
        if ($iHTTPCode !== 200) {
            return self::triggerError('invalid http request code `' . $iHTTPCode . '` !== 200');
        }

        // Then, after your curl_exec call:
        $iHeaderSize = curl_getinfo($rCURL, CURLINFO_HEADER_SIZE);
        $sHeaders = substr($sResult, 0, $iHeaderSize);
        $sBody = substr($sResult, $iHeaderSize);

        $aHeaderLines = explode(PHP_EOL, $sHeaders);
        foreach ($aHeaderLines AS $sHeaderLine) {
            $sHeaderLine = trim($sHeaderLine);
            if (preg_match('#^Authentication: ?(.+):(.+)#', $sHeaderLine, $aMatches)) {
                $iReturnWebsiteId = $aMatches[1];
                $sReturnHash = $aMatches[2];
            }
            if (preg_match('#^Timestamp: ?(.+)#', $sHeaderLine, $aMatches)) {
                $iReturnTimestamp = $aMatches[1];
            }
        }

        if (empty($iReturnWebsiteId) || empty($sReturnHash) || empty($iReturnTimestamp)) {
            return self::triggerError('empty websiteId, hash or timestamp');
        }

        if (Date::strToDate($iReturnTimestamp)->minutesDiff(new Date()) > 5) {
            return self::triggerError('timestamp to old > 5 minutes');
        }

        if (!self::isHashValid($sReturnHash, $iReturnTimestamp, $sBody)) {
            return self::triggerError('return hash invalid');
        }

        curl_close($rCURL);
        static::$responseData = $sBody;
        $aResponseData = self::getResponseDataArray();

        if (empty($aResponseData)) {
            return self::triggerError('error in response data');
        }

        if ($aResponseData['errornr'] !== 0) {
            if (!empty($aResponseData['message'])) {
                $sMessage = $aResponseData['message'];
                return self::triggerError($sMessage);
            } else {
                $sMessage = '';
            }
        }

        return true;
    }

    /**
     * generate seal
     * @param int $iTimestamp
     * @param string $sData
     * @return string
     */
    private static function generateHash($iTimestamp, $sData) {
        return hash_hmac('sha256', static::$websiteId . $iTimestamp . mb_strlen($sData, '8bit'), static::$token);
    }

    /**
     * check if hash is valid
     * @return boolean
     */
    private static function isHashValid($sHash, $iTimestamp, $sData) {
        return $sHash === self::generateHash($iTimestamp, $sData);
    }

    /**
     * get data as array
     * @return array
     */
    protected static function getResponseDataArray() {
        return json_decode(static::$responseData, true);
    }

    /**
     * return error message and optionally create log record for debugging
     * @param int $iErrornr
     * @param string $sMessage
     * @param string $sLogMessage (special message for internal use, optional)
     */
    protected static function triggerError($sMessage) {
        $sExtraInfo = '';
        $sExtraInfo .= 'Website id: `' . static::$websiteId . '`<br />';
        $sExtraInfo .= 'Webservice: `' . static::$name . '`<br />';
        $sExtraInfo .= 'Call: `' . static::$call . '`<br />';
        $sExtraInfo .= 'Data: `' . static::$data . '`<br />';
        Debug::logError('0', 'Error in webservice `' . static::$name . '`: `' . $sMessage . '`', __FILE__, __LINE__, $sExtraInfo, Debug::LOG_IN_EMAIL);
        return false;
    }

    /**
     * get data
     * @param string $sCall
     * @param string $sData
     * @return array
     */
    protected static function getData($sCall, $sData = null) {
        if (static::doCall($sCall, $sData)) {
            $aResponseData = static::getResponseDataArray();
            if (isset($aResponseData['data'])) {
                return $aResponseData['data'];
            }
        }
        return null;
    }

}

?>