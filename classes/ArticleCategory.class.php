<?php

/* Models and managers used by the ArticleItem model */
require_once 'Model.class.php';
require_once 'ArticleCategoryManager.class.php';

class ArticleCategory extends Model {

    const WORLD_LEADER_LETTERS_CATEGORY = -1;
    public $articleCategoryId;
    public $parentCategoryId;
    public $parentPageId = 0;
    public $windowTitle; //browser window title
    public $metaKeywords;
    public $metaDescription;
    public $name;
    public $order = 9999;
    public $online = 1;
    private $urlPart; // part of the url fe /articles/urlpart
    private $urlPartText; // text to use for url part
    public $created;
    public $modified;

    /**
     * validate object
     */

    public function validate() {
        if (empty($this->name))
            $this->setPropInvalid('name');
        
        if (!is_numeric($this->parentPageId))
            $this->setPropInvalid('parentPageId');
    }

    /**
     * get url to category optional with extension .html
     * @param boolean $bWithExtension (optional) DEFAULT FALSE
     * @return string
     */
    public function getUrlPath($bWithExtension = false) {
        $sUrlPath = $this->getPage()->getUrlPath() .'/' . $this->getUrlPart();

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
     *
     * @return Article or array Article
     */
    public function getArticles() {
        return ArticleCategoryManager::getArticlesByFilter($this->articleCategoryId);
    }

    /**
     * check if object is deletable
     * @return Boolean
     */
    public function isDeletable() {
        if (intval($this->articleCategoryId) == self::WORLD_LEADER_LETTERS_CATEGORY) {
            return false;
        }
        
        return count($this->getArticles()) == 0 && count($this->getSubcategories()) == 0;
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
        //return $this->urlPartText ? $this->urlPartText : prettyUrlPart($this->name);
        return $this->urlPartText ? $this->urlPartText : $this->__genUrlPart();
    }
    
    private function __genUrlPart() {
        $aUrlParts = array();
        $oCategory = $this;
        
        while ($oCategory  != null) {
            $aUrlParts[] = prettyUrlPart($oCategory->name);
            $oCategory = $oCategory->getParent();
        }        
        
        $aUrlParts = array_reverse($aUrlParts);
        return implode('/', $aUrlParts);
    }
    
    /**
     * Get the parent category
     * 
     * @return ArticleCategory
     */
    public function getParent() {
        return empty($this->parentCategoryId) ? null : ArticleCategoryManager::getArticleCategoryById($this->parentCategoryId);
    }
    
    /**
     * Get the subcategories of this category
     * 
     * @return array ArticleCategory
     */
    public function getSubcategories() {
        return ArticleCategoryManager::getArticleCategorySubcategories($this->articleCategoryId);
    }
    
    /**
     * 
     * @return string
     */
    public function getFullName() {
        $aNameParts = array();
        $oCategory = $this;
        
        while ($oCategory  != null) {
            $aNameParts[] = $oCategory->name;
            $oCategory = $oCategory->getParent();
        }        
        
        $aNameParts = array_reverse($aNameParts);
        return implode(' > ', $aNameParts); 
    }
    
    /**
     * 
     * @return Page
     */
    public function getPage() {
        return PageManager::getPageById($this->parentPageId);
    }
}
?>