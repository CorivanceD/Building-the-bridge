<?php
class WorldLeaderLetter extends Model {
    public $worldLeaderLetterId;
    public $articleId;
    public $worldLeaderId;
    public $worldLeaderAddressId;
    public $created;
    public $modified;
    private $oArticle;
    private $oWorldLeader;
    private $oWorldLeaderAddress;
    
    public function validate() {
        if (!is_numeric($this->articleId)) {
            $this->setPropInvalid('articleId');
        }
        
        if (!is_numeric($this->worldLeaderId)) {
            $this->setPropInvalid('worldLeaderId');
        }        
    }
    
    /**
     * Get the letter itself
     * 
     * @param boolean $bForceUpdate
     * @return Article
     */
    public function getArticle($bForceUpdate = true) {
        if (!empty($this->articleId) && (empty($this->oArticle) || $bForceUpdate)) {
            $this->oArticle = ArticleManager::getArticleById($this->articleId);
        }
        
        return $this->oArticle;
    }
    
    /**
     * Get the addressee of the letter
     * @param boolean $bForceUpdate
     * @return WorldLeader
     */    
    public function getWorldLeader($bForceUpdate = true) {
        if (!empty($this->worldLeaderId) && (empty($this->oWorldLeader) || $bForceUpdate)) {
            $this->oWorldLeader = WorldLeaderManager::getWorldLeaderById($this->worldLeaderId);
        }
        
        return $this->oWorldLeader;
    }
    
    
    /**
     * Get the address of the letter
     * @param boolean $bForceUpdate
     * @return WorldLeaderAddress
     */     
    public function getWorldLeaderAddress($bForceUpdate = true) {
        if (!empty($this->worldLeaderAddressId) && (empty($this->oWorldLeaderAddress) || $bForceUpdate)) {
            $this->oWorldLeaderAddress = WorldLeaderAddressManager::getWorldLeaderAddressById($this->worldLeaderAddressId);
        }
        
        return $this->oWorldLeaderAddress;
    }    
}
?>