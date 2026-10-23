<?php

/* Models and managers used by the NewsItem model */
require_once 'Model.class.php';
require_once 'NewsItemManager.class.php';
require_once 'ImageManager.class.php';

class NewsItem extends Model {

    const FILES_PATH = '/files/news';
    const IMAGES_PATH = '/images/news';

    public $newsItemId;
    public $windowTitle; //browser window title
    public $metaKeywords;
    public $metaDescription;
    public $title;
    public $intro;
    public $content;
    public $shortTitle; //for special use in f.e. menu's
    public $date; //date the news appeared
    public $source;
    public $onlineFrom; //date time from when the news item is showed online
    public $onlineTo; //date time to when the news item is showed online
    public $online = 1;
    public $created;
    public $modified;
    private $aImages = null; //array with different lists of images
    private $aFiles = null; //array with different lists of files
    private $aLinks = null; //array with different lists of links
    private $aYoutubeLinks = null; //array with different lists of youtube links
    private $aCategories = null; //array with different lists of newsItem categories

    /**
     * validate object
     */

    public function validate() {
        if (empty($this->title))
            $this->setPropInvalid('title');
        if (empty($this->date))
            $this->setPropInvalid('date');
        if (empty($this->onlineFrom))
            $this->setPropInvalid('onlineFrom');
        if (!is_numeric($this->online))
            $this->setPropInvalid('online');

        // check or at least 1 category is set
        if (class_exists('NewsItemCategoryManager')) {
            if (count($this->getCategories('all')) == 0)
                $this->setPropInvalid('categories');
        }
    }

    /**
     * check if news item is editable
     * @return Boolean
     */
    public function isEditable() {
        return true;
    }

    /**
     * check if news item is deletable
     * @return Boolean
     */
    public function isDeletable() {
        return true;
    }

    public function isOnline() {

        $oDateFrom = new Date($this->onlineFrom);
        $oDateTo = new Date($this->onlineTo);
        $oDateNow = new Date('NOW');

        $bOnline = true;
        if (!($this->online && (($oDateFrom->lowerEqualTo($oDateNow) && $this->onlineFrom === null) || ($oDateFrom->lowerEqualTo($oDateNow) && $oDateNow->lowerEqualTo($oDateTo))))) {
            $bOnline = false;
        }

        // with categories so check if at least 1 category is online
        if (class_exists('NewsItemCategoryManager') && count($this->getCategories()) <= 0) {
            $bOnline = false;
        }

        return $bOnline;
    }

    /**
     * get url to news item optional with extension .html
     * @param boolean $bWithExtension (optional) DEFAULT FALSE
     * @return string
     */
    public function getUrlPath($bWithExtension = false) {
        $sUrlPath = '/nieuws/' . $this->newsItemId . '/' . prettyUrlPart($this->title);

        if ($bWithExtension)
            return $sUrlPath . '.html';
        return $sUrlPath;
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
        return $this->metaDescription ? $this->metaDescription : generateMetaDescription($this->content);
        ;
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
     * 
     * get an array of the breadcrumbs
     * @param type $iNewsItemCategoryId
     * @return array
     */
    public function getCrumbles($iNewsItemCategoryId = null) {

        $aCrumbles = array();
        $aCrumbles['Nieuws'] = '/nieuws';
        if ($iNewsItemCategoryId && ($oNewsItemCategory = NewsItemCategoryManager::getNewsItemCategoryById($iNewsItemCategoryId))) {
            $aCrumbles[$oNewsItemCategory->name] = $oNewsItemCategory->getUrlPath();
        }
        $aCrumbles[$this->getShortTitle()] = $this->getUrlPath();
        return $aCrumbles;
    }

    /**
     * get all images by specific list name for a newsItem
     * @param string $sList
     * @return Image or array Images
     */
    public function getImages($sList = 'online') {
        if (!isset($this->aImages[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aImages[$sList] = NewsItemManager::getImagesByFilter($this->newsItemId);
                    break;
                case 'first-online':
                    $aImages = NewsItemManager::getImagesByFilter($this->newsItemId, array(), 1);
                    if (!empty($aImages))
                        $oImage = $aImages[0];
                    else
                        $oImage = null;
                    $this->aImages[$sList] = $oImage;
                    break;
                case 'all':
                    $this->aImages[$sList] = NewsItemManager::getImagesByFilter($this->newsItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aImages[$sList];
    }

    /**
     * get all files by specific list name for a newsItem
     * @param string $sList
     * @return array File
     */
    public function getFiles($sList = 'online') {
        if (!isset($this->aFiles[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aFiles[$sList] = NewsItemManager::getFilesByFilter($this->newsItemId);
                    break;
                case 'all':
                    $this->aFiles[$sList] = NewsItemManager::getFilesByFilter($this->newsItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aFiles[$sList];
    }

    /**
     * get all links by specific list name for a newsItem
     * @param string $sList
     * @return array Link
     */
    public function getLinks($sList = 'online') {
        if (!isset($this->aLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aLinks[$sList] = NewsItemManager::getLinksByFilter($this->newsItemId);
                    break;
                case 'all':
                    $this->aLinks[$sList] = NewsItemManager::getLinksByFilter($this->newsItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aLinks[$sList];
    }

    /**
     * get all youtube links by specific list name for a newsItem
     * @param string $sList
     * @return array YoutubeLink
     */
    public function getYoutubeLinks($sList = 'online') {
        if (!isset($this->aYoutubeLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aYoutubeLinks[$sList] = NewsItemManager::getYoutubeLinksByFilter($this->newsItemId);
                    break;
                case 'all':
                    $this->aYoutubeLinks[$sList] = NewsItemManager::getYoutubeLinksByFilter($this->newsItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aYoutubeLinks[$sList];
    }

    /**
     * get all news item categories by specific list name for a newsItem
     * @param string $sList
     * @return array NewsItemCategory
     */
    public function getCategories($sList = 'online') {
        if (!isset($this->aCategories[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aCategories[$sList] = NewsItemManager::getCategoriesByFilter($this->newsItemId);
                    break;
                case 'all':
                    $this->aCategories[$sList] = NewsItemManager::getCategoriesByFilter($this->newsItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aCategories[$sList];
    }

    /**
     * set categories
     * @param array $aNewsItemCategories
     * @param string $sList (set in specific list)
     */
    public function setCategories(array $aNewsItemCategories, $sList = 'online') {
        $this->aCategories[$sList] = $aNewsItemCategories;
    }

}

?>
