<?php

/* Models and managers used by the NewsItemItem model */
require_once 'Model.class.php';
require_once 'NewsItemCategoryManager.class.php';

class NewsItemCategory extends Model {

    public $newsItemCategoryId;
    public $windowTitle; //browser window title
    public $metaKeywords;
    public $metaDescription;
    public $name;
    private $urlPart; // part of the url fe /nieuws/urlpart
    private $urlPartText; // text to use for url part
    public $online = 1;
    public $order = 99999;
    public $created;
    public $modified;
    private $aNewsItems = array(); //array with different lists of newsitems

    /**
     * validate object
     */

    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
        if (!is_numeric($this->online))
            $this->setPropInvalid('online');
    }

    /**
     * get url to category optional with extension .html
     * @param boolean $bWithExtension (optional) DEFAULT FALSE
     * @return string
     */
    public function getUrlPath($bWithExtension = false) {
        $sUrlPath = '/nieuws/' . $this->getUrlPart();

        if ($bWithExtension) {
            return $sUrlPath . '.html';
        }
        return $sUrlPath;
    }

    /**
     * return the window title if there is one, otherwise return name
     * @return string
     */
    public function getWindowTitle() {
        return $this->windowTitle ? $this->windowTitle : $this->name;
    }

    /**
     * return meta description if exists or a
     * @return string
     */
    public function getMetaDescription() {
        return $this->metaDescription;
    }

    /**
     * return meta keywords
     * @return string
     */
    public function getMetaKeywords() {
        return $this->metaKeywords;
    }

    /**
     * get an array of the breadcrumbs
     * @return array
     */
    public function getCrumbles() {
        return array('Nieuws' => '/nieuws', $this->name => $this->getUrlPath());
    }

    /**
     * get all news items by specific list name for a category
     * @param string $sList
     * @return NewsItem or array NewsItem
     */
    public function getNewsItems($sList = 'online') {
        if (!isset($this->aNewsItems[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aNewsItems[$sList] = NewsItemCategoryManager::getNewsItemsByFilter($this->newsItemCategoryId);
                    break;
                case 'all':
                    $this->aNewsItems[$sList] = NewsItemCategoryManager::getNewsItemsByFilter($this->newsItemCategoryId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aNewsItems[$sList];
    }

    /**
     * check if object is deletable
     * @return Boolean
     */
    public function isDeletable() {
        return count($this->getNewsItems('all')) == 0;
    }

    /**
     * set url part text
     * @param string $sUrlPartText
     */
    public function setUrlPartText($sUrlPartText) {
        $this->urlPartText = prettyUrlPart($sUrlPartText);
    }

    /**
     * return url part text
     * @return string
     */
    public function getUrlPartText() {
        return $this->urlPartText;
    }

    /**
     * set url part
     * @param string $sUrlPart
     */
    public function setUrlPart($sUrlPart) {
        $this->urlPart = prettyUrlPart($sUrlPart);
    }

    /**
     * return urlPart
     * @return string
     */
    public function getUrlPart() {
        return $this->urlPart;
    }

    /**
     * return generated urlPart
     * @return string
     */
    public function generateUrlPart() {
        return $this->urlPartText ? $this->urlPartText : prettyUrlPart($this->name);
    }

    public function isOnline() {
        return $this->online && count($this->getNewsItems()) > 0;
    }

}

?>
