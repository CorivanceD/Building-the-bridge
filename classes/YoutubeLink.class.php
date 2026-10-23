<?php

/* Models and managers used by the Page model */
require_once 'Media.class.php';

class YoutubeLink extends Media {

    public $type = Media::YOUTUBE;
    public $online = 1;

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->title))
            $this->setPropInvalid('title');
        parent::validate(); //validate parent (media values)
    }

    /**
     * return youtube unique video id
     * @return string
     */
    public function getYoutubeLinkUniqueId() {
        // get the unqiue video ID -> v
        if (preg_match("#youtube\..*/watch\?.*v=([^\&]+)#i", $this->link, $aMatches)) {
            return $aMatches[1];
        } elseif (preg_match("#youtu.be/(.*)#i", $this->link, $aMatches)) {
            return $aMatches[1];
        } else {
            return false;
        }
    }

    /**
     * return youtube embed link
     * @return string
     */
    public function getEmbedLink() {
        return 'https://www.youtube.com/embed/' . $this->getYoutubeLinkUniqueId() . '?autoplay=1';
    }

    /**
     * return thumb link
     * Youtube provides 4 different thumbs
     * 1 is a big thumb with number 0 (size: 480x360)
     * 3 are small thumbs from the movie with numbers 1-3 (size: 130x97)
     * @param int $iThumb
     * @return String
     */
    public function getThumbLink($iThumb) {
        return 'https://img.youtube.com/vi/' . $this->getYoutubeLinkUniqueId() . '/' . $iThumb . '.jpg';
    }

    /**
     * Get the video info from Youtube.
     * @return array youtube video info
     */
    public function getYoutubeVideoInfo() {
        parse_str(file_get_contents("https://youtube.com/get_video_info?video_id=" . $this->getYoutubeLinkUniqueId()), $aVideoInfo);
        return $aVideoInfo;
    }

    /**
     * check if file online/offline may be changed
     * @return bool 
     */
    public function isOnlineChangeable() {
        return true;
    }

    /**
     * check if file is editable
     * @return bool 
     */
    public function isEditable() {
        return true;
    }

    /**
     * check if file is deletable
     * @return bool 
     */
    public function isDeletable() {
        return true;
    }

}

?>
