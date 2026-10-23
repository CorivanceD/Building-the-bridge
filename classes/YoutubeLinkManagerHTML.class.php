<?php

/* Models and managers used by the YoutubeLinkManagerHTML model */
require_once 'Model.class.php';

class YoutubeLinkManagerHTML extends Model {

    public $onlineChangeable = true; // youtubelinks can be set online, offline
    public $changeOnlineLink = null; // send data to link
    public $editable = true; // youtubelinks are editable
    public $editLink = null; // send data to link
    public $deletable = true; // youtubelinks are deletable
    public $deleteLink = null; // send data to link
    public $iContainerIDAddition = 1; // unique addition for the imageManager container 
    public $sUploadUrl = null; // uploadUrl
    public $sEditYoutubeLinkFormLocation; // location of the edit form which will be included once
    public $sortable = true; // youtubelinks are sortable
    public $saveOrderLink = null; // link to save images
    public $sHiddenAction = 'saveYoutubeLink'; // hidden form field with name `action`
    public $template = null; // template for managing youtubelinks
    public $aLinks = array(); // links for displaying under form

    public function __construct(array $aData = array(), $bStripTags = true) {
        # set default links, doesn't work with setting properties directly (constants show erors)
        $this->changeOnlineLink = ADMIN_FOLDER . '/youtubeLinkManagement/ajax-setOnline/';
        $this->editLink = ADMIN_FOLDER . '/youtubeLinkManagement/ajax-edit/';
        $this->deleteLink = ADMIN_FOLDER . '/youtubeLinkManagement/ajax-delete';
        $this->saveOrderLink = ADMIN_FOLDER . '/youtubeLinkManagement/ajax-saveOrder';
        $this->template = ADMIN_TEMPLATES_FOLDER . '/elements/youtubeLinkManagement/youtubeLinkManagerHTML.inc.php';
        $this->sEditYoutubeLinkFormLocation = ADMIN_TEMPLATES_FOLDER . '/elements/youtubeLinkManagement/editYoutubeLinkForm.inc.php';

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
    }

    /**
     * include template for managing linksyoutubeLinks
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
                echo '<li'.sysTranslations::get('classes_editLink_not_set').'</li>';
            if (!$this->isPropValid('deleteLink'))
                echo '<li>'.sysTranslations::get('classes_deleteLink_not_set').'</li>';
            if (!$this->isPropValid('saveOrderLink'))
                echo '<li>'.sysTranslations::get('classes_saveOrderLink_not_set').'</li>';
            if (!$this->isPropValid('template'))
                echo '<li>'.sysTranslations::get('classes_template_not_set').'</li>';
            if (!$this->isPropValid('sUploadUrl'))
                echo '<li>'.sysTranslations::get('classes_sUploadUrl_not_set').'</li>';
            if (!$this->isPropValid('iContainerIDAddition'))
                echo '<li>'.sysTranslations::get('classes_iContainerIDAddition_not_set').'</li>';
            if (!$this->isPropValid('sHiddenAction'))
                echo '<li>'.sysTranslations::get('classes_sHiddenAction_not_set').'</li>';
            echo '</ul>';
        }
    }

}

?>
