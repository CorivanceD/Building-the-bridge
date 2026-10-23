<?php

/* Models and managers used by the User model */
require_once 'Model.class.php';
require_once 'ModuleManager.class.php';

class User extends Model {

    const ADMINISTRATOR = 1; // ID of administrator account
    const ANONYMOUS_USER_ID = -1;
    const USER_CLIENT_ADMIN = 10;
    const USER_EDITOR = 20;
    const USER_WRITER = 30;
    const IMAGES_PATH = '/images/user';
    
    public $userId = null;
    public $name; //name of the user
    public $username; //username for login
    public $password; //password for login
    public $email;
    public $userProfile;
    private $administrator = 0; //user is full Administrator user
    private $seo = 0; //user is full SEO user
    public $systemLanguageId = null; //user language
    public $editorId = null;
    public $imageId = null;
    public $description = null;
    private $aModules = null; //array with lists of modules
    private $oLanguage = null;
    public $created; //created timestamp
    public $modified; //last modified timestamp

    /**
     * validate object 
     */

    public function validate() {
        if (empty($this->username))
            $this->setPropInvalid('username');
        if (empty($this->password))
            $this->setPropInvalid('password');
        if (empty($this->systemLanguageId))
            $this->setPropInvalid ('systemLanguageId');
        if (empty($this->name))
            $this->setPropInvalid('name');
        $oUser = UserManager::getUserByUsername($this->username);
        if ($oUser && $oUser->userId != $this->userId)
            $this->setPropInvalid('username');
        
//        $oUser = UserManager::getUserByEmail($this->email);        
//        if ($oUser && $oUser->email != $this->email)
//            $this->setPropInvalid('email');        
    }

    /**
     * mask pasword for session use
     */
    function maskPass() {
        $this->password = "XXX";
    }

    /**
     * get user modules
     * @return array of module objects
     */
    public function getModules($sList = 'active-menu') {
        if (!isset($this->aModules[$sList])) {
            switch ($sList) {
                case 'active-menu':
                    $this->aModules[$sList] = ModuleManager::getModulesByFilter(array('parentModuleId' => -1));
                    break;
                case 'active-all':
                    $this->aModules[$sList] = ModuleManager::getModulesByFilter(array('showAll' => 1, 'checkRights' => 1, 'active' => 1));
                    break;
                case 'user-all':
                    $this->aModules[$sList] = ModuleManager::getModulesByFilter(array('showAll' => 1, 'active' => 1, 'userId' => $this->userId));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aModules[$sList];
    }

    /**
     * set user modules
     * @param array of module objects
     */
    public function setModules(array $aModules, $sList = 'active-all') {
        $this->aModules[$sList] = $aModules;
    }

    /**
     * check if user has rights to see module
     * @param string $sModuleName
     * @return boolean
     */
    public function hasRightsFor($sModuleName) {
        $bResult = false;
        foreach ($this->getModules('user-all') AS $oModule) {
            if (strtolower($sModuleName) == strtolower($oModule->name)) {
                $bResult = true;
                break;
            }
        }
        return $bResult;
    }

    /**
     * return if user has administartor rights
     * @return boolean
     */
    public function isAdmin() {
        return $this->administrator;
    }

    /**
     * return if user has SEO rights
     * @return boolean
     */
    public function isSEO() {
        return $this->seo;
    }        

    /**
     * set administrator
     * @return boolean
     */
    public function setAdministrator($bAdministartor) {
        $this->administrator = $bAdministartor;
    }

    /**
     * set seo
     * @return boolean
     */
    public function setSEO($bSeo) {
        $this->seo = $bSeo;
    }

    /**
     * get administrator value
     * @return type
     */
    public function getAdministrator() {
        return $this->administrator;
    }

    /**
     * get seo value
     * @return type
     */
    public function getSeo() {
        return $this->seo;
    }

    public function isEditable() {
        global $oCurrentUser;
        return $this->userId != self::ADMINISTRATOR || $this->userId == $oCurrentUser->userId;
    }

    public function isDeletable() {
        return $this->userId != self::ADMINISTRATOR;
    }

    /**
     * check if user is superadmin
     * @return type
     */
    public function isAsideAdmin() {
        return $this->userId == self::ADMINISTRATOR;
    }

    /**
     * 
     * @return SystemLanguage
     */
    public function getLanguage() {
        return SystemLanguageManager::getLanguageById($this->systemLanguageId);
    }
    
    /**
     * 
     * @return Image
     */
    public function getImage() {
        return ImageManager::getImageById($this->imageId);
    }
    
    public function getEditor() {
        if (empty($this->editorId)) {
            return $this;
        }
        else {
            return UserManager::getUserById($this->editorId);
        }
    }
}
?>