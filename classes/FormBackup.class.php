<?php

/* Models and managers used by the FormBackup model */
require_once 'Model.class.php';

class FormBackup extends Model {

    public $formBackupId;
    public $name; //form name (could be descriptive)
    public $serializedFormData; //serialized post data (php serialize)
    public $extraInfo; //optional extra info if needed
    public $created;
    public $modified;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
        if (empty($this->serializedFormData))
            $this->setPropInvalid('serializedPostData');
    }
    
    /**
     * unserialize formData and return array
     * @return array
     */
    public function getFormData(){
        return unserialize($this->serializedFormData);
    }

}

?>
