<?php

/* Models and managers used by the Page model */
require_once 'Model.class.php';

class Media extends Model {
    const IMAGE = 'image'; //image file
    const LINK = 'link'; //link
    const YOUTUBE = 'youtube'; //youtube link
    const FILE = 'file'; // regular file

    public $mediaId;
    public $link;
    public $title;
    public $type;
    public $online = 0;
    public $order = 99999;
    public $modified;
    public $created;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->link))
            $this->setPropInvalid('link');
        if (empty($this->type))
            $this->setPropInvalid('type');
        if ($this->online === null)
            $this->setPropInvalid('online');
        if ($this->order === null)
            $this->setPropInvalid('order');
    }
    
    /**
     * check if media item is online
     * @return boolean 
     */
    public function isOnline(){
        return $this->online;
    }

}

?>
