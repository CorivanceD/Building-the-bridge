<?php

/* Models and managers used by the ImageFile model */
require_once 'File.class.php';

class ImageFile extends File {

    public $imageId;
    public $reference;
    public $type = Media::IMAGE;
    public $online = 1;
    public $imageSizeAttr;
    private $height = null;
    private $width = null;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->imageId))
            $this->setPropInvalid('imageId');
        if (empty($this->reference))
            $this->setPropInvalid('reference');
        parent::validate();
    }

    /**
     * return width of image
     * @return int
     */
    public function getWidth() {
        if (!empty($this->imageSizeAttr) && $this->width === null) {
            $this->setWidthHeight();
        }
        return $this->width;
    }

    /**
     * return height of image
     * @return int
     */
    public function getHeight() {
        if (!empty($this->imageSizeAttr) && $this->height === null) {
            $this->setWidthHeight();
        }
        return $this->height;
    }

    /**
     * set width and height in object based on imageSizeAttr
     */
    private function setWidthHeight() {
        $aMatches = array();
        if (preg_match('#^width="(\d+)" height="(\d+)"$#', $this->imageSizeAttr, $aMatches)) {
            $this->width = $aMatches[1];
            $this->height = $aMatches[2];
        }
    }

    /**
     * get resize link for autocrop and resize an ImageFile via uploads controller
     * @param int $iWidth
     * @param int $iHeight
     */
    public function getResizeLink($iWidth = null, $iHeight = null) {

        $bSameWidth = false;
        $bSameHeight = false;
        if (!empty($iWidth) && $iWidth == $this->getWidth()) {
            $bSameWidth = true;
        }
        if (!empty($iHeight) && $iHeight == $this->getHeight()) {
            $bSameHeight = true;
        }

        if (!empty($iWidth) && !empty($iHeight)) {
            if (!$bSameWidth || !$bSameHeight) {
                return preg_replace('#/' . $this->reference . '/#', '/' . $this->reference . '/autoresized_w' . $iWidth . 'h' . $iHeight . '/', $this->link);
            }
        } elseif (!empty($iWidth) && $iWidth != $this->getWidth()) {
            if (!$bSameWidth) {
                return preg_replace('#/' . $this->reference . '/#', '/' . $this->reference . '/autoresized_w' . $iWidth . '/', $this->link);
            }
        } elseif (!empty($iHeight) && ($iHeight != $this->getHeight())) {
            if (!$bSameHeight) {
                return preg_replace('#/' . $this->reference . '/#', '/' . $this->reference . '/autoresized_h' . $iHeight . '/', $this->link);
            }
        }
        return $this->link;
    }

    /**
     * delete all resized versions of this ImageFile
     */
    public function deleteAutoResized() {

        // get folder of ImageFiles
        $sImageFileFolder = dirname($this->link);

        // loop trough folder to find `autoresized` folders and check for this file
        if (!$rDir = @opendir(DOCUMENT_ROOT . $sImageFileFolder)) {
            return false;
        }

        while (false !== ($sFile = @readdir($rDir))) {
            # not a file name or not a autoresized folder, continue
            if ($sFile == '.' || $sFile == '..' || !is_dir(DOCUMENT_ROOT . '/' . $sImageFileFolder . '/' . $sFile) || !preg_match('#^(autoresized_(?:w(\d+))?(?:h(\d+))?)$#', $sFile)) {
                continue;
            }

            // loop trough folder to find auto resized image files folders and check for this file
            if (!$rAutoresizedDir = @opendir(DOCUMENT_ROOT . '/' . $sImageFileFolder . '/' . $sFile)) {
                return false;
            }
            while (false !== ($sResizedFile = @readdir($rAutoresizedDir))) {
                # not a file name or not a autoresized folder, continue
                if ($sResizedFile == '.' || $sResizedFile == '..' || $sResizedFile != $this->name) {
                    continue;
                }

                // unlink autoresized image
                @unlink(DOCUMENT_ROOT . '/' . $sImageFileFolder . '/' . $sFile . '/' . $sResizedFile);
            }

            # close dir
            @closedir($rAutoresizedDir);
        }

        # close dir
        @closedir($rDir);
    }

}

?>
