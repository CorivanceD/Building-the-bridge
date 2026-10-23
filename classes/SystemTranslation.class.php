<?php

class SystemTranslation extends Model {

    public $systemTranslationId = null;
    public $systemLanguageId = null;
    public $label = null;
    public $text = null;
    private $oSystemLanguage = null;

    function validate() {
        if (empty($this->systemLanguageId)) {
            $this->setPropInvalid('systemLanguageId');
        }
        if (empty($this->label)) {
            $this->setPropInvalid('label');
        }

        $oDuplicate = SystemTranslationManager::getTranslationByLabel($this->systemLanguageId, $this->label);
        if ($oDuplicate && $oDuplicate->systemTranslationId != $this->systemTranslationId) {
            $this->setPropInvalid('systemTranslationLabel');
        }

        if (empty($this->text)) {
            $this->setPropInvalid('text');
        }
    }
    
    public function getLanguage(){
        if($this->oSystemLanguage === null)
            $this->oSystemLanguage = SystemLanguageManager::getLanguageById ($this->systemLanguageId);
        return $this->oSystemLanguage;
    }

}
?>

