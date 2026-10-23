<?php

set_time_limit(0);

# set document root
//define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);
define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');

include_once DOCUMENT_ROOT . '/inc/config.inc.php';

// default false, set to true to really compress, delete and update images and image files
$bDoItNow = true;

// all folders to walk recursively to compress all images in it
$aFolders = array(
    DOCUMENT_ROOT . '/images/apple-touch-icons',
    DOCUMENT_ROOT . '/images/layout',
    DOCUMENT_ROOT . '/images/window-8-tiles',
);

foreach ($aFolders AS $sFolder) {
    compressImagesInDir($sFolder);
    echo '<hr />';
}

/**
 * remove a directory and all in it
 * @param string $sPath
 */
function compressImagesInDir($sPath) {
    global $bDoItNow;
    echo '<b>' . ($sPath) . '</b><br />';
    if (empty($sPath)) {
        return false;
    } else {
        $sPath = preg_replace('#(/)$#i', '', $sPath); // remove slash if has one
        if (!$rDir = opendir($sPath)) {
            return false;
        }
        while (false !== ($sFileDir = readdir($rDir))) {
            
            # not a file name, continue
            if ($sFileDir == '.' || $sFileDir == '..') {
                continue;
            }

            if (is_file($sPath . '/' . $sFileDir)) {
                
                $sDestination = $sPath . '/' . $sFileDir;
                $aSettings = array();
                $aCompressedImages = ImageEditorWebserviceManager::getCompressedImages(array(basename($sDestination) => base64_encode(file_get_contents($sDestination))), $aSettings);
                if (!empty($aCompressedImages['images'])) {
                    // measure old filesize
                    $iOldFileSize = filesize($sDestination);

                    // overwrite file with compressed one
                    if ($bDoItNow) {
                        // overwrite file with compressed one
                        file_put_contents($sDestination, base64_decode(array_shift($aCompressedImages['images'])));
                        // get file size, clear cache first to get new size
                        clearstatcache();
                        $iFileSize = filesize($sDestination);
                    } else {
                        // misuse the /images/page/original, because it's always there and writable, to measure filesize on server
                        $sTmpDestination = DOCUMENT_ROOT . '/images/page/original/tmp-' . basename($sDestination);

                        // create tmp file to calculate compression
                        file_put_contents($sTmpDestination, base64_decode(array_shift($aCompressedImages['images'])));
                        // get tmp file size, clear cache first to get new size
                        clearstatcache();
                        $iFileSize = filesize($sTmpDestination);
                        // delete tmp file
                        unlink($sTmpDestination);
                    }

                    _d('Image `' . $sDestination . '` compressed `' . $iOldFileSize . '->' . $iFileSize . '` | compression: `' . round((($iOldFileSize - $iFileSize) / $iOldFileSize) * 100, 2) . '%`');
                }
            }

            # try to unlink, otherwise, try to empty
            if (is_dir($sPath . '/' . $sFileDir)) {
                compressImagesInDir($sPath . '/' . $sFileDir);
            }
        }
        echo '<hr />';

        # close dir
        closedir($rDir);
    }
}
