<?php

/*
 * This is an abstract class which means you cannot create an object of this class.
 * Use this class as an extend on a model class for loading data into the object.
 */

abstract class Model {

    protected $bIsValid = null;
    protected $aInvalidProps = array();

    /*
     * an object always needs a validate function
     */

    abstract protected function validate();

    /**
     * constructor can load data into the object through the _load function
     * @param $aData optional array of data
     */
    public function __construct(array $aData = array(), $bStripTags = true) {
        $this->_load($aData, $bStripTags);
    }

    /**
     * the load function will actualy load the data given in param $aData into the object itself, but only public class vars will be filled
     * @param Array $aData of data to be loaded in itself
     * @param Boolean $bStripTags strip tags from properties (optional, default = true)
     */
    public function _load(array $aData, $bStripTags = true) {
        $oReflection = new ReflectionObject($this);
        foreach ($aData as $sPropName => $mValue) {
            if (property_exists($this, $sPropName) && $oReflection->getProperty($sPropName)->isPublic())
                $this->$sPropName = ($bStripTags ? strip_tags($mValue) : $mValue);
        }
    }

    /**
     * is the page object valid
     * @return boolean
     */
    public function isValid() {
        $this->bIsValid = true;
        $this->aInvalidProps = array();
        $this->validate();
        return $this->bIsValid;
    }

    /**
     * is a property valid
     * @param $sPropName string property name
     * @return boolean
     */
    public function isPropValid($sPropName) {
        if (in_array($sPropName, $this->aInvalidProps))
            return false;
        return true;
    }

    /**
     * set a property and the page as invalid
     * @param $sPropName string property name
     */
    protected function setPropInvalid($sPropName) {
        $this->aInvalidProps[] = $sPropName;
        $this->bIsValid = false;
    }
    
    /**
     * return all invalid props
     * @return array
     */
    public function getInvalidProps() {
        return $this->aInvalidProps;
    }

}

?>
