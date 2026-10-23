<?php

/* Models and managers used by the Article model */
require_once 'Model.class.php';
require_once 'ArticleManager.class.php';
require_once 'ImageManager.class.php';

class Article extends Model {

    const ARTICLE_STATUS_PENDING = 1;
    const ARTICLE_STATUS_REVIEW = 2;
    const ARTICLE_STATUS_PUBLISHED = 3;
    const ARTICLE_STATUS_REJECTED = 4;
    const ARTICLE_STATUS_OFFLINE = 5;
    const ARTICLE_USED_BEFORE = 10;
    const ARTICLE_NOT_USED_BEFORE = 20;
    const FILES_PATH = '/files/articles';
    const IMAGES_PATH = '/images/articles';

    public $articleId;
    public $alias;
    public $authorEmail;
    public $parentPageId;

    /** communication related attributes */
    public $communicationItemId;
    public $included;
    public $alreadySent;

    /** /communication related attributes */
    public $articleCategoryId;
    public $windowTitle; //browser window title
    public $metaKeywords;
    public $metaDescription;
    public $title;
    public $intro;
    public $content;
    public $shortTitle; //for special use in f.e. menu's
    public $pubDate; //date the news appeared
    public $authorId;
    public $status = self::ARTICLE_STATUS_PENDING;
    public $isLuminary = 0;
    public $order = 99999;
    public $created;
    public $modified;
    private $aImages = null; //array with different lists of images
    private $aFiles = null; //array with different lists of files
    private $aLinks = null; //array with different lists of links
    private $aYoutubeLinks = null; //array with different lists of youtube links
    private $oWorldLeaderLetter = null; // not null if this article is a letter to a world leader

    /**
     * validate object
     */

    public function validate() {
        if (empty($this->articleCategoryId))
            $this->setPropInvalid('articleCategoryId');

        if (empty($this->parentPageId))
            $this->setPropInvalid('parentPageId');

        if (empty($this->title))
            $this->setPropInvalid('title');

        if (empty($this->authorId))
            $this->setPropInvalid('authorId');
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

    /**
     * get url to news item optional with extension .html
     * @param boolean $bWithExtension (optional) DEFAULT FALSE
     * @return string
     */
    public function getUrlPath($bWithExtension = false) {
        $sUrlPath = $this->getCategory()->getUrlPath() . '/' . prettyUrlPart($this->title) . '/' . $this->articleId;

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
     * get all images by specific list name for a article
     * @param string $sList
     * @return Image or array Images
     */
    public function getImages($sList = 'online') {
        if (!isset($this->aImages[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aImages[$sList] = ArticleManager::getImagesByFilter($this->articleId);
                    break;
                case 'first-online':
                    $aImages = ArticleManager::getImagesByFilter($this->articleId, array(), 1);
                    if (!empty($aImages))
                        $oImage = $aImages[0];
                    else
                        $oImage = null;
                    $this->aImages[$sList] = $oImage;
                    break;
                case 'all':
                    $this->aImages[$sList] = ArticleManager::getImagesByFilter($this->articleId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aImages[$sList];
    }

    /**
     * get all files by specific list name for a article
     * @param string $sList
     * @return array File
     */
    public function getFiles($sList = 'online') {
        if (!isset($this->aFiles[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aFiles[$sList] = ArticleManager::getFilesByFilter($this->articleId);
                    break;
                case 'all':
                    $this->aFiles[$sList] = ArticleManager::getFilesByFilter($this->articleId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aFiles[$sList];
    }

    /**
     * get all links by specific list name for a article
     * @param string $sList
     * @return array Link
     */
    public function getLinks($sList = 'online') {
        if (!isset($this->aLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aLinks[$sList] = ArticleManager::getLinksByFilter($this->articleId);
                    break;
                case 'all':
                    $this->aLinks[$sList] = ArticleManager::getLinksByFilter($this->articleId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aLinks[$sList];
    }

    /**
     * get all youtube links by specific list name for a article
     * @param string $sList
     * @return array YoutubeLink
     */
    public function getYoutubeLinks($sList = 'online') {
        if (!isset($this->aYoutubeLinks[$sList])) {
            switch ($sList) {
                case 'online':
                    $this->aYoutubeLinks[$sList] = ArticleManager::getYoutubeLinksByFilter($this->articleId);
                    break;
                case 'all':
                    $this->aYoutubeLinks[$sList] = ArticleManager::getYoutubeLinksByFilter($this->articleId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aYoutubeLinks[$sList];
    }

    /**
     * 
     * @return ArticleCategory
     */
    public function getCategory() {
        return empty($this->articleCategoryId) ? null : ArticleCategoryManager::getArticleCategoryById($this->articleCategoryId);
    }

    /**
     * 
     * @return User
     */
    public function getAuthor() {
        if ($this->authorId == User::ANONYMOUS_USER_ID) {
            $oAuthor = UserManager::getUserById($this->authorId);
            $oAuthor->name = $this->alias;

            if (!empty($this->authorEmail)) {
                $oAuthor->email = $this->authorEmail;
            }

            return $oAuthor;
        }
        return empty($this->authorId) ? null : UserManager::getUserById($this->authorId);
    }

    /**
     * 
     * @return array ArticleComment
     */
    public function getComments($bOffline = false) {
        $aFilter = array('articleId' => $this->articleId);

        if ($bOffline) {
            $aFilter['showAll'] = 1;
        }

        return ArticleManager::getArticleCommentsByFilter($aFilter);
    }

    /**
     * Was the article already sent in a newsletter;
     * 
     * @return boolean
     */
    public function wasAlreadySent() {
        return ArticleManager::wasArticleAlreadySent($this->articleId);
    }

    /**
     * The article is included in the newsletter
     * 
     * @param int $iCommunicationItemId
     * @return boolean
     */
    public function isIncludedInNewsletter($iCommunicationItemId) {
        return ArticleManager::isIncludedInNewsLetter($this->articleId, $iCommunicationItemId);
    }

    /**
     * Get the associated WorldLeaderLetter object
     * @param boolean $bForceUpdate
     * @return WorldLeaderLetter
     */
    public function getWorldLeaderLetter($bForceUpdate = true) {
        if (is_numeric($this->articleId) && (empty($this->oWorldLeaderLetter) || $bForceUpdate)) {
            $this->oWorldLeaderLetter = WorldLeaderLetterManager::getWorldLeaderLetterByArticleId($this->articleId);
        }
        
        return $this->oWorldLeaderLetter;
    }
}

?>