<?php

require_once 'Model.class.php';

class NewsSourceLogo extends Model {
    const IMAGES_PATH = '/images/newssourcelogo';     
    public $newsSourceLogoId;
    public $sourceName;
    public $imageId = null;
    private $oImage = null;
    
    public function validate() {
        if (empty($this->sourceName)) {
            $this->setPropInvalid('sourceName');
        }
    }
    
    /**
     * 
     * @return Image
     */
    public function getImage() {
        if (!empty($this->imageId) && empty($this->oImage)) {
            $this->oImage = ImageManager::getImageById($this->imageId);
        }
        
        return $this->oImage;
    }
    
}
?>

