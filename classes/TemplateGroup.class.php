<?php

/* Models and managers used by the Template model */
require_once 'Model.class.php';
require_once 'TemplateManager.class.php';

class TemplateGroup extends Model {

    public $templateGroupId;
    public $templateGroupName; // description of the template, where is it used for
    public $templateVariables; //text field with usable template variables [variable_replace_name]

    /**
     * validate object 
     */

    public function validate() {
        if (empty($this->templateGroupName))
            $this->setPropInvalid('templateGroupName');
    }

}

?>