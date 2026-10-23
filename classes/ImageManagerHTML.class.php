<?php

/* Models and managers used by the ImageManagerHTML model */
require_once 'Model.class.php';

class ImageManagerHTML extends Model {

    public $onlineChangeable = true; // images can be set online, offline
    public $changeOnlineLink = null; // send data to link
    public $cropable = true; // images are cropable
    public $cropLink = null; // go to crop link
    public $editable = true; // images are editable
    public $editLink = null; // send data to link
    public $deletable = true; // images are deletable
    public $deleteLink = null; // send data to link
    public $bShowCropAfterUploadOption = true; // display option to crop directly after upload
    public $bCropAfterUploadChecked = true; // default value -> true = checked false = not checked
    public $iContainerIDAddition = 1; // unique addition for the imageManager container 
    public $sUploadUrl = null; // uploadUrl
    public $sEditImageFormLocation; // location of the edit form which will be included once
    public $bMultipleFileUpload = false; // true for SWFUpload
    public $sMultipleFileUploadFileSizeLimit; // file size limit e.g. (20 MB)
    public $aMultipleFileUploadAllowedExtensions = array(); // empty array is all files
    public $sValidateFile = 'png|jpeg|jpg|gif'; // validation string for jQuery validate file input validation
    public $sExtraUploadLine = ''; // extra line of info above upload field
    public $sExtraUploadedLine = ''; // extra line of info above uploaded images
    public $sortable = true; // images are sortable
    public $saveOrderLink = null; // link to save images
    public $sHiddenAction = 'saveImage'; // hidden form field with name `action`
    public $template = null; // template for managing images
    public $iMaxImages = 0; // max amount of images that can be uploaded 0 is unlimited
    public $aImages = array(); // images for displaying under form
    public $aNeededImageFileReferences = array(); // array of imagefilereferences to make image 'valid', not valid will result in a exclamation mark at the thumb
    public $bCoverImageShow = false;
    public $sCoverImageTitle = 'Voor overzicht';
    public $oCoverImageImageFile = null;
    public $sCoverImageUpdateLink = null;
    public $sCoverImageSuccessText = null;
    public $sCoverImageErrorText = null;
    public $sCoverImageGetLink = null;
    private $aExtraOptions = null; // array with arrays(label, value) for use in selectbox

    public function __construct(array $aData = array(), $bStripTags = true) {
        # set default links, doesn't work with setting properties directly (constants show erors)
        $this->changeOnlineLink = ADMIN_FOLDER . '/imageManagement/ajax-setOnline/';
        $this->editLink = ADMIN_FOLDER . '/imageManagement/ajax-edit/';
        $this->deleteLink = ADMIN_FOLDER . '/imageManagement/ajax-delete';
        $this->saveOrderLink = ADMIN_FOLDER . '/imageManagement/ajax-saveOrder';
        $this->template = ADMIN_TEMPLATES_FOLDER . '/elements/imageManagement/imageManagerHTML.inc.php';
        $this->sEditImageFormLocation = ADMIN_TEMPLATES_FOLDER . '/elements/imageManagement/editImageForm.inc.php';
        $this->sCoverImageSuccessText = sysTranslations::get('global_cover_image_success');
        $this->sCoverImageErrorText = sysTranslations::get('global_cover_image_error');
        parent::__construct($aData, $bStripTags);
    }

    /**
     * validate object
     */
    public function validate() {
        if ($this->onlineChangeable && empty($this->changeOnlineLink))
            $this->setPropInvalid('changeOnlineLink');
        if ($this->cropable && empty($this->cropLink))
            $this->setPropInvalid('cropLink');
        if ($this->editable && empty($this->editLink))
            $this->setPropInvalid('editLink');
        if ($this->deletable && empty($this->deleteLink))
            $this->setPropInvalid('deleteLink');
        if ($this->sortable && empty($this->saveOrderLink))
            $this->setPropInvalid('saveOrderLink');
        if (empty($this->template))
            $this->setPropInvalid('template');
        if (empty($this->sUploadUrl))
            $this->setPropInvalid('sUploadUrl');
        if (empty($this->iContainerIDAddition))
            $this->setPropInvalid('iContainerIDAddition');
        if (empty($this->sHiddenAction))
            $this->setPropInvalid('sHiddenAction');
        if ($this->iMaxImages !== null && !is_numeric($this->iMaxImages))
            $this->setPropInvalid('iMaxImages');
        if ($this->bCoverImageShow) {
            if (empty($this->sCoverImageUpdateLink))
                $this->setPropInvalid('sCoverImageUpdateLink');
            if (empty($this->sCoverImageGetLink))
                $this->setPropInvalid('sCoverImageGetLink');
            if (!empty($this->oCoverImageImageFile) && !($this->oCoverImageImageFile instanceof ImageFile))
                $this->setPropInvalid('oCoverImageImageFile');
        }
    }

    /**
     * include template for managing images
     * @global string $oPageLayout 
     */
    public function includeTemplate() {
        global $oPageLayout; // pageLayout is needed here for javascript adding

        if ($this->isValid()) {
            include $this->template;
        } else {
            echo '<b>'.sysTranslations::get('classes_config_missing').'</b>';
            echo '<ul class="standard">';
            if (!$this->isPropValid('changeOnlineLink'))
                echo '<li>'.sysTranslations::get('classes_changeOnlineLink_not_set').'</li>';
            if (!$this->isPropValid('cropLink'))
                echo '<li>'.sysTranslations::get('classes_cropLink_not_set').'</li>';
            if (!$this->isPropValid('editLink'))
                echo '<li>'.sysTranslations::get('classes_editLink_not_set').'</li>';
            if (!$this->isPropValid('deleteLink'))
                echo '<li>'.sysTranslations::get('classes_deleteLink_not_set').'</li>';
            if (!$this->isPropValid('saveOrderLink'))
                echo '<li>'.sysTranslations::get('classes_sortableLink_not_set').'</li>';
            if (!$this->isPropValid('template'))
                echo '<li>'.sysTranslations::get('classes_template_not_set').'</li>';
            if (!$this->isPropValid('sUploadUrl'))
                echo '<li>'.sysTranslations::get('classes_sUploadUrl_not_set').'</li>';
            if (!$this->isPropValid('iContainerIDAddition'))
                echo '<li>'.sysTranslations::get('classes_iContainerIDAddition_not_set').'</li>';
            if (!$this->isPropValid('sHiddenAction'))
                echo '<li>'.sysTranslations::get('classes_sHiddenAction _not_set').'</li>';
            if (!$this->isPropValid('iMaxImages'))
                echo '<li>'.sysTranslations::get('classes_iMaxImages_not_int').'</li>';
            if (!$this->isPropValid('sCoverImageUpdateLink'))
                echo '<li>'.sysTranslations::get('classes_cover_image_link_not_set').'</li>';
            if (!$this->isPropValid('sCoverImageGetLink'))
                echo '<li>'.sysTranslations::get('classes_get_cover_image_link_not_set').'</li>';
            if (!$this->isPropValid('oCoverImageImageFile'))
                echo '<li>'.sysTranslations::get('classes_imageFile_not_valid').'</li>';
            echo '</ul>';
        }
    }

    /**
     * add an option to the extra options selectbox
     * @param string $sOptionName
     * @param string $sOptionValue
     * @param string $sOptionLabel 
     */
    public function addExtraOption($sOptionLabel, $sOptionValue) {
        if ($this->aExtraOptions === null) {
            $this->aExtraOptions = array();
        }
        $this->aExtraOptions[] = array($sOptionLabel, $sOptionValue);
    }

    /**
     * return extra options array
     * @return array 
     */
    public function getExtraOptions() {
        return $this->aExtraOptions;
    }

}

?>
