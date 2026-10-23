<?php

set_time_limit(0);

# set document root
//define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);
define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
//echo DOCUMENT_ROOT; die;

include_once DOCUMENT_ROOT . '/inc/config.inc.php';

// default false, set to true to really compress, delete and update images and image files
$bDoItNow = true;
// limit (for test mode only)
$iLimit = null;

$oDb = DBConnections::get();
$sQuery = 'SELECT * FROM `images`';

// get all images
$aImages = $oDb->query($sQuery, QRY_OBJECT, 'Image');
$oImage = new Image();
foreach ($aImages AS $iKey => $oImage) {
    // quit at `$iLimit` images
    if (!$bDoItNow && $iLimit && $iKey == $iLimit) {
        break;
    }

    // get all image files
    $aImageFiles = $oImage->getImageFiles();
    $oImageFile = new ImageFile();
    $bNoImageFiles = true;
    foreach ($aImageFiles AS $oImageFile) {

        $sDestination = DOCUMENT_ROOT . $oImageFile->link;

        if (!file_exists($sDestination)) {
            // delete image file from database when file does not exists on the server
            $sQuery = 'DELETE FROM `media` WHERE `mediaId` = ' . db_int($oImageFile->mediaId);
            if ($bDoItNow) {
                $oDb->query($sQuery, QRY_NORESULT);
            }
            _d('ImageFile `' . $oImageFile->link . '` deleted');
            continue;
        }

        // set noImageFiles to true when a file is found
        $bNoImageFiles = false;

        $aSettings = array();
        $aCompressedImages = ImageEditorWebserviceManager::getCompressedImages(array(basename($sDestination) => base64_encode(file_get_contents($sDestination))), $aSettings);
        if (!empty($aCompressedImages['images'])) {
            // overwrite file with compressed one
            if ($bDoItNow) {
                // overwrite file with compressed one
                file_put_contents($sDestination, base64_decode(array_shift($aCompressedImages['images'])));
                // get file size
                $iFileSize = filesize($sDestination);
            } else {
                // create tmp file to calculate compression
                file_put_contents($sDestination . 'tmp', base64_decode(array_shift($aCompressedImages['images'])));
                // get file size from tmp file
                $iFileSize = filesize($sDestination . 'tmp');
                // delete tmp file
                unlink($sDestination . 'tmp');
            }

            // update new size
            $sQuery = 'UPDATE `files` SET `size` = ' . db_int($iFileSize) . ' WHERE `mediaId` = ' . db_int($oImageFile->mediaId);
            if ($bDoItNow) {
                $oDb->query($sQuery, QRY_NORESULT);
            }
            _d('ImageFile `' . $oImageFile->link . '` compressed `' . $oImageFile->size . '->' . $iFileSize . '` | compression: `' . round((($oImageFile->size - $iFileSize) / $oImageFile->size) * 100, 2) . '%`');
        }
    }

    // no image files at all, delete image
    if ($bNoImageFiles) {
        _d('Image `' . $oImage->imageId . '` deleted');
        if ($bDoItNow) {
            ImageManager::deleteImage($oImage);
        }
    }
    echo '<hr />';
}