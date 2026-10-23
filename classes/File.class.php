<?php

/* Models and managers used by the File model */
require_once 'Media.class.php';

class File extends Media {

    public $name;
    public $mimeType;
    public $size;
    public $type = Media::FILE;
    public $online = 1;
    

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
        if (empty($this->title))
            $this->setPropInvalid('title');
        if (empty($this->mimeType))
            $this->setPropInvalid('mimeType');
        if (empty($this->size))
            $this->setPropInvalid('size');
        parent::validate(); //validate parent (media values)
    }

    /**
     * check if file online/offline may be changed
     * @return bool 
     */
    public function isOnlineChangeable() {
        return true;
    }

    /**
     * check if file is editable
     * @return bool 
     */
    public function isEditable() {
        return true;
    }

    /**
     * check if file is deletable
     * @return bool 
     */
    public function isDeletable() {
        return true;
    }

    /**
     * return file extension
     */
    public function getExtension() {
        // extensions that start with a number, add 'x-' 
        return preg_replace('/^([0-9]){1}/i', 'x-$1', strtolower(pathinfo($this->link, PATHINFO_EXTENSION)));
    }

}

?>
