<?php

/* Models and managers used by the FileManagerHTML model */
require_once 'Model.class.php';

class FileManagerHTML extends Model {

    public $onlineChangeable = true; // images can be set online, offline
    public $changeOnlineLink = null; // send data to link
    public $editable = true; // files are editable
    public $editLink = null; // send data to link
    public $deletable = true; // files are deletable
    public $deleteLink = null; // send data to link
    public $iContainerIDAddition = 1; // unique addition for the imageManager container 
    public $sUploadUrl = null; // uploadUrl
    public $sEditFileFormLocation; // location of the edit form which will be included once
    public $bMultipleFileUpload = false; // true for SWFUpload
    public $aMultipleFileUploadAllowedExtensions = array(); // empty array is all files
    public $sMultipleFileUploadFileSizeLimit; // file size limit e.g. (20 MB)
    public $sValidateFile = null; // validation string for jQuery validate file input validation eg 'jpg|png|gif'
    public $bTitleRequired = true;
    public $sTitleTitle;
    public $sortable = true; // images are sortable
    public $saveOrderLink = null; // link to save images
    public $sHiddenAction = 'saveFile'; // hidden form field with name `action`
    public $template = null; // template for managing images
    public $iMaxFiles = 0; // max amount of files that can be uploaded 0 is unlimited
    public $aFiles = array(); // files for displaying under form

    public function __construct(array $aData = array(), $bStripTags = true) {
        # set default links, doesn't work with setting properties directly (constants show erors)
        $this->changeOnlineLink = ADMIN_FOLDER . '/fileManagement/ajax-setOnline/';
        $this->editLink = ADMIN_FOLDER . '/fileManagement/ajax-edit/';
        $this->deleteLink = ADMIN_FOLDER . '/fileManagement/ajax-delete';
        $this->saveOrderLink = ADMIN_FOLDER . '/fileManagement/ajax-saveOrder';
        $this->template = ADMIN_TEMPLATES_FOLDER . '/elements/fileManagement/fileManagerHTML.inc.php';
        $this->sEditFileFormLocation = ADMIN_TEMPLATES_FOLDER . '/elements/fileManagement/editFileForm.inc.php';
        $this->sTitleTitle = sysTranslations::get('classes_file_enter_description');

        parent::__construct($aData, $bStripTags);
    }

    /**
     * validate object
     */
    public function validate() {
        if ($this->onlineChangeable && empty($this->changeOnlineLink))
            $this->setPropInvalid('changeOnlineLink');
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
        if ($this->iMaxFiles !== null && !is_numeric($this->iMaxFiles))
            $this->setPropInvalid('iMaxFiles');
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
            if (!$this->isPropValid('iMaxFiles'))
                echo '<li>'.sysTranslations::get('classes_sHiddenAction _not_set').'</li>';
            echo '</ul>';
        }
    }

}

?>
