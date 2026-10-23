<?php

/* Models and managers used by the CropSettings model */
require_once 'Model.class.php';

class CropSettings extends Model {

    const TMP_FOLDER = '/admin/tmp';

    public $bDeleteOriginal = false; // delete original
    public $bShowPreview = true; // show preview
    public $iMaxPreviewWidth; // max width of the preview image (optional) default = fit space
    public $sName = null; // name of crop
    public $sReferenceName; // referenceName from imageFile to use for cropping
    public $iImageId; // id of the image to save crops for
    public $sReferrer; // location go to after crop
    public $sReferrerTekst; // text to show on referrer link
    public $bAutoCropBoxSize = true; // automatically take biggest cropbox possible
    protected $aCrops = null; // crop dimensions array(w,h,destination, reference name, show under cropbox, absolute size) (optional)
    protected $fAspectRatio = null; // float (optional)
    protected $aMinSize = null; //array(w,h) (optional)
    protected $aMaxSize = null; //array(w,h) (optional)
    protected $aCropBoxSize = null; //array(x,y,x2,y2) (optional)

    /**
     * validate object
     */

    protected function validate() {
        if (empty($this->sReferenceName))
            $this->setPropInvalid('sReferenceName');
        if (empty($this->sReferrer))
            $this->setPropInvalid('sReferrer');
        if (empty($this->sReferrerTekst))
            $this->setPropInvalid('sReferrerTekst');
        if ($this->fAspectRatio !== null && !is_float($this->fAspectRatio))
            $this->setPropInvalid('fAspectRatio');
        if ($this->aResizes !== null && !is_array($this->aResizes)) {
            $this->setPropInvalid('aResizes');
        } elseif (is_array($this->aResizes)) {
            # check all resizes size for 7 values
            foreach ($this->aResizes AS $aResizeInfo) {
                if (count($aResizeInfo) != 7) {
                    $this->setPropInvalid('aResizes');
                    break;
                }
            }
        }

        if ($this->aMinSize !== null && !is_array($this->aMinSize)) {
            $this->setPropInvalid('aMinSize');
        } elseif (is_array($this->aMinSize) && (!is_array($this->aMinSize) || !is_int($this->aMinSize[0]) || !is_int($this->aMinSize[1]))) {
            $this->setPropInvalid('aMinSize');
        }
        if ($this->aMaxSize !== null && !is_array($this->aMaxSize)) {
            $this->setPropInvalid('aMaxSize');
        } elseif (is_array($this->aMaxSize) && (!is_array($this->aMaxSize) || !is_int($this->aMaxSize[0]) || !is_int($this->aMaxSize[1]))) {
            $this->setPropInvalid('aMaxSize');
        }
        if ($this->aCropBoxSize !== null && !is_array($this->aCropBoxSize)) {
            $this->setPropInvalid('aCropBoxSize');
        } elseif (is_array($this->aCropBoxSize) && (count($this->aCropBoxSize) != 4 || !is_int($this->aCropBoxSize[0]) || !is_int($this->aCropBoxSize[1]) || !is_int($this->aCropBoxSize[2]) || !is_int($this->aCropBoxSize[3]))) {
            $this->setPropInvalid('aCropBoxSize');
        }
    }

    /**
     * add a crop to the crops array
     * @param int $iW
     * @param int $iH
     * @param string $sDestination
     * @param string $sReferenceName name of the imageFile reference
     * @param bool $bShowUnderCropBox show onder just created crops default true
     * @param bool $bAbsoluteSize do not scale, just make exact size default false
     * @param int $iJpegQuality quality of jpeg 0-100 default -1 (takes default from system settings or webservice)
     */
    public function addCrop($iW, $iH, $sDestination, $sReferenceName, $bShowUnderCropBox = true, $bAbsoluteSize = false, $iJpegQuality = -1) {
        if ($this->aCrops === null) {
            $this->aCrops = array();
        }
        $this->aCrops[] = array($iW, $iH, $sDestination, $sReferenceName, $bShowUnderCropBox, $bAbsoluteSize, $iJpegQuality);
    }

    /**
     * return all crops
     * @param return array
     */
    public function getCrops() {
        if ($this->aCrops === null) {
            $this->aCrops = array();
        }
        return $this->aCrops;
    }

    /**
     * set aspect ratio for crop box
     * @param int $iW
     * @param int $iH
     * @param float $fRatio (optional)
     */
    public function setAspectRatio($iW, $iH, $fRatio = null) {
        if ($fRatio !== null) {
            $this->fAspectRatio = $fRatio;
        } else {
            $this->fAspectRatio = $iW / $iH;
        }
    }

    /**
     * return aspect ratio for crop box
     * @return float
     */
    public function getAspectRatio() {
        return $this->fAspectRatio;
    }

    /**
     * set min size of crop box
     * @param int $iW
     * @param int $iH
     */
    public function setMinSize($iW, $iH) {
        $this->aMinSize = array($iW, $iH);
    }

    /**
     * get min size for crop box
     * @return array
     */
    public function getMinSize() {
        return $this->aMinSize;
    }

    /**
     * set max size of crop box
     * @param int $iW
     * @param int $iH
     */
    public function setMaxSize($iW, $iH) {
        $this->aMaxSize = array($iW, $iH);
    }

    /**
     * get max size for crop box
     * @return array
     */
    public function getMaxSize() {
        return $this->aMaxSize;
    }

    /**
     * set size of cropbox
     * @param int $iX
     * @param int $iY
     * @param int $iW
     * @param int $iH
     */
    public function setCropBoxSize($iX, $iY, $iW, $iH) {
        $iW += $iX; // add X to width
        $iH += $iY; // add Y to height
        $this->aCropBoxSize = array($iX, $iY, $iW, $iW);
    }

    /**
     * get size of cropbox
     * @return array
     */
    public function getCropBoxSize() {
        return $this->aCropBoxSize;
    }

}

?>
