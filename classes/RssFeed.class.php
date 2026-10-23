<?php
require_once 'Model.class.php';

class RssFeed extends Model {
    const DEFAULT_IMAGE_WIDTH = 88;
    public $rssFeedId;
    public $online = 1;
    public $title;
    public $filter;
    public $logicOperator = "AND";
    public $negative = 0;
    public $link;
    public $description;
    # optional fields according to RSS 2.0 standard
    public $language;
    public $copyright;
    public $managingEditor;
    public $webMaster;
    public $pubDate;
    public $lastBuildDate;
    public $category;
    public $generator;
    public $docsUrl;
    public $cloud;   # not currently used
    public $ttl;
    public $imageUrl;
    public $imageTitle;
    public $imageLink;
    public $imageHeight;
    public $imageWidth = self::DEFAULT_IMAGE_WIDTH;
    public $textInput;  # not currently used
    public $skipHours;
    public $skipDays;
    public $updateInterval;
    public $lastUpdate;
    public $created;
    public $modified;   
    
    public function validate() {
        if (empty($this->title)) {
            $this->setPropInvalid('title');
        }
        
        if (empty($this->link)) {
            $this->setPropInvalid('link');
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
     * Get the list of the Rss items for this feed
     * 
     * @param int $iLimit
     * @param int $iStart
     * @param int $iFoundRows
     * @param array $aOrderBy
     * @return array RssFeedItem
     */
    public function getRssFeedItems($aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`pubDate`' => 'DESC')) {
        $aFilter['rssFeedId'] = $this->rssFeedId;
        return RssFeedItemManager::getRssFeedItemsByFilter($aFilter, $iLimit, $iStart, $iFoundRows, $aOrderBy);
    }
    
    /**
     * Read the rss feed again
     * 
     * @return boolean
     */
    public function load() {
        return @RssFeedManager::loadRssFeed($this);
    }    
}
?>