<?php
require_once 'Model.class.php';

class RssFeedItem extends Model {
    public $rssFeedItemId;
    public $rssFeedId;
    public $online = 1;
    public $title;
    public $link;
    public $description;      
    # optional field according to RSS 2.0 standard
    public $author;
    public $category;
    public $enclosureUrl;
    public $enclosureLength;
    public $enclosureType;
    public $commentsUrl;
    public $guidIsPermalink;
    public $guidUrl;
    public $sourceUrl;
    public $sourceTitle;
    public $pubDate;
    public $created;
    public $modified;
    
    public function validate() {
        if (empty($this->title)) {
            $this->setPropInvalid('title');
        }
        
        if (empty($this->link)) {
            $this->setPropInvalid('link');
        }    
        
        if (!is_numeric($this->rssFeedId)) {
            $this->setPropInvalid('rssFeedId');
        } 
    }
    
    /**
     * check if page is online changeable
     * @return boolean
     */
    public function isOnlineChangeable() {
        return $this->onlineChangeable;
    }
    
    /**
     * Return the link to the page where is the item in Building the Bridge
     * @return string
     */
    public function getPageLink($oPage = null) {                
        //$oPage = PageManager::getPageWithRssFeed($this->rssFeedId);
        
        if (empty($oPage)) {
            return $this->link;
        }
        
        return CLIENT_HTTPS_URL.$oPage->generateUrlPath().'?rssitem='.$this->rssFeedItemId.'#rssitem-'.$this->rssFeedItemId;
    }
}
?>