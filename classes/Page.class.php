<?php

/* Models and managers used by the Page model */
require_once 'Model.class.php';
require_once 'PageManager.class.php';
require_once 'ImageManager.class.php';
require_once 'FileManager.class.php';
require_once 'LinkManager.class.php';
require_once 'YoutubeLinkManager.class.php';

class Page extends Model {

    const FILES_PATH = '/files/page';
    const IMAGES_PATH = '/images/page';

    const WORLD_LEADER_PAGE = 127;

    public $pageId = null;
    public $windowTitle = null; //browser window title
    public $metaKeywords = null; //meta tag keywords
    public $metaDescription = null; //meta tag description
    public $title = null; //page title
    public $content = null; //page content
    public $shortTitle = null; //short title f.e. for use in menus
    public $parentPageId = null; //page parent id, if set then this is a subpage
    public $online = 1; //0 no, 1 yes
    private $urlPath = null; // unique part of the url e.g. http://www.website.nl[/url/part].html?test=1
    private $urlPart = null; // part of the url to use in stead of short link text
    private $urlParameters = null; // part of the url to set some parameters
    private $controllerPath = '/page.cont.php'; //path to the controller to handle this object
    public $created = null; //created timestamp
    public $modified = null; //last modified timestamp
    public $order = 99999; //for ordering pages
    public $level = 1; //main pages have level 0
    private $onlineChangeable = 1; //online is changeable 1 by default
    private $editable = 1; //editable 1 by default
    private $deletable = 1; //deletable 1 by default
    private $inMenu = 1; //inMenu 1 by default
    private $mayHaveSub = 1; //mayHaveSub 1 by default
    private $lockUrlPath = 0; //url path does not change with title 0 by default
    private $lockParent = 0; //do not change parentPageId 0 by default
    private $hideImageManagement = 0; //do not hide imagemanagement by default
    private $hideFileManagement = 0; //do not hide filemanagement 0 by default
    private $hideLinkManagement = 0; //do not hide linkmanagement 0 by default
    private $hideYoutubeLinkManagement = 0; //do hide youtubelinkmanagement 0 by default
    private $hideRssManagement = 0; //do hide rss management 0 by default
    public $hasArticles = 0;
    private $aSubPages = null; //array with different lists of sub pages
    private $aImages = null; //array with different lists of images
    private $aFiles = null; //array with different lists of files
    private $aLinks = null; //array with different lists of links
    private $aYoutubeLinks = null; //array with different lists of youtube links
    private $oParent = null; //parent page

    /**
     * validate object
     */

    public function validate() {
        if (empty($this->controllerPath))
            $this->setPropInvalid('controllerPath');
        if (empty($this->title))
            $this->setPropInvalid('title');
        if (!is_numeric($this->online))
            $this->setPropInvalid('online');
    }

    /**
     * return all subpages for this page
     */
    public function getSubPages($sList = 'online-menu') {
        if (!isset($this->aSubPages[$sList])) {
            switch ($sList) {
                case 'online-menu':
                    $this->aSubPages[$sList] = PageManager::getPagesByFilter(array('parentPageId' => $this->pageId));
                    break;
                case 'online-all':
                    $this->aSubPages[$sList] = PageManager::getPagesByFilter(array('parentPageId' => $this->pageId, 'showAll' => 1, 'online' => 1));
                    break;
                case 'all':
                    $this->aSubPages[$sList] = PageManager::getPagesByFilter(array('parentPageId' => $this->pageId, 'showAll' => 1));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aSubPages[$sList];
    }

    /**
     * check if page has subs
     */
    public function hasSubPages($sList = 'online-menu') {
        return count($this->getSubPages($sList)) > 0;
    }

    /**
     * get url to page optional with extension .html
     * @param boolean $bWithExtension (optional) DEFAULT false
     * @return string
     */
    public function getUrlPath($bWithExtension = false) {
        if ($bWithExtension)
            return $this->urlPath . '.html';
        return $this->urlPath;
    }

    /**
     * return part of the url for this page
     * @return string
     */
    public function getUrlPart() {
        return $this->urlPart;
    }

    /**
     * set url parameters
     * @param string $sUrlPart
     */
    public function setUrlParameters($sUrlParameters) {
        $this->urlParameters = $sUrlParameters;
    }
    
    /**
     * return the url parameters
     * @return string
     */
    public function getUrlParameters() {
        return $this->urlParameters;
    }    

    /**
     * set url part for this page
     * @param string $sUrlPart
     */
    public function setUrlPart($sUrlPart) {
        $this->urlPart = prettyUrlPart($sUrlPart);
    }    

    /**
     * return modified timestamp in format
     * @param string $sFormat (optional)
     */
    public function getModified($sFormat = '%d-%m-%Y %H:%M') {
        return Date::strToDate($this->modified)->format($sFormat);
    }

    /**
     * return controller path
     * @return string
     */
    public function getControllerPath() {
        return $this->controllerPath;
    }

    /**
     * get parent
     * @return Page object
     */
    public function getParent() {
        if ($this->oParent === null)
            $this->oParent = PageManager::getPageById($this->parentPageId);
        return $this->oParent;
    }

    /**
     * generate a urlPath for this page
     * @return string generated urlPath
     */
    public function generateUrlPath() {
        $sUrlPath = '';
        $oParentPage = $this->getParent();
        if ($oParentPage)
            $sUrlPath = $oParentPage->getUrlPath(false);
        return $sUrlPath . '/' . (!empty($this->urlPart) ? $this->urlPart : prettyUrlPart($this->getShortTitle()));
    }

    /**
     * generate urlPath and set in object
     */
    public function setUrlPath() {
        $this->urlPath = $this->generateUrlPath();
    }

    /**
     * calculate level and set in object
     */
    public function setLevel() {
        if ($this->parentPageId) {
            $this->level = $this->getParent()->level + 1;
        } else {
            $this->level = 1;
        }
    }

    /**
     * check if page is editable
     * @return boolean
     */
    public function isEditable() {
        global $oCurrentUser;
        return $this->editable || ($oCurrentUser && $oCurrentUser->isAdmin()); // admin can always edit pages
    }

    /**
     * check if page is online changeable
     * @return boolean
     */
    public function isOnlineChangeable() {
        return $this->onlineChangeable;
    }

    /**
     * check if page is deletable
     * @return boolean
     */
    public function isDeletable() {
        return $this->hasSubPages() === false && $this->deletable;
    }

    /**
     * check showing page in menu
     * @return boolean
     */
    public function showInMenu() {
        return $this->inMenu && $this->online;
    }

    /**
     * check if page can have sub page
     * @return boolean
     */
    public function mayHaveSub() {
        return $this->mayHaveSub;
    }

    /**
     * just returns integer, DO NOT USE FOR IS MAY HAVE SUB CHECKING
     * @return boolean
     */
    public function getMayHaveSub() {
        return $this->mayHaveSub;
    }

    /**
     * set onlineChangeable
     * @param boolean $bOnlineSchangeable
     */
    public function setOnlineChangeable($bOnlineSchangeable) {
        $this->onlineChangeable = $bOnlineSchangeable;
    }

    /**
     * set controllerPath
     * @param string $sControllerPath
     */
    public function setControllerPath($sControllerPath) {
        $this->controllerPath = $sControllerPath;
    }

    /**
     * set editable
     * @param boolean $bEditable
     */
    public function setEditable($bEditable) {
        $this->editable = $bEditable;
    }

    /**
     * set deletable
     * @param boolean $bDeletable
     */
    public function setDeletable($bDeletable) {
        $this->deletable = $bDeletable;
    }

    /**
     * set inMenu
     * @param boolean $bInMenu
     */
    public function setInMenu($bInMenu) {
        $this->inMenu = $bInMenu;
    }

    /**
     * set mayHavesub
     * @param boolean $bMayHavesub
     */
    public function setMayHaveSub($bMayHavesub) {
        $this->mayHaveSub = $bMayHavesub;
    }

    /**
     * set lockUrlPath
     * @param boolean $bLockUrlPath
     */
    public function setLockUrlPath($bLockUrlPath) {
        $this->lockUrlPath = $bLockUrlPath;
    }

    /**
     * set lockParent
     * @param boolean $bLockParent
     */
    public function setLockParent($bLockParent) {
        $this->lockParent = $bLockParent;
    }

    /**
     * set hideImageManagement
     * @param boolean $bHideImageManagement
     */
    public function setHideImageManagement($bHideImageManagement) {
        $this->hideImageManagement = $bHideImageManagement;
    }

    /**
     * set hideFileManagement
     * @param boolean $bHideFileManagement
     */
    public function setHideFileManagement($bHideFileManagement) {
        $this->hideFileManagement = $bHideFileManagement;
    }

    /**
     * set hideLinkManagement
     * @param boolean $bHideLinkManagement
     */
    public function setHideLinkManagement($bHideLinkManagement) {
        $this->hideLinkManagement = $bHideLinkManagement;
    }

    /**
     * set hideYoutubeLinkManagement
     * @param boolean $bHideYoutubeLinkManagement
     */
    public function setHideYoutubeLinkManagement($bHideYoutubeLinkManagement) {
        $this->hideYoutubeLinkManagement = $bHideYoutubeLinkManagement;
    }
    
    /**
     * 
     * @param boolean $bHideRssManagement
     */
    public function setHideRssManagement($bHideRssManagement) {
        $this->hideRssManagement = $bHideRssManagement;
    } 

    /**
     * just returns integer, DO NOT USE FOR IS ONLINECHANGEABLE CHECKING
     * return value of onlineChangeable
     * @return int
     */
    public function getOnlineChangeable() {
        return $this->onlineChangeable;
    }

    /**
     * just returns integer, DO NOT USE FOR IS EDITABLE CHECKING
     * return value of editable
     * @return int
     */
    public function getEditable() {
        return $this->editable;
    }

    /**
     * just returns integer, DO NOT USE FOR IS DELETABLE CHECKING
     * return value of deletable
     * @return int
     */
    public function getDeletable() {
        return $this->deletable;
    }

    /**
     * just returns integer, DO NOT USE FOR SHOW IN MENU CHECKING
     * return value of inMenu
     * @return int
     */
    public function getInMenu() {
        return $this->inMenu;
    }

    /**
     * just returns integer, DO NOT USE FOR LOCK URL PATH CHECKING
     * return value of lockUrlPath
     * @return int
     */
    public function getLockUrlPath() {
        return $this->lockUrlPath;
    }

    /**
     * just returns integer, DO NOT USE FOR LOCK PARENT CHECKING
     * return value of lockParent
     * @return int
     */
    public function getLockParent() {
        return $this->lockParent;
    }

    /**
     * just returns integer, DO NOT USE FOR HIDE IMAGE MANAGEMENT CHECKING
     * return value of hideImageManagement
     * @return int
     */
    public function getHideImageManagement() {
        return $this->hideImageManagement;
    }

    /**
     * just returns integer, DO NOT USE FOR HIDE FILE MANAGEMENT CHECKING
     * return value of hideFileManagement
     * @return int
     */
    public function getHideFileManagement() {
        return $this->hideFileManagement;
    }

    /**
     * just returns integer, DO NOT USE FOR HIDE LINK MANAGEMENT CHECKING
     * return value of hideLinkManagement
     * @return int
     */
    public function getHideLinkManagement() {
        return $this->hideLinkManagement;
    }

    /**
     * just returns integer, DO NOT USE FOR HIDE YOUTUBE LINK MANAGEMENT CHECKING
     * return value of hideYoutubeLinkManagement
     * @return int
     */
    public function getHideYoutubeLinkManagement() {
        return $this->hideYoutubeLinkManagement;
    }
    
    
    /**
     * just returns integer, DO NOT USE FOR Rss LINK MANAGEMENT CHECKING
     * return value of hideRssManagement
     * @return int
     */
    public function getHideRssManagement() {
        return $this->hideRssManagement;
    }

    /**
     * check to hide image management
     * @return boolean 
     */
    public function hideImageManagement() {
        return $this->hideImageManagement;
    }

    /**
     * check to hide file management
     * @return boolean 
     */
    public function hideFileManagement() {
        return $this->hideFileManagement;
    }

    /**
     * check to hide link management
     * @return boolean 
     */
    public function hideLinkManagement() {
        return $this->hideLinkManagement;
    }

    /**
     * check to hide youtube link management
     * @return boolean 
     */
    public function hideYoutubeLinkManagement() {
        return $this->hideYoutubeLinkManagement;
    }

    /**
     * check to lock parent
     * @return boolean
     */
    public function lockParent() {
        return $this->lockParent;
    }

    /**
     * get all images by specific list name for a page
     * @param string $sList
     * @return Image or array Images
     */
    public function getImages($sList = 'online') {
        if (!isset($this->aImages[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aImages[$sList] = PageManager::getImagesByFilter($this->pageId);
                    break;
                case 'first-online':
                    $aImages = PageManager::getImagesByFilter($this->pageId, array(), 1);
                    if (!empty($aImages))
                        $oImage = $aImages[0];
                    else
                        $oImage = null;
                    $this->aImages[$sList] = $oImage;
                    break;
                case 'all':
                    $this->aImages[$sList] = PageManager::getImagesByFilter($this->pageId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aImages[$sList];
    }

    /**
     * get all files by specific list name for a page
     * @param string $sList
     * @return array File
     */
    public function getFiles($sList = 'online') {
        if (!isset($this->aFiles[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aFiles[$sList] = PageManager::getFilesByFilter($this->pageId);
                    break;
                case 'all':
                    $this->aFiles[$sList] = PageManager::getFilesByFilter($this->pageId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aFiles[$sList];
    }

    /**
     * get all links by specific list name for a page
     * @param string $sList
     * @return array Link
     */
    public function getLinks($sList = 'online') {
        if (!isset($this->aLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aLinks[$sList] = PageManager::getLinksByFilter($this->pageId);
                    break;
                case 'all':
                    $this->aLinks[$sList] = PageManager::getLinksByFilter($this->pageId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aLinks[$sList];
    }

    /**
     * get all youtube links by specific list name for a page
     * @param string $sList
     * @return array YoutubeLink
     */
    public function getYoutubeLinks($sList = 'online') {
        if (!isset($this->aYoutubeLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aYoutubeLinks[$sList] = PageManager::getYoutubeLinksByFilter($this->pageId);
                    break;
                case 'all':
                    $this->aYoutubeLinks[$sList] = PageManager::getYoutubeLinksByFilter($this->pageId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aYoutubeLinks[$sList];
    }

    /**
     * return the window title if there is one, otherwise return title
     * @return string
     */
    public function getWindowTitle() {
        return $this->windowTitle ? $this->windowTitle : $this->title;
    }

    /**
     * return meta description if exists or a 
     * @return string
     */
    public function getMetaDescription() {
        $sDesc = $this->metaDescription ? $this->metaDescription : generateMetaDescription($this->content);        
        $sDesc = empty($sDesc) ? CLIENT_NAME .' - '.$this->title : $sDesc;
        
        return $sDesc;
    }

    /**
     * return meta keywords
     * @return string
     */
    public function getMetaKeywords() {
        return $this->metaKeywords;
    }

    /**
     * return short title but fall back on title if short does not exists
     * @return string
     */
    public function getShortTitle() {
        return $this->shortTitle ? $this->shortTitle : $this->title;
    }

    /**
     * generates an array of the breadcrumbs, but in reverse order
     * @param array $aCrumbles
     */
    private function generateCrumbles(&$aCrumbles = array()) {
        $aCrumbles[$this->getShortTitle()] = $this->getUrlPath();

        $oParentPage = $this->getParent();
        if (!empty($oParentPage)) {
            $oParentPage->generateCrumbles($aCrumbles);
        }
    }

    /**
     * get an array of the breadcrumbs in the right order
     * @return array
     */
    public function getCrumbles() {
        $this->generateCrumbles($aCrumbles);
        return array_reverse($aCrumbles);
    }

    /**
     * copy specific predefined properties from parent
     * @param Page $oPage
     */
    public function copyFromParent(Page $oPage) {
        $this->parentPageId = $oPage->pageId;
        $this->online = $oPage->online;
        $this->onlineChangeable = $oPage->onlineChangeable;
        $this->editable = $oPage->editable;
        $this->deletable = $oPage->deletable;
        $this->inMenu = $oPage->inMenu;
        $this->mayHaveSub = $oPage->mayHaveSub;
        $this->lockUrlPath = $oPage->lockUrlPath;
        $this->controllerPath = $oPage->controllerPath;
        $this->lockParent = $oPage->lockParent;
        $this->hideImageManagement = $oPage->hideImageManagement;
        $this->hideFileManagement = $oPage->hideFileManagement;
        $this->hideLinkManagement = $oPage->hideLinkManagement;
        $this->hideYoutubeLinkManagement = $oPage->hideYoutubeLinkManagement;
    }

    
    /**
     * Get the rss feeds associated with this page
     * 
     * @return array RssFeed
     */
    public function getRssFeeds() {
        return empty($this->pageId) ? array() : RssFeedManager::getRssFeedsByFilter(array('pageId' => $this->pageId));
    }
    
    /**
     * Set the rss feeds for this page
     * 
     * @param array $aRssFeeds
     */
    public function setRssFeeds($aRssFeeds) {
        RssFeedManager::deletePageRssFeedRelations($this->pageId);
        
        foreach ($aRssFeeds AS $oRssFeed) {            
            RssFeedManager::savePageRssFeedRelation($this->pageId, $oRssFeed->rssFeedId, $oRssFeed->filter, $oRssFeed->logicOperator, $oRssFeed->negative);
        }
    }
    
    /**
     * Remove relations between rss feeds and this page
     */
    public function unsetRssFeeds() {
        RssFeedManager::deletePageRssFeedRelations($this->pageId);
    }
    
    /**
     * 
     * @param int $iRssFeedId
     * @return boolean
     */
    public function hasRssFeed($iRssFeedId) {
        return empty($this->pageId) ? false : RssFeedManager::existsPageRssFeedRelation($this->pageId, $iRssFeedId);
    }   
}

?>
