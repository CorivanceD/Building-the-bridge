<?php

require_once 'Model.class.php';

class Country extends Model {
    const UNKNOWN_COUNTRY_ID = -1;
    const UN_NY_ID = 228;
    const UN_GE_ID = 229;
    
    public $countryId = null;
    public $name = null;
    public $code = null;
    public $created = null;
    public $modified = null;
    
    public function validate() {
        if (empty($this->name)) {
            $this->setPropInvalid('name');
        }
        if (empty($this->code)) {
            $this->setPropInvalid('name');
        }
    }
}
?>