<?php

/* Models and managers used by the User model */
require_once 'Model.class.php';
require_once 'SettingManager.class.php';

class Setting extends Model {

    public $settingId;
    public $name; //name of the user
    public $value; //username for login
    public $created; //created timestamp
    public $modified; //last modified timestamp

    /**
     * validate object 
     */

    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
    }

}

?>