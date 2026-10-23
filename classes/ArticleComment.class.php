<?php
include_once 'Model.class.php';

class ArticleComment extends Model {
    const COMMENT_APPROPRIATE = 0;
    const COMMENT_INAPPROPRIATE  = 1;

    public $articleCommentId;
    public $authorId;
    public $articleId;
    public $authorName;
    public $authorEmail;
    public $comment;
    public $confirmCode;
    public $postedOn;
    public $inappropriate = 0;
    public $online = 1;
    
    public function validate() {
        if (empty($this->authorName)) {
            $this->setPropInvalid('authorName');
        }
        
        if (empty($this->authorEmail)) {
            $this->setPropInvalid('authorEmail');
        }        
        
        if (!is_numeric($this->articleId)) {
            $this->setPropInvalid('articleId');
        }
        
        if (empty($this->comment)) {
            $this->setPropInvalid('comment');
        }
        
        if (!is_numeric($this->online)) {
            $this->setPropInvalid('online');
        }
    }
    
    /**
     * Get the author of the comment
     * 
     * @return Author
     */
    public function getAuthor() {
        return empty($this->authorId) ? null : CustomerManager::getCustomerById($this->authorId) ;
    }
    
    /**
     * 
     * @return Article
     */
    public function getArticle() {
        return empty($this->articleId) ? null : ArticleManager::getArticleById($this->articleId);
    }
}
?>