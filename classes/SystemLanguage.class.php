<?php

require_once 'Model.class.php';

class SystemLanguage extends Model {
    
    const default_languageId = 1;

    public $systemLanguageId;
    public $abbr;
    public $name;

    public function validate() {
        
    }

}
?>

