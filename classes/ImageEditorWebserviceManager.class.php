<?php

class ImageEditorWebserviceManager extends WebserviceManager {

    protected static $name = 'imageeditor'; // name of the API

    public static function getCompressedImages(array $aImages = array(), array $aSettings = array()) {
        return static::getData('compress', array('images' => $aImages, 'settings' => $aSettings));
    }

}

?>