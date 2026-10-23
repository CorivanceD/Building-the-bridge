<?php

/**
 * Basic functions for all needs
 * @param String $sClassName name of class that should be autoloaded
 */
/* auto load class by name */

function autoloadClass($sClassName) {

    $sFileName = CLASSES_FOLDER . "/" . $sClassName . ".class.php";
    if (file_exists($sFileName))
        include_once($sFileName);
}

/**
 * Do the same as mysql_real_escape_string
 * @param String $mValue (waarde om te escapen)
 * @param Boolean $bEMptyString2NULL if value is empty string, return 'NULL'
 * @return String escaped string
 */
function db_str($mValue, $bEmptyString2NULL = true) {

    if ($mValue === null)
        return 'NULL';
    elseif ($mValue === '' && $bEmptyString2NULL)
        return 'NULL';
    else
        return "'" . str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a", "\x00"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z', ''), trim($mValue)) . "'";
}

/**
 * Try to make a real integer of this value, else return null
 * @param Int $iValue (value to parse)
 * @return Int or null
 */
function db_int($iValue) {
    if ($iValue === null || $iValue === "") {
        return "NULL";
    } elseif (is_numeric($iValue)) {
        return (int) $iValue;
    } else {
        return null;
    }
}

/**
 * Make value a real float/double
 * @param Float $fValue (value to parse)
 * @return Int or null
 */
function db_deci($fValue) {
    if ($fValue === null || $fValue === "") {
        return "NULL";
    } else {
        # remove ,-
        $fValue = preg_replace("#([0-9])(,-)$#", "$1.00", $fValue);

        # remove .-
        $fValue = preg_replace("#([0-9])(\.-)$#", "$1.00", $fValue);

        # remove all dashes at the end
        $fValue = preg_replace("#-$#", "", $fValue);

        # komma to decimal sign
        $fValue = preg_replace("#,#", ".", $fValue);

        # remove thousands sign
        $fValue = preg_replace("#\.([0-9]{3})#", "$1", $fValue);

        # If it is a float or a numeric value, make decimal
        if (is_float($fValue) || is_numeric($fValue)) {
            return number_format((float) $fValue, 2, '.', '');
        } else {
            return null;
        }
    }
}

/**
 * Try to return a date in the right format
 * @param String $sDate (value to parse)
 * @param Boolean $bOnlyReturnDate return a date without the time
 * @return String or null
 */
function db_date($sDate, $bOnlyReturnDate = false) {
    if ($sDate === null || $sDate === "") {
        return "NULL";
    }
    # Covert format from d-m-Y to Y-m-d
    if (preg_match("#^\d{1,2}[\/\-]{1}\d{1,2}[\/\-]{1}\d{4}#", $sDate)) {
        $sDate = preg_replace("#^(\d{1,2})[\/\-]{1}(\d{1,2})[\/\-]{1}(\d{4})#", "$3-$2-$1", $sDate);
    }
    # Format is Y-m-d so this is ok
    if (preg_match("#^\d{4}[\/\-]{1}\d{1,2}[\/\-]{1}\d{1,2}#", $sDate)) {
        if ($bOnlyReturnDate) {
            $sDate = preg_replace("#^(\d{4}[\/\-]{1}\d{1,2}[\/\-]{1}\d{1,2})(.*)$#", "$1", $sDate);
        }
        return "'" . trim($sDate) . "'";
    } else {
        return null;
    }
}

/**
 * Dump variables to the screen or return result
 * @param string $mValue value to dump
 * @param boolean $bShowHTML do uses htmlentities
 * @param boolean $sReturnResult return value rather than printing it
 */
function _d($mValue, $bShowHTML = true, $sReturnResult = false) {

    $sDump = "<pre>";
    if ($bShowHTML === true) {
        $sDump .= htmlentities(print_r($mValue, 1));
    } else {
        $sDump .= print_r($mValue, 1);
    }
    $sDump .= "</pre>";

    if ($sReturnResult) {
        return $sDump;
    } else {
        echo $sDump;
    }
}

/**
 * Get a value from the $_GET Array
 * @param String $sKey
 * @param Mixed $mDefault
 * @return Mixed
 */
function http_get($sKey, $mDefault = null) {
    return array_key_exists($sKey, $_GET) ? $_GET[$sKey] : $mDefault;
}

/**
 * Get a value from the $_POST Array
 * @param String $sKey
 * @param Mixed $mDefault
 * @return Mixed
 */
function http_post($sKey, $mDefault = null) {
    return array_key_exists($sKey, $_POST) ? $_POST[$sKey] : $mDefault;
}

/**
 * Get a value from the $_SESSION Array
 * @param String $sKey
 * @param Mixed $mDefault
 * @return Mixed
 */
function http_session($sKey, $mDefault = null) {
    if (!$_SESSION) {
        return $mDefault;
    }
    return array_key_exists($sKey, $_SESSION) ? $_SESSION[$sKey] : $mDefault;
}

/**
 * Get a value from the $_COOKIE Array
 * @param String $sKey
 * @param Mixed $mDefault
 * @return Mixed
 */
function http_cookie($sKey, $mDefault = null) {
    return array_key_exists($sKey, $_COOKIE) ? $_COOKIE[$sKey] : $mDefault;
}

/**
 * redirect to a given location and stop script from executing
 * @param $sRedirectURL  URL to redirect to
 * @param Boolean $bUtm (true to keep Google Analytics UTM parameters working)
 */
function http_redirect($sRedirectURL, $bUtm = false, $b301 = false) {
    if ($bUtm) {
        if (http_get('utm_source') && !strpos($url, 'utm_source=')) {
            $sRedirectURL .= (strpos($sRedirectURL, '?') ? '&' : '?') . 'utm_source=' . http_get('utm_source');
        }
        if (http_get('utm_medium') && !strpos($sRedirectURL, 'utm_medium=')) {
            $sRedirectURL .= (strpos($sRedirectURL, '?') ? '&' : '?') . 'utm_medium=' . http_get('utm_medium');
        }
        if (http_get('utm_campaign') && !strpos($sRedirectURL, 'utm_campaign=')) {
            $sRedirectURL .= (strpos($sRedirectURL, '?') ? '&' : '?') . 'utm_campaign=' . http_get('utm_campaign');
        }
        if (http_get('utm_term') && !strpos($sRedirectURL, 'utm_term=')) {
            $sRedirectURL .= (strpos($sRedirectURL, '?') ? '&' : '?') . 'utm_term=' . http_get('utm_term');
        }
        if (http_get('utm_content') && !strpos($sRedirectURL, 'utm_content=')) {
            $sRedirectURL .= (strpos($sRedirectURL, '?') ? '&' : '?') . 'utm_content=' . http_get('utm_content');
        }
    }
    if ($b301)
        header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $sRedirectURL);
    die();
}

/**
 * Make a given part of a URL a pretty (SEO friendly) look
 * @param String $sUrlPart
 * @param String $bConvertToUTF8 convert $sUrlPart to UTF-8
 * @return String (SEO friendly) pretty URL part
 */
function prettyUrlPart($sUrlPart, $bConvertToUTF8 = false) {

    if ($bConvertToUTF8) {
        $sUrlPart = mb_convert_encoding($sUrlPart, 'UTF-8', 'ISO-8859-1');
    }

    $sUrlPart = htmlentities((string) $sUrlPart, ENT_COMPAT, "UTF-8", false); //make HTMLentities to remove accents
    $sUrlPart = preg_replace('#&szlig;#i', 'ss', $sUrlPart); //replace `ß` for `ss` (next row is not enough, replaces `ß` for `sz`)
    $sUrlPart = preg_replace('#&([a-z]{1,2})(?:acute|lig|grave|ring|tilde|uml|cedil|caron);#i', '\1', $sUrlPart); //remove accents
    $sUrlPart = html_entity_decode($sUrlPart, ENT_COMPAT, "UTF-8"); //decode to normal character again

    $sUrlPart = str_replace('\'', '', $sUrlPart); //remove all single quotes
    $sUrlPart = preg_replace('#[^a-z0-9-]+#i', '-', $sUrlPart); //replace all non alphanumeric characters and hyphens, with a hyphen
    $sUrlPart = preg_replace('#-+#', '-', $sUrlPart); //remove all double hyphens
    $sUrlPart = trim($sUrlPart, '-'); //remove hyphens at beginning and end of string

    $sUrlPart = strtolower($sUrlPart); //string to lower case characters
    return $sUrlPart;
}

/**
 * prepend a given string with http:// if necessary (optionally keeps HTTPS in tact)
 * @param string $sValue (www.google.nl or http://www.google.nl or https://www.google.nl)
 * @param boolean $bForceHttp
 * @return string 
 */
function addHttp($sValue, $bForceHttp = false) {
    if (!preg_match("#^http(s)?:\/\/#", $sValue) && $sValue) {
        $sValue = "http://" . $sValue;
    }
    if ($bForceHttp) {
        return preg_replace('#^(https://)#i', 'http://', $sValue);
    }
    return $sValue;
}

/**
 * prepend a given string with https:// if necessary (optionally keeps HTTP in tact)
 * @param string $sValue (www.google.nl or http://www.google.nl or https://www.google.nl)
 * @param boolean $bForceHttps
 * @return string 
 */
function addHttps($sValue, $bForceHttps = false) {
    if (!preg_match("#^http(s)?:\/\/#", $sValue) && $sValue) {
        $sValue = "https://" . $sValue;
    }
    if ($bForceHttps) {
        return preg_replace('#^(http://)#i', 'https://', $sValue);
    }
    return $sValue;
}

/* show http error page */

function showHttpError($iErrorNr) {
    include_once DOCUMENT_ROOT . "/controllers" . PATH_PREFIX . "/errors.cont.php";
    die();
}

/**
 * returns the current url
 * @return string
 */
function getCurrentUrl() {
    $sUrl = 'http';
    $bIsHTTPS = false;
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
        $bIsHTTPS = true;
        $sUrl .= 's';
    }
    $sUrl .= '://';
    $sUrl .= isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '';

    if (isset($_SERVER["SERVER_PORT"]) && (!$bIsHTTPS && $_SERVER["SERVER_PORT"] != '80') || ($bIsHTTPS && $_SERVER["SERVER_PORT"] != '443')) {
        $sUrl .= ':' . $_SERVER["SERVER_PORT"];
    }

    $sUrl .= isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
    return $sUrl;
}

function curPageURL() {
    $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
    $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
    $port = ($port) ? ':' . $_SERVER["SERVER_PORT"] : '';
    $url = ($isHTTPS ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . $port . $_SERVER["REQUEST_URI"];
    return $url;
}

/**
 * return relative url path of the current page without extension optional with querystring
 * @param Boolean $bWithQRS also keep query string attached
 * @return string
 */
function getCurrentUrlPath($bWithQRS = false, $bWithExtension = false) {
    $sCurrentUrlPath = $_SERVER['REQUEST_URI'];

    return cleanUrlPath($sCurrentUrlPath, $bWithQRS, $bWithExtension);
}

/**
 * cleans url path from extension and or querystring and slash at the end
 * @param string $sUrlPath relative path of the url after the domain name starting with slash
 * @param boolean $bWithQRS keep qrystring
 * @param boolean $bWithExtension keep extension
 * @return string
 */
function cleanUrlPath($sUrlPath, $bWithQRS = false, $bWithExtension = false) {

# cut urlPart in pieces
    preg_match("/^([^\?\&\.]*)([^\?\&]*)(.*)$/", $sUrlPath, $aMatches);

    $sUrlPath = $aMatches[0];
    $sUrlPathClean = $aMatches[1];
    $sExtension = $aMatches[2];
    $sQRYString = $aMatches[3];

# part needs to start with a slash
    if (!preg_match("#^\/#", $sUrlPathClean)) {
        $sUrlPathClean = "/" . $sUrlPathClean;
    }

# remove slash at the end
    if (preg_match("#\/$#", $sUrlPathClean)) {
        $sUrlPathClean = substr($sUrlPathClean, 0, -1); // remove slash at end
    }

# add extension
    if ($bWithExtension) {
        $sUrlPathClean .= $sExtension;
    }

# add QRYString
    if ($bWithQRS) {
        $sUrlPathClean .= $sQRYString;
    }

#if empty, add slash
    if (empty($sUrlPathClean))
        $sUrlPathClean = '/';

    return $sUrlPathClean;
}

/**
 * remove query string
 * @param string $sUrlPart
 * @return string
 */
function removeQRYStringFromUrlPath($sUrlPath) {
    if (preg_match("/([^\?\&]*)[\?\&]/", $sUrlPath, $aMatches)) {
        $sUrlPath = $aMatches[1];
    }
    return $sUrlPath;
}

/**
 * remove extension
 * @param string $sUrlPath
 * @return string
 */
function removeExtensionFormUrlPath($sUrlPath) {
    if (preg_match("/([^\?\&\.]*)([^\?\&]*)(.*)/", $sUrlPath, $aMatches)) {
        $sUrlPath = $aMatches[1];
        $sUrlPath .= $aMatches[3];
    }
    return $sUrlPath;
}

/**
 * prepare password for database with sha 512
 * @param string $sPass (passwordt to prepare)
 * @return string hashed and max 100 chars
 */
function hashPasswordForDb($sPass) {
    return substr(hash('sha512', $sPass), 0, 100);
}

/**
 * first x words from a string
 * @param string $sText
 * @param int $iMaxWords max amount of words
 * @param string $sKeep_tags keep given tags default = '<a><b><i><u><strong><italic><underline>'
 * @return string
 */
function firstXWords($sText, $iMaxWords, $sKeep_tags = '<a><b><i><u><strong><italic><underline>', $sMoreSign = '&hellip;') {

# strip tags
    $sText = strip_tags($sText, $sKeep_tags);

# match all tags, whitespace and words seperately (special thanks to `The Djuke`)
    preg_match_all('#<[^\>]+>|[^\<\>\s]+|\s+#', $sText, $aTextElements);
    $aTextElements = $aTextElements[0];

# count words and on limit set splice index
    $iWords = 0;
    $iSpliceIndex = count($aTextElements);
    foreach ($aTextElements AS $iIndex => $sElement) {
        if (preg_match('#^[^\s\<]#', $sElement)) {
            $iWords++;
        }
        # words limit is reached, set splice index
        if ($iWords > $iMaxWords) {
            $iSpliceIndex = $iIndex;
            break;
        }
    }

# splice array on splice index
    array_splice($aTextElements, $iSpliceIndex);

# close all unclosed HTML tags
    return closeHTMLTags(implode("", $aTextElements)) . ($iWords > $iMaxWords ? $sMoreSign : '');
}

/**
 * First x characters from a string (UTF-8 compatible!)
 * @param string $sText
 * @param int $iChars
 */
function firstXCharacters($sText, $iChars, $sMoreSign = '&hellip;') {

    $sText = preg_replace('#(\<br\ ?\/?>)#i', ' ', $sText); // replace html and line breaks with a space

    /* verwijder eerst de ongewenste tags */
    $sText = strip_tags($sText);

    if (mb_strlen($sText) <= $iChars)
        return $sText;
    else {
        return mb_strcut($sText, 0, $iChars) . $sMoreSign;
    }
}

/**
 * close all unclosed html tags
 * @param string $sHtml
 * @return string
 */
function closeHTMLTags($sHtml) {

# match all start tags
    preg_match_all("#<((?!br|li)[a-z]+)(?: (?:[a-z0-9-:_\.]+ ?\= ?((?:\")[^\"]+\"|\'[^\']+\')|[^/]+))*(?!/)>#iU", $sHtml, $result);
    $aOpenedTags = $result[1];

# match all close tags
    preg_match_all("#</([a-z]+)>#iU", $sHtml, $result);
    $aClosedTags = $result[1];
    $iLengthOpened = count($aOpenedTags);

# check if all tags are closed
    if (count($aClosedTags) == $iLengthOpened) {
        return $sHtml;
    }

# reverse opened tags for closing
    $aOpenedTags = array_reverse($aOpenedTags);

# close tags
    for ($iC = 0; $iC < $iLengthOpened; $iC++) {
        if (!in_array($aOpenedTags[$iC], $aClosedTags)) {
            $sHtml .= "</" . $aOpenedTags[$iC] . ">";
        } else {
            unset($aClosedTags[array_search($aOpenedTags[$iC], $aClosedTags)]);
        }
    }
    return $sHtml;
}

/**
 * Generate HTML code for displaying pagination
 * @param int $iPageCount total amount of pages
 * @param int $iCurrPage curent page number
 * @param string $sURLFormat the url to place in de <a href="$sURLFormat"></a>, page number must be placed by %s
 * @param string $iPrevPage string to represent the previous page link/button
 * @param string $iNextPage string to represent the next page link/button
 * @param int $iPageOffsetBef amount of pages shown before the current page
 * @param int $iPageOffsetAft amount of pages shown after the current page
 * @param int $sDotSep seperator between pageLinks
 * @return string
 */
function generatePaginationHTML($iPageCount, $iCurrPage, $sURLFormat = '?page=%s', $iPrevPage = '&lt;', $iNextPage = '&gt;', $iPageOffsetBef = 2, $iPageOffsetAft = 2, $sDotSep = '&hellip;') {
    $sHtml = '';

    if ($iPageCount > 1) {

        /* if difference between (currPage-offset-1) equals 2, make offset one larger */
        if (($iCurrPage - $iPageOffsetBef - 1) == 2) {
            $iPageOffsetBef += 1;
        }

        /* if difference between (currPage+offset-lastPage) equals 2, make offset one larger */
        if (($iPageCount - ($iCurrPage + $iPageOffsetAft)) == 2) {
            $iPageOffsetAft += 1;
        }

        $sHtml .= '<div class="pagination">';

        if ($iCurrPage > 1)
            $sHtml .= '<a class="prevPage" href="' . sprintf($sURLFormat, ($iCurrPage - 1)) . '">' . $iPrevPage . '</a>';

        if (($iCurrPage - 1) > $iPageOffsetBef) {
            $sHtml .= '<a class="firstPage" href="' . sprintf($sURLFormat, "1") . '">1</a>';
            if (($iCurrPage - $iPageOffsetBef) > 2)
                $sHtml .= '<span class="dotSep">' . $sDotSep . '</span>';
        }

        /* for loop to make links around current page */
        $iOffsetCounter = (-$iPageOffsetBef); //extra counter for offset counting

        for ($i = ($iCurrPage - $iPageOffsetBef); $i <= ($iCurrPage + $iPageOffsetAft); $i++) {
            if ($i >= 1 && $i <= ($iPageCount)) {
                if ($i != $iCurrPage) {
                    $sHtml .= '<a class="pageLink offset' . $iOffsetCounter . '" href="' . sprintf($sURLFormat, $i) . '">' . $i . '</a>';
                } else
                    $sHtml .= '<span class="currPage">' . $i . '</span>';
            }
            $iOffsetCounter++;
        }

        if (($iCurrPage + $iPageOffsetAft) < $iPageCount) {
            if (($iCurrPage + $iPageOffsetAft) < ($iPageCount - 1)) {
                $sHtml .= '<span class="dotSep">' . $sDotSep . '</span>';
            }
            $sHtml .= '<a class="lastPage" href="' . sprintf($sURLFormat, $iPageCount) . '">' . $iPageCount . '</a>';
        }


        if ($iCurrPage < $iPageCount)
            $sHtml .= '<a class="nextPage" href="' . sprintf($sURLFormat, ($iCurrPage + 1)) . '">' . $iNextPage . '</a>';


        $sHtml .= '</div>';
    }
    return $sHtml;
}

/**
 * check if string is a valid password
 * minimal 8 characters, 1 uppercase, 1 digit, 1 special character
 * @return boolean
 */
function isValidPassword($sPass) {
    return preg_match('#^.*(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[\W])(?=.*[\d]).*$#', $sPass);
}

/**
 * remove a directory and all in it
 * @param string $sPath
 */
function emptyAndRemoveDir($sPath) {
    if (empty($sPath)) {
        return false;
    } else {
        $sPath = preg_replace('#(/)$#i', '', $sPath); // remove slash if has one
        if (!$rDir = @opendir($sPath))
            return false;
        while (false !== ($sFileDir = @readdir($rDir))) {
            # not a file name, continue
            if ($sFileDir == '.' || $sFileDir == '..')
                continue;
            # try to unlink, otherwise, try to empty
            if (!@unlink($sPath . '/' . $sFileDir))
                emptyAndRemoveDir($sPath . '/' . $sFileDir, true);
        }

        # close dir
        @closedir($rDir);

        # remove dir
        @rmdir($sPath);
    }
}

/**
 * get link target based on the server host (target='')
 * @param string $sLink
 * @return string ('_blank', '_self')
 */
function getLinkTarget($sLink) {
    $sHost = $_SERVER['HTTP_HOST'];
    if (preg_match('#' . $sHost . '#', $sLink) || preg_match('#^/(?!/)#', $sLink)) {
        return '_self';
    } else {
        return '_blank';
    }
}

/**
 * check if IP addres is aside
 */
function isAside() {
    return isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '37.0.81.193';
}

/**
 * check if IP addres is client IP
 */
function isClient() {
    return isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], explode(',', CLIENT_IP));
}

/**
 * handle PHP errors
 * @param int $iErrorno
 * @param string $sError
 * @param string $sFile
 * @param int $iLine
 * @param string $sExtraInfo
 */
function error_handler($iErrorLevel, $sError, $sFile, $iLine, $sExtraInfo = '') {
    // PHP 8: @-suppressed errors no longer make error_reporting() return 0, so check the level against the mask
    if (!(error_reporting() & $iErrorLevel))
        return false;

    // deprecations are only logged to file, never emailed or shown
    if ($iErrorLevel === E_DEPRECATED || $iErrorLevel === E_USER_DEPRECATED) {
        @file_put_contents(ASIDE_LOG_FOLDER . '/php-deprecated.log', '[' . date('Y-m-d H:i:s') . '] ' . $sFile . ':' . $iLine . ' ' . $sError . PHP_EOL, FILE_APPEND);
        return true;
    }

    $sErrorMsg = '<br />';
    switch ($iErrorLevel) {
        case E_WARNING:
        case E_USER_WARNING:
            $sErrorMsg .= '<b>Warning: </b>';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $sErrorMsg .= '<b>Notice: </b>';
            break;
        default:
            $sErrorMsg .= '<b>Error: </b>';
            break;
    }
    $sErrorMsg .= $sError . ' in <b>' . $sFile . '</b>' . ' on line ' . $iLine . '<br />';
    if (DEBUG) {
        echo $sErrorMsg;
    } else {
        Debug::logError($iErrorLevel, $sErrorMsg, $sFile, $iLine, _d($sExtraInfo, 1, 1), Debug::LOG_IN_EMAIL);
        Debug::logError($iErrorLevel, $sErrorMsg, $sFile, $iLine, _d($sExtraInfo, 1, 1), Debug::LOG_IN_DATABASE);
    }
}

/**
 * replacement for strftime (deprecated in PHP 8.1), maps the used % codes to date() with English names
 * unsupported codes are left in the output literally and trigger an E_USER_NOTICE
 * @param string $sFormat strftime format
 * @param int $iTimestamp (optional, default = now)
 * @return string
 */
function legacy_strftime($sFormat, $iTimestamp = null) {
    $iTimestamp = ($iTimestamp === null ? time() : (int) $iTimestamp);
    return preg_replace_callback('#%([a-zA-Z%])#', function ($aMatch) use ($iTimestamp, $sFormat) {
        switch ($aMatch[1]) {
            case 'a': return date('D', $iTimestamp); // short weekday name
            case 'A': return date('l', $iTimestamp); // full weekday name
            case 'b':
            case 'h': return date('M', $iTimestamp); // short month name
            case 'B': return date('F', $iTimestamp); // full month name
            case 'c': return date('D M ', $iTimestamp) . sprintf('%2d', date('j', $iTimestamp)) . date(' H:i:s Y', $iTimestamp); // C locale date and time
            case 'd': return date('d', $iTimestamp); // day of month 01-31
            case 'e': return sprintf('%2d', date('j', $iTimestamp)); // day of month, space padded
            case 'H': return date('H', $iTimestamp); // hour 00-23
            case 'I': return date('h', $iTimestamp); // hour 01-12
            case 'j': return sprintf('%03d', date('z', $iTimestamp) + 1); // day of year 001-366
            case 'm': return date('m', $iTimestamp); // month 01-12
            case 'M': return date('i', $iTimestamp); // minutes 00-59
            case 'p': return date('A', $iTimestamp); // AM/PM
            case 'S': return date('s', $iTimestamp); // seconds 00-59
            case 'u': return date('N', $iTimestamp); // weekday 1-7, monday = 1
            case 'w': return date('w', $iTimestamp); // weekday 0-6, sunday = 0
            case 'W': return sprintf('%02d', intdiv(date('z', $iTimestamp) + 7 - ((date('w', $iTimestamp) + 6) % 7), 7)); // week number, first monday starts week 1
            case 'y': return date('y', $iTimestamp); // 2 digit year
            case 'Y': return date('Y', $iTimestamp); // 4 digit year
            case '%': return '%';
            default:
                trigger_error('legacy_strftime: unsupported format code `' . $aMatch[0] . '` in `' . $sFormat . '`', E_USER_NOTICE);
                return $aMatch[0];
        }
    }, $sFormat);
}

/**
 * handle fatal error or other errors that shutdown the script
 */
function shutdown_handler() {
    $aErrorDetails = error_get_last();
    if ($aErrorDetails === null)
        return;
    if (!error_reporting())
        return false;
    $sErrorMsg = '<br />';
    switch ($aErrorDetails['type']) {
        case E_ERROR:
            $sErrorMsg .= '<b>Fatal error: </b>';
            break;
        default:
            return;
    }
    $sErrorMsg .= $aErrorDetails['message'] . ' in <b>' . $aErrorDetails['file'] . '</b>' . ' on line ' . $aErrorDetails['line'] . '<br />';

    if (DEBUG) {
        echo $sErrorMsg;
    } else {
        Debug::logError($aErrorDetails['type'], $sErrorMsg, $aErrorDetails['file'], $aErrorDetails['line'], '', Debug::LOG_IN_EMAIL);
        Debug::logError($aErrorDetails['type'], $sErrorMsg, $aErrorDetails['file'], $aErrorDetails['line'], '', Debug::LOG_IN_DATABASE);
        echo 'Er is een fatale fout opgetreden. Neem a.u.b. contact met ons op.';
    }
}

/**
 * check string for anchor tags and BB code links (SPAM content check)
 * @param string $sContent
 * @return boolean 
 */
function hasLinks($sContent) {
    return preg_match('#(<a([^>]*)href=|\[link=|\[url=|\[hyperlink=)#i', $sContent);
}

/**
 * generate a random password
 * @param int $iLength length of the password
 * @param int $iMinLowerChars minimum number of lowercase characters
 * @param int $iMinUpperChars minimum number of uppercase characters
 * @param int $iMinNumbers minimum number of numbers
 * @param int $iMinSpecialChars minimum number of special characters
 * @param string $sLowerChars usable lowercase characters
 * @param string $sUpperChars usable uppercase characters
 * @param string $sNumbers usable numbers
 * @param string $sSpecialChars usable special characters
 * @return string
 */
function randomPassword($iLength = 10, $iMinLowerChars = 1, $iMinUpperChars = 1, $iMinNumbers = 1, $iMinSpecialChars = 1, $sLowerChars = 'abcdefghijkmnpqrstuvwxyz', $sUpperChars = 'ABCDEFGHJKLMNPQRSTUVWXYZ', $sNumbers = '23456789', $sSpecialChars = '!@#$%^&*()?') {
# count all the required characters
    $iSumRequiredChars = $iMinLowerChars + $iMinUpperChars + $iMinNumbers + $iMinSpecialChars;

# make sure the length is never lower than the number of required characters
    if ($iLength < ($iSumRequiredChars))
        $iLength = $iSumRequiredChars;

    $sPassword = '';

# add the minimun number of lowercase characters
    for ($i = 0; $i < $iMinLowerChars; $i++) {
        $sPassword .= substr($sLowerChars, rand(0, strlen($sLowerChars) - 1), 1);
    }

# add the minimun number of uppercase characters
    for ($i = 0; $i < $iMinUpperChars; $i++) {
        $sPassword .= substr($sUpperChars, rand(0, strlen($sUpperChars) - 1), 1);
    }

# add the minimun number of numbers
    for ($i = 0; $i < $iMinNumbers; $i++) {
        $sPassword .= substr($sNumbers, rand(0, strlen($sNumbers) - 1), 1);
    }

# add the minimun number of special characters
    for ($i = 0; $i < $iMinSpecialChars; $i++) {
        $sPassword .= substr($sSpecialChars, rand(0, strlen($sSpecialChars) - 1), 1);
    }

# put all usable characters together for the rest
    $sAllUsableChars = $sLowerChars . $sUpperChars . $sNumbers . $sSpecialChars;

# add the rest
    for ($i = 0; $i < ($iLength - $iSumRequiredChars); $i++) {
        $sPassword .= substr($sAllUsableChars, rand(0, strlen($sAllUsableChars) - 1), 1);
    }

    return str_shuffle($sPassword);
}

/**
 * generate a meta description based on given content
 * replaces </p>,<br />,<br>,<br/> with spaces
 * takes first 20 words and 156 chars from the 20 words
 * makes html entities for strange chars and quotes
 * trims whitespace
 * @param string $sContent
 * @return string
 */
function generateMetaDescription($sContent) {
    return trim(htmlspecialchars(firstXCharacters(firstXWords(preg_replace('#(\<\/p\>|\<br ?\/?\>)#i', ' ', preg_replace('#(\n|\r)#i', '', (string) $sContent)), 20, '', ''), 156, ''), ENT_QUOTES, 'UTF-8'));
}

/**
 * get string between two given strings
 * @param string $sString haystack
 * @param string $sStart string where to start searching, for example "<p>@kolom1@</p>"
 * @param string $sEnd string where to stop searching, for example "<p>@end_kolom1@</p>"
 * @param array $aMatches 
 */
function matchBetweenStrings($sString, $sStart, $sEnd, &$aMatches) {
    $sStart = preg_replace('#([\^\$\(\)\<\>\|\\\{\}\[\]\.\*\+\?\#]+?)#i', '\\\\$1', $sStart); // remove all characters that need to be escaped for regular expressions
    $sEnd = preg_replace('#([\^\$\(\)\<\>\|\\\{\}\[\]\.\*\+\?\#]+?)#i', '\\\\$1', $sEnd); // remove all characters that need to be escaped for regular expressions
    preg_match_all('#' . $sStart . '(.*?)' . $sEnd . '#si', $sString, $aMatches); // match all characters between the start and end
    $aMatches = $aMatches[1]; // only set individual matches
}

/**
 * Insert the address and return the lat long
 * @param string $sAddress
 * @return array (latitude / longitude)
 */
function getLatLong($sAddress) {
    $sGeocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . urlencode($sAddress) . '&sensor=false');
    $oOutput = json_decode($sGeocode);

# Check if there is a lattitude and longitude
    if (empty($oOutput->results[0]->geometry->location->lat) || empty($oOutput->results[0]->geometry->location->lng)) {
        return NULL;
    } else {
        $aLocation = array();
        $aLocation['latitude'] = $oOutput->results[0]->geometry->location->lat;
        $aLocation['longitude'] = $oOutput->results[0]->geometry->location->lng;
        return $aLocation;
    }
}

/**
 * return decimal converted to valuta optional with currency symbol
 * @param float $fDecimal
 * @param string $sLang language to determine currency symbol and decimal signs etc DEFAULT 'nl'
 * @param bool $bWithCurrencySymbol optionally show currency symbol dEFAULT true
 * @return string
 */
function decimal2valuta($fDecimal, $sLang = 'nl', $bWithCurrencySymbol = true) {

    if (!is_numeric($fDecimal)) {
        return '';
    }

    switch ($sLang) {
        case 'nl':
            return ($bWithCurrencySymbol ? '€ ' : '' ) . number_format($fDecimal, 2, ',', '.');
            break;
    }
}

/**
 * converts HTML characters to HTML entities (double encode disables)
 * @param mixed $mValue
 * @return string 
 */
function _e($mValue) {
    return htmlentities($mValue, ENT_QUOTES, 'UTF-8');
}

/**
 * compare the sSortValue property with two objects (this can be used to create a user defined sort)
 * @param object $oX
 * @param object $oY
 * @return 0/-1/1 
 */
function compareSortValue($oX, $oY) {
    $oX->sSortValue = strtolower($oX->sSortValue);
    $oY->sSortValue = strtolower($oY->sSortValue);

    if ($oX->sSortValue == $oY->sSortValue)
        return 0;
    else if ($oX->sSortValue < $oY->sSortValue)
        return -1;
    else
        return 1;
}


/**
 * Replace tags for AMP equivalents for dynamic content
 * @param type $html
 * @return type string
 */

function ampify($html='') {
    
    # Replace img, audio, and video elements with amp custom elements
    
    $aReplace = array('<img','<video','/video>','<audio','/audio>');
    $aReplaceWidth = array('<amp-img','<amp-video','/amp-video>','<amp-audio','/amp-audio>');
    
    $html = str_ireplace(
        $aReplace,
        $aReplaceWidth,
        $html
    );
    # Add closing tags to amp-img custom element
    $html = preg_replace('/<amp-img(.*?)>/', '<amp-img layout="responsive"$1></amp-img>',$html);
    # Whitelist of HTML tags allowed by AMP
    $html = strip_tags($html,'<h1><h2><h3><h4><h5><h6><a><p><ul><ol><li><blockquote><q><cite><ins><del><strong><em><code><pre><svg><table><thead><tbody><tfoot><th><tr><td><dl><dt><dd><article><section><header><footer><aside><figure><time><abbr><div><span><hr><small><br><amp-img><amp-audio><amp-video><amp-ad><amp-anim><amp-carousel><amp-fit-rext><amp-image-lightbox><amp-instagram><amp-lightbox><amp-twitter><amp-youtube>');
    $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
    return $html;
}

?>