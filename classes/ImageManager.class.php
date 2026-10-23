<?php

/* Models and managers used by the uploadManager */
require_once 'Image.class.php';

class ImageManager {

    /**
     * save Image object
     * @param Image $oImage
     */
    public static function saveImage(Image $oImage) {

        $oDb = DBConnections::get();

        # new image
        $sQuery = ' INSERT INTO
                        `images`
                        (
                            `imageId`,
                            `order`,
                            `online`
                        )
                        VALUES (
                            ' . db_int($oImage->imageId) . ',
                            ' . db_int($oImage->order) . ',
                            ' . db_int($oImage->online) . '
                        )
                        ON DUPLICATE KEY UPDATE
                            `imageId`=VALUES(`imageId`),
                            `order`=VALUES(`order`),
                            `online`=VALUES(`online`)
                    ;';

        $oDb->query($sQuery, QRY_NORESULT);
        if ($oImage->imageId === null)
            $oImage->imageId = $oDb->insert_id;

        # save image files
        self::saveImageFiles($oImage);
    }

    /**
     * save image files from image
     * @param Image $oImage
     */
    private static function saveImageFiles(Image $oImage) {

        $sFileValues = '';
        $sImageFileValues = '';

        $oDb = DBConnections::get();
        foreach ($oImage->getImageFiles() AS $oImageFile) {

            list($iW, $iH, $iImageType, $sImageSizeAttr) = getimagesize(DOCUMENT_ROOT . $oImageFile->link);
            $oImageFile->imageSizeAttr = $sImageSizeAttr;

            # continue
            if ($oImageFile->mediaId !== null)
                continue;

            # save imageFile
            $sQuery = ' INSERT INTO
                            `media`
                        (
                            `mediaId`,
                            `link`,
                            `title`,
                            `type`,
                            `online`,
                            `order`,
                            `created`
                        )
                        VALUES
                        (
                            ' . db_int($oImageFile->mediaId) . ',
                            ' . db_str($oImageFile->link) . ',
                            ' . db_str($oImageFile->title) . ',
                            ' . db_str($oImageFile->type) . ',
                            ' . db_int($oImageFile->online) . ',
                            ' . db_int($oImageFile->order) . ',
                            NOW()
                        )ON DUPLICATE KEY UPDATE
                            `title` = VALUES(`title`),
                            `online` = VALUES(`online`),
                            `order` = VALUES(`order`)
                        ;';

            $oDb->query($sQuery, QRY_NORESULT);
            $oImageFile->mediaId = $oDb->insert_id;
            $oImageFile->imageId = $oImage->imageId;

            $sFileValues .= ($sFileValues == '' ? '' : ',') . '(' . db_int($oImageFile->mediaId) . ',' . db_str($oImageFile->name) . ',' . db_str($oImageFile->mimeType) . ',' . db_int($oImageFile->size) . ')';
            $sImageFileValues .= ($sImageFileValues == '' ? '' : ',') . '(' . db_int($oImageFile->imageId) . ',' . db_int($oImageFile->mediaId) . ',' . db_str($oImageFile->reference) . ',' . db_str($oImageFile->imageSizeAttr) . ')';
        }

        # insert file values
        if ($sFileValues != '') {
            $sQuery = ' INSERT INTO
                            `files`
                        (
                            `mediaId`,
                            `name`,
                            `mimeType`,
                            `size`
                        )
                        VALUES
                        ' . $sFileValues . '
                        ON DUPLICATE KEY UPDATE
                            `size`=VALUES(`size`)
                        ;';
            $oDb->query($sQuery, QRY_NORESULT);
        }

        # insert imageFile values
        if ($sImageFileValues != '') {
            $sQuery = ' INSERT INTO
                            `imageFiles`
                        (
                            `imageId`,
                            `mediaId`,
                            `reference`,
                            `imageSizeAttr`
                        )
                        VALUES
                        ' . $sImageFileValues . '
                        ON DUPLICATE KEY UPDATE
                            `imageSizeAttr`=VALUES(`imageSizeAttr`)                            
                        ;';
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

    /**
     * save a single imageFile
     * @param ImageFile $oImageFile 
     */
    public static function saveImageFile(ImageFile $oImageFile) {

        list($iW, $iH, $iImageType, $sImageSizeAttr) = getimagesize(DOCUMENT_ROOT . $oImageFile->link);
        $oImageFile->imageSizeAttr = $sImageSizeAttr;

        $oDb = DBConnections::get();
        # save imageFile
        # insert media, can not be updated because of special functions
        $sQuery = ' INSERT INTO
                        `media`
                    (
                        `mediaId`,
                        `link`,
                        `title`,
                        `type`,
                        `online`,
                        `order`,
                        `created`
                    )
                    VALUES
                    (
                        ' . db_int($oImageFile->mediaId) . ',
                        ' . db_str($oImageFile->link) . ',
                        ' . db_str($oImageFile->title) . ',
                        ' . db_str($oImageFile->type) . ',
                        ' . db_int($oImageFile->online) . ',
                        ' . db_int($oImageFile->order) . ',
                        NOW()
                    )ON DUPLICATE KEY UPDATE
                            `title` = VALUES(`title`),
                            `online` = VALUES(`online`),
                            `order` = VALUES(`order`)
                        ;';
        $oDb->query($sQuery, QRY_NORESULT);
        if ($oImageFile->mediaId === null)
            $oImageFile->mediaId = $oDb->insert_id;

        # insert file (only update size on duplicate key)
        $sQuery = ' INSERT INTO
                        `files`
                    (
                        `mediaId`,
                        `name`,
                        `mimeType`,
                        `size`
                    )
                    VALUES(
                        ' . db_int($oImageFile->mediaId) . ',
                        ' . db_str($oImageFile->name) . ',
                        ' . db_str($oImageFile->mimeType) . ',
                        ' . db_int($oImageFile->size) . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `size`=VALUES(`size`)
                        ;';
        $oDb->query($sQuery, QRY_NORESULT);

        # insert imageFile only if not exists
        $sQuery = ' INSERT INTO
                        `imageFiles`
                    (
                        `imageId`,
                        `mediaId`,
                        `reference`,
                        `imageSizeAttr`
                    )
                    VALUES
                    (
                        ' . db_str($oImageFile->imageId) . ',
                        ' . db_str($oImageFile->mediaId) . ',
                        ' . db_str($oImageFile->reference) . ',
                        ' . db_str($oImageFile->imageSizeAttr) . '
                    )
                    ON DUPLICATE KEY UPDATE
                        `imageSizeAttr`=VALUES(`imageSizeAttr`)
                    ;';
        $oDb->query($sQuery, QRY_NORESULT);

        // update to existing ImageFile so reset resized versions
        if (!empty($oImageFile->mediaId)) {
            // delete all auto resized versions of this ImageFile
            $oImageFile->deleteAutoResized();
        }
    }

    /**
     * get all image files for a image
     * @param int $iImageId
     */
    public static function getImageFilesByImageId($iImageId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `imageFiles` AS `if`
                    JOIN
                        `files` AS `f` USING(`mediaId`)
                    JOIN
                        `media` AS `m` USING(`mediaId`)
                    WHERE
                        `imageId` = ' . db_int($iImageId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'ImageFile');
    }

    /**
     * get an image by imageId
     * @param iny $iImageId
     * @return Image 
     */
    public static function getImageById($iImageId) {
        $sQuery = ' SELECT
                        `i`.*
                    FROM
                        `images` AS `i`
                    WHERE
                        `i`.`imageId` = ' . db_int($iImageId) . '
                    ;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Image');
    }

    /**
     * get Image file object by reference
     * @param int $iImageId
     * @param string $sReference
     * @param ImageFile
     */
    public static function getImageFileByImageAndReference($iImageId, $sReference) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `imageFiles` AS `if`
                    JOIN
                        `files` AS `f` USING(`mediaId`)
                    JOIN
                        `media` AS `m` USING(`mediaId`)
                    WHERE
                        `imageId` = ' . db_int($iImageId) . '
                    AND
                        `if`.`reference` = ' . db_str($sReference) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'ImageFile');
    }

    /**
     *
     * @param int $iOriW
     * @param int $iOriH
     * @param int $iW
     * @param int $iH
     * @param int $newW
     * @param int $newH 
     */
    private static function getResizeDimensions($iOriW, $iOriH, $iW = null, $iH = null, &$iNewW = 0, &$iNewH = 0) {
        if ($iW && $iH) {

            #set defaults
            $iH1 = 0;
            $iW1 = 0;
            $iH2 = 0;
            $iW2 = 0;

            #resize based on height
            if ($iOriH > $iH) {
                $iNewH = $iH;
                $iNewW = round($iNewH * $iOriW / $iOriH);
                if ($iNewW <= $iW && $iNewH <= $iH) {
                    $iW1 = $iNewW;
                    $iH1 = $iNewH;
                }
            }

            #resize based on width
            if ($iOriW > $iW) {
                $iNewW = $iW;
                $iNewH = round($iNewW * $iOriH / $iOriW);
                if ($iNewW <= $iW && $iNewH <= $iH) {
                    $iW2 = $iNewW;
                    $iH2 = $iNewH;
                }
            }

            # check which option results in a bigger area
            if (($iW1 * $iH1) > ($iW2 * $iH2)) {
                $iNewW = $iW1;
                $iNewH = $iH1;
            } else {
                $iNewW = $iW2;
                $iNewH = $iH2;
            }

            # already right size
            if ($iOriH <= $iH && $iOriW <= $iW) {
                $iNewW = $iOriW;
                $iNewH = $iOriH;
            }
        } elseif ($iW) {
            # only fit in width
            if ($iOriW > $iW) {
                $iNewW = $iW;
                $iNewH = round($iNewW * $iOriH / $iOriW);
            } else {
                $iNewW = $iOriW;
                $iNewH = $iOriH;
            }
        } elseif ($iH) {
            # only fit in height
            if ($iOriH > $iH) {
                $iNewH = $iH;
                $iNewW = round($iNewH * $iOriW / $iOriH);
            } else {
                $iNewW = $iOriW;
                $iNewH = $iOriH;
            }
        } else {
            # no resizing is needed
            $iNewW = $iOriW;
            $iNewH = $iOriH;
        }
    }

    /**
     *
     * resize an image, save the file to the server
     * save a new ImageFile to the database
     * @param string $sOriLocation location of original, local or remote
     * @param string $sDestination destination location (optional - overwrite original)
     * @param int $iW width of the image to become
     * @param int $iH height of the image to become     
     * @param int $iCx crop start position x default: 0
     * @param int $iCy crop start position y default: 0 
     * @param int $iCw crop box width
     * @param int $iHc crop box height
     * @param int $iJpegQuality queality of the jpeg file default: -1 (setts to 93 by default for GD or default webservice value)
     * @param string $sErrorMsg variable for error messaging
     * @param bool $bAbsoluteSize take size absolute (no resizing, just take size)
     * @return bool 
     */
    private static function resizeAndCrop($sOriLocation, $sDestination = null, $iW = null, $iH = null, $iCx = 0, $iCy = 0, $iCw = null, $iCh = null, $iJpegQuality = -1, &$sErrorMsg = null, $bAbsoluteSize = false) {

        # file exists?
        if (!file_exists($sOriLocation)) {
            $sErrorMsg = 'Het bestand kan niet worden gevonden';
            return false;
        }

        # get image properties
        $aFileProps = getimagesize($sOriLocation);

        $iOriW = $aFileProps[0];
        $iOriH = $aFileProps[1];
        $iOriType = $aFileProps[2];
        $iOriMimeType = $aFileProps['mime'];

        # set destination to original if not given
        if ($sDestination === null)
            $sDestination = $sOriLocation;

        # set absoulte size if needed
        if ($bAbsoluteSize === true) {
            # only W, calculate H (absolute W)
            if ($iW !== null && $iH === null) {
                $iNewW = $iW;
                $iNewH = $iNewW * $iOriH / $iOriW;
            } elseif ($iW === null && $iH !== null) {
                # only H, calculate W (absolute H)
                $iNewH = $iH;
                $iNewW = $iNewH * $iOriW / $iOriH;
            } else {
                # make destination image exact size
                $iNewW = $iW;
                $iNewH = $iH;
            }
        } else {
            self::getResizeDimensions($iOriW, $iOriH, $iW, $iH, $iNewW, $iNewH);
        }

        # set crop box size to full original if one dimension is missing (resize)
        if ($iCw === null || $iCh === null) {
            $iCw = $iOriW;
            $iCh = $iOriH;
        } else {
            # cropbox sizes are given (crop)
            if ($bAbsoluteSize === true) {
                # destination filesize equals crop box size
                $iNewW = $iW;
                $iNewH = $iH;
            } else {
                # resize destination filedimensions by based on cropbox size (new cropped image)
                self::getResizeDimensions($iCw, $iCh, $iW, $iH, $iNewW, $iNewH);
            }
        }

        # copy image, resize and save
        switch ($iOriType) {
            case IMAGETYPE_JPEG:
                $rOriginalImage = imagecreatefromjpeg($sOriLocation);
                $rTemporaryImage = imagecreatetruecolor($iNewW, $iNewH);

                imagecopyresampled($rTemporaryImage, $rOriginalImage, 0, 0, $iCx, $iCy, $iNewW, $iNewH, $iCw, $iCh);

                // compress image
                if (class_exists('ImageEditorWebserviceManager')) {
                    $bSuccess = imagejpeg($rTemporaryImage, $sDestination, 100);
                    $aSettings = array();
                    if ($iJpegQuality !== -1) {
                        $aSettings['compression'] = $iJpegQuality;
                    }

                    $aCompressedImages = ImageEditorWebserviceManager::getCompressedImages(array(basename($sDestination) => base64_encode(file_get_contents($sDestination))), $aSettings);
                    if (!empty($aCompressedImages['images'])) {
                        // overwrite file with compressed one
                        file_put_contents($sDestination, base64_decode(array_shift($aCompressedImages['images'])));
                    }
                } else {
                    // do normal creating of image and set jpeg quality to 93 when not set
                    $bSuccess = imagejpeg($rTemporaryImage, $sDestination, $iJpegQuality !== -1 ? $iJpegQuality : 93);
                }
                return $bSuccess;
            case IMAGETYPE_GIF:
                $rOriginalImage = imagecreatefromgif($sOriLocation);
                $rTemporaryImage = imagecreatetruecolor($iNewW, $iNewH);

                # transparency magic
                $iTransparency = imagecolortransparent($rOriginalImage);
                $iPalletSize = imagecolorstotal($rOriginalImage);
                if ($iTransparency >= 0 && $iTransparency < $iPalletSize) {
                    $aTransparencyColor = imagecolorsforindex($rOriginalImage, $iTransparency);
                    $iTransparency = imagecolorallocate($rTemporaryImage, $aTransparencyColor['red'], $aTransparencyColor['green'], $aTransparencyColor['blue']);
                    imagefill($rTemporaryImage, 0, 0, $iTransparency);
                    imagecolortransparent($rTemporaryImage, $iTransparency);
                }

                imagecopyresampled($rTemporaryImage, $rOriginalImage, 0, 0, $iCx, $iCy, $iNewW, $iNewH, $iCw, $iCh);
                $bSuccess = imagegif($rTemporaryImage, $sDestination);
                return $bSuccess;
            case IMAGETYPE_PNG:
                $rOriginalImage = imagecreatefrompng($sOriLocation);
                $rTemporaryImage = imagecreatetruecolor($iNewW, $iNewH);

                /* transparantie magie */
                $iTransparency = imagecolortransparent($rOriginalImage);
                if ($iTransparency >= 0) {
                    # If we have a specific transparent color
                    $aTransparencyColor = imagecolorsforindex($rOriginalImage, $iTransparency);

                    # Get the original image's transparent color's RGB values
                    $iTransparency = imagecolorallocate($rTemporaryImage, $aTransparencyColor['red'], $aTransparencyColor['green'], $aTransparencyColor['blue']);

                    # Completely fill the background of the new image with allocated color.
                    imagefill($rTemporaryImage, 0, 0, $iTransparency);

                    # Set the background color for new image to transparent
                    imagecolortransparent($rTemporaryImage, $iTransparency);
                } else {
                    # Always make a transparent background color for PNGs that don't have one allocated already
                    # Turn off transparency blending (temporarily)
                    imagealphablending($rTemporaryImage, false);

                    # Create a new transparent color for image
                    $color = imagecolorallocatealpha($rTemporaryImage, 0, 0, 0, 127);

                    # Completely fill the background of the new image with allocated color.
                    imagefill($rTemporaryImage, 0, 0, $color);

                    # Restore transparency blending
                    imagesavealpha($rTemporaryImage, true);
                }

                imagecopyresampled($rTemporaryImage, $rOriginalImage, 0, 0, $iCx, $iCy, $iNewW, $iNewH, $iCw, $iCh);
                $bSuccess = imagepng($rTemporaryImage, $sDestination);

                // compress image
                if (class_exists('ImageEditorWebserviceManager')) {
                    $aSettings = array();
                    $aCompressedImages = ImageEditorWebserviceManager::getCompressedImages(array(basename($sDestination) => base64_encode(file_get_contents($sDestination))), $aSettings);
                    if (!empty($aCompressedImages['images'])) {
                        // overwrite file with compressed one
                        file_put_contents($sDestination, base64_decode(array_shift($aCompressedImages['images'])));
                    }
                }

                return $bSuccess;
            default:
                $sErrorMsg = 'Geen geldig type `' . $iOriMimeType . '`: png, jpg of gif';
                return false;
        }
    }

    /**
     * resize image to fit specified height
     * @param type $sOriLocation
     * @param type $sDestination (optional - overwrite original)
     * @param type $iH 
     * @param string $sErrorMsg message if error occures (optional)
     * @param int $iJpegQuality quality (optional) -1 by default (takes system or webservice default value)
     * @return bool
     */
    public static function resizeImageH($sOriLocation, $sDestination, $iH, &$sErrorMsg = null, $iJpegQuality = -1) {
        return self::resizeAndCrop($sOriLocation, $sDestination, null, $iH, null, null, null, null, $iJpegQuality, $sErrorMsg);
    }

    /**
     * resize image to fit specified width
     * @param string $sOriLocation
     * @param string $sDestination (optional - overwrite original)
     * @param int $iW 
     * @param string $sErrorMsg message if error occures
     * @param int $iJpegQuality quality (optional) -1 by default (takes system or webservice default value)
     * @return bool
     */
    public static function resizeImageW($sOriLocation, $sDestination, $iW, &$sErrorMsg = null, $iJpegQuality = -1) {
        return self::resizeAndCrop($sOriLocation, $sDestination, $iW, null, null, null, null, null, $iJpegQuality, $sErrorMsg);
    }

    /**
     * resize image to fit specified width and height (fit box)
     * @param string $sOriLocation
     * @param string $sDestination (optional - overwrite original)
     * @param int $iW
     * @param int $iH 
     * @param string $sErrorMsg message if error occures
     * @param int $bAbsoluteSize take width and height as leading, do not scale just resize
     * @param int $iJpegQuality quality (optional) -1 by default (takes system or webservice default value)
     * @return bool
     */
    public static function resizeImage($sOriLocation, $sDestination, $iW, $iH, &$sErrorMsg = null, $bAbsoluteSize = false, $iJpegQuality = -1) {
        return self::resizeAndCrop($sOriLocation, $sDestination, $iW, $iH, null, null, null, null, $iJpegQuality, $sErrorMsg, $bAbsoluteSize);
    }

    /**
     * crop image
     * @param string $sOriLocation
     * @param string $sDestination (optional - overwrite original)
     * @param int $iW width of the crop
     * @param int $iH height of the crop
     * @param int $iCx x position of cropbox
     * @param int $iCy y position of cropbox
     * @param int $iCw width of cropbox
     * @param int $iCh  height of cropbox
     * @param string $sErrorMsg message if error occures
     * @param int $bAbsoluteSize take width and height as leading, do not scale just resize
     * @param int $iJpegQuality quality (optional) -1 by default (takes system or webservice default value)
     * @return bool
     */
    public static function cropImage($sOriLocation, $sDestination, $iW, $iH, $iCx = 0, $iCy = 0, $iCw = null, $iCh = null, &$sErrorMsg = '', $bAbsoluteSize = false, $iJpegQuality = -1) {
        return self::resizeAndCrop($sOriLocation, $sDestination, $iW, $iH, $iCx, $iCy, $iCw, $iCh, $iJpegQuality, $sErrorMsg, $bAbsoluteSize);
    }

    /**
     * autocrop and resize image
     * @param string $sOriLocation
     * @param string $sDestination (optional) 
     * @param int $iCw
     * @param int $iCh
     * @param string $sErrorMsg (optional)
     * @param int $bAbsoluteSize take width and height as leading, do not scale just resize
     * @param int $iJpegQuality quality (optional) -1 by default (takes system or webservice default value)
     * @return bool
     */
    public static function autoCropAndResizeImage($sOriLocation, $sDestination, $iCw, $iCh, &$sErrorMsg = '', $bAbsoluteSize = false, $iJpegQuality = -1) {

        # set dimensions for use after crop
        $iCwForResize = $iCw;
        $iChForResize = $iCh;

        # calculate crop box x,y w,h
        self::getAutoCropBoxSize($sOriLocation, ($iCw / $iCh), $iCx, $iCy, $iCw, $iCh);

        return self::resizeAndCrop($sOriLocation, $sDestination, $iCwForResize, $iChForResize, $iCx, $iCy, $iCw, $iCh, $iJpegQuality, $sErrorMsg, $bAbsoluteSize);
    }

    /**
     * get x,y w,h of biggest possible crop
     * @param string $sOriLocation
     * @param int $iCx
     * @param int $iCy
     * @param int $iCw
     * @param int $iCh 
     */
    public static function getAutoCropBoxSize($sOriLocation, $fRatio, &$iCx, &$iCy, &$iCw, &$iCh) {
        # get image dimensions
        list($iW, $iH) = getimagesize($sOriLocation);

        # ratio of original image
        $fOriRatio = $iW / $iH;

        # ratio of crop
        $fRatio;

        # calculate width and height
        if ($fOriRatio >= 1 && $fRatio >= 1 && $fOriRatio < $fRatio) {
            # both landscape
            $iCw = $iW;
            $iCh = $iCw * (1 / $fRatio);
        } elseif ($fOriRatio >= 1 && $fRatio >= 1 && $fOriRatio > $fRatio) {
            # both landscape
            $iCh = $iH;
            $iCw = $iCh * $fRatio;
        } elseif ($fOriRatio <= 1 && $fRatio <= 1 && $fOriRatio > $fRatio) {
            # both portret
            $iCh = $iH;
            $iCw = $iCh * $fRatio;
        } elseif ($fOriRatio <= 1 && $fRatio <= 1 && $fOriRatio < $fRatio) {
            # both portret
            $iCw = $iW;
            $iCh = $iCw * (1 / $fRatio);
        } elseif ($fOriRatio < 1 && $fRatio > 1) {
            # ori portret, crop landscape
            $iCw = $iW;
            $iCh = $iCw * (1 / $fRatio);
        } elseif ($fOriRatio > 1 && $fRatio < 1) {
            # crop portret, ori landscape
            $iCh = $iH;
            $iCw = $iCh * $fRatio;
        } elseif ($fOriRatio == $fRatio) {
            # both same ratio
            $iCh = $iH;
            $iCw = $iW;
        }

        $iCx = ($iW - $iCw) / 2;
        $iCy = ($iH - $iCh) / 2;
    }

    /**
     * delete an image
     * @param Image $oImage 
     * @return boolean
     */
    public static function deleteImage(Image $oImage) {

        if ($oImage->isDeletable()) {
            $oDb = DBConnections::get();

            # delete all imageFiles
            foreach ($oImage->getImageFiles() AS $oImageFile) {

                if (!empty($oImageFile->link)) {
                    @unlink(DOCUMENT_ROOT . $oImageFile->link);
                }

                // delete all auto resized versions of this ImageFile
                $oImageFile->deleteAutoResized();

                $sQuery = 'DELETE FROM `media` WHERE `mediaId` = ' . db_int($oImageFile->mediaId);
                $oDb->query($sQuery, QRY_NORESULT);
            }

            $sQuery = 'DELETE FROM `images` WHERE `imageId` = ' . db_int($oImage->imageId);

            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        }
        return false;
    }

    /**
     * update online for all imageFiles by Image
     * @param int $iOnline
     * @param int $iImageId
     */
    public static function updateImageFilesOnlineByImage($iOnline, $iImageId) {

        $sQuery = ' UPDATE
                        `media` AS `m`
                    JOIN
                        `imageFiles` AS `if` USING(`mediaId`)
                    SET
                        `m`.`online` = ' . db_int($iOnline) . '
                    WHERE
                        `if`.`imageId` = ' . db_int($iImageId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * update title for all imageFiles by Image
     * @param str $sTitle
     * @param int $iImageId
     */
    public static function updateImageFilesTitleByImage($sTitle, $iImageId) {

        $sQuery = ' UPDATE
                        `media` AS `m`
                    JOIN
                        `imageFiles` AS `if` USING(`mediaId`)
                    SET
                        `m`.`title` = ' . db_str($sTitle) . '
                    WHERE
                        `if`.`imageId` = ' . db_int($iImageId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * update image order
     * @param array $aImageIds 
     */
    public static function updateImageOrder(array $aImageIds) {

        # start query, uses on duplicate key
        $sQuery = " 
                    ";

        # add all imageIds and values
        $iT = 1; //counter
        $sValues = '';
        foreach ($aImageIds AS $iImageId) {
            $sValues .= ($sValues == '' ? '' : ',') . '(' . db_int($iImageId) . ', ' . db_int($iT) . ')';
            $iT++;
        }
        $sQuery .= 'INSERT INTO
                        `images`
                    (
                        `imageId`,
                        `order`
                    )
                    VALUES
                    ' . $sValues . '
                    ON DUPLICATE KEY UPDATE
                        `imageId`=VALUES(`imageId`),
                        `order`=VALUES(`order`)
            ;';

        if (count($aImageIds) > 0) {
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

}

?>
