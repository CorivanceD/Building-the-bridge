<?php
include_once 'Model.class.php';

class Petition extends Model {
    const IMAGES_PATH = '/images/petitions';
    public $petitionId;
    public $author;
    public $countryId;
    public $email;
    public $phone;
    public $title;
    public $intro;
    public $description;
    public $imageId;
    public $requiredSignatures;
    public $online = 0;
    public $created;
    public $modified;
    
    public function validate() {
        if (empty($this->title)) {
            $this->setPropInvalid('title');
        }
        
        if (empty($this->intro)) {
            $this->setPropInvalid('intro');
        }        
        
        if (empty($this->description)) {
            $this->setPropInvalid('description');
        }
        
        if (!is_numeric($this->requiredSignatures) || intval($this->requiredSignatures) <= 0) {
            $this->setPropInvalid('requiredSignatures');
        }
    }
    
    /**
     * 
     * @return array PetitionSignature
     */
    public function getSignatures() {
        return PetitionManager::getPetitionSignatures($this->petitionId);
    }
    
    /**
     * 
     * @return boolean
     */
    public function isEditable() {
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isDeletable() {
        return count($this->getSignatures()) === 0;
    }
    
    /**
     * 
     * @return Image
     */
    public function getImage() {
        return empty($this->imageId) ? null : ImageManager::getImageById($this->imageId);
    }
}
?>