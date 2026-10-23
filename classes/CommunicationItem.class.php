<?php

// Models and managers used by this class
require_once 'Model.class.php';
require_once 'CommunicationItemManager.class.php';

class CommunicationItem extends Model {
    
    const IMAGES_PATH = '/images/communication';
    const CSS_PATH = '/files/communication/css/CI_style.css';
    const COMMUNICATION_LOG_PATH = '/files/communication/communicationlog.php';
    
    const COMMUNICATION_TYPE_EMAIL = 'email';
    const COMMUNICATION_TYPE_SMS = 'sms';
    const COMMUNICATION_TEMPLATEGROUPID_SMS = 7;
    const COMMUNICATION_TEMPLATEGROUPID_EMAIL = 8;
    const MS_SLEEP_SEND_EMAIL = 500000;
    const MAX_LENGTH_SMS_SENDERNAME = 11;
    const MAX_LENGTH_SINGLE_SMS = 160;
    const SPF_RULE = 'include:spf.a-side.email';
    const MESSAGE_UNSUBSCRIBE_EMAIL = "Click here of you don't want to receive our messages";
    
    public $communicationItemId = null;
    public $type = null;
    public $templateId = null;
    public $title = null;
    public $subject = null;
    public $messageParts = 1;
    public $pendingInBulkCron = 0;
    public $hasBulkSendingError = 0;
    public $senderName = COMMUNICATION_CLIENT_FROM_NAME;
    public $senderEmail = COMMUNICATION_CLIENT_EMAIL_FROM;
    public $message = null;
    public $messageWithRedirectUrls = null;
    public $sending = 0;
    public $sended = 0;
    public $sendDate = null;
    public $created = null;
    public $modified = null;
    
    public $sCss = null;
    public $oTemplate = null;
    
    private $aCustomerGroups = null;
    private $aSendCustomers = null;
    private $aConversionItems = null;
    private $aBlacklistedCustomers = null;
    private $aBouncedCustomers = null;
    private $aRedirectLinks = null;
    

    /**
     * validate object
     */
    public function validate() {
        if (empty($this->type))
            $this->setPropInvalid('type');
        if (empty($this->title))
            $this->setPropInvalid('title');
        if (empty($this->subject))
            $this->setPropInvalid('subject');
        if (empty($this->senderName))
            $this->setPropInvalid('senderName');
        if ($this->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
            if (empty($this->senderEmail) || !filter_var($this->senderEmail, FILTER_VALIDATE_EMAIL)) {
                $this->setPropInvalid('senderEmail');
            }
        }
    }
    
    /**
     * sets CSS content
     */
    public function setCssHeader() {
        $this->sCss = '<style type="text/css">';
        $this->sCss .= file_get_contents(CLIENT_HTTPS_URL . self::CSS_PATH);
        $this->sCss .= '</style>';
    }
    
    /**
     * get CSS content
     * @return string css
     */
    public function getCssHeader() {
        if($this->sCss === null) {
            $this->setCssHeader();
        }
        return $this->sCss;
    }
    
    /**
     * set message length
     */
    public function setSmsMessagePartsAmount() {
        
        # calculate length of message
        $iCharCountMessage = mb_strlen($this->message, 'UTF-8');
        
        # check whether special chars are found (encoding these requires two chars)
        $aSpecialChars = array("^", "{", "}", "\\", "[", "~", "]", "|", "€");
        $iExtraCharsTotal = 0;
        foreach($aSpecialChars as $sSpecialChar) {
            $iExtraChars = substr_count($this->message, $sSpecialChar);
            $iExtraCharsTotal += $iExtraChars;
        }
        $iCharCount = $iCharCountMessage + $iExtraCharsTotal;

        # needs to split over more than one message?
        $iAmountOfSmsItems = ceil($iCharCount / self::MAX_LENGTH_SINGLE_SMS);  
        $this->messageParts = $iAmountOfSmsItems;
        
    }
    
    /**
     * set sms sendername
     */
    public function setSmsSenderName() {
        $iExtraChars = 0;
        $aSpecialChars = array("@", "-", "+", "_", ".");
        foreach($aSpecialChars as $sSpecialChar) {
            $iCharCount = substr_count($this->senderName, $sSpecialChar);
            $iExtraChars += $iCharCount;
        }
        # substract special chars which count as two chars
        $iMaxCharsSender = self::MAX_LENGTH_SMS_SENDERNAME - $iExtraChars;
        $this->senderName = substr($this->senderName, 0, $iMaxCharsSender);
    }
    
    /**
     * get message with optional email signoff option
     * param bool $bHashed
     */
    public function getMessage($bReturnHashedVersion = false, $bAddEmailSignOff = false, $oCustomer = null) {

        $sUnsubscribeMessage = '';

        if($bAddEmailSignOff && $this->type === self::COMMUNICATION_TYPE_EMAIL) {
            if(!empty($oCustomer)) {

                # create token
                $sToken = hashPasswordForDb($oCustomer->customerId.'hash'.$oCustomer->email);

                # add unsubscribe link to message
                $sUnsubscribeMessage = '
                    <p style="text-align: center"><a href="' . CLIENT_HTTP_URL . '/afmelden?customerid=' . $oCustomer->customerId . '&communicationItemId=' . $this->communicationItemId . '&token=' . $sToken . '" style="color: #14204a;">' . CommunicationItem::MESSAGE_UNSUBSCRIBE_EMAIL . '.</a></p>
                ';
                
            } else {
                die('Customer not found');
            }
        }
        
        if($bReturnHashedVersion) {
            return $this->messageWithRedirectUrls . ($bAddEmailSignOff ? $sUnsubscribeMessage : '');
        } else {
            return $this->message . ($bAddEmailSignOff ? $sUnsubscribeMessage : '');
        }
    }
    
    /**
     * adds conversion pixel to track if mail is opened
     */
    public function addConversionPixel($oCommunicationItem, $oCustomer) {
        if($this->type === self::COMMUNICATION_TYPE_EMAIL) {
            # check if required item are valid
            if(!empty($oCommunicationItem) && !empty($oCustomer)) {
                # add unsubscribe link to message
                $sConversionPixel = '<p style="text-align: center"><img src="' . CLIENT_HTTP_URL . self::COMMUNICATION_LOG_PATH . '?communicationItemId=' . $oCommunicationItem->communicationItemId . '&customerId=' . $oCustomer->customerId . '" height="5" width="5" border="0" alt="" /></p>';
            }
        }
        return $sConversionPixel;
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
     * set Template object
     */
    public function setTemplate() {
        if(is_null($this->oTemplate)) {
            $this->oTemplate = TemplateManager::getTemplateById($this->templateId);
        }
    }
    
    /**
     * get related Template object
     */
    public function getTemplate() {
        if(is_null($this->oTemplate)) {
            $this->setTemplate();
        }
        return $this->oTemplate;
    }
    
    /**
     * @param $aCustomerGroups
     * set related costumerGroups
     */
    public function setCustomerGroups(array $aCustomerGroups) {
        $this->aCustomerGroups = $aCustomerGroups;
    }
    
    /**
     * get all related costumers
     */
    public function getCustomerGroups() {
        if(is_null($this->aCustomerGroups)) {
            $this->aCustomerGroups = CustomerGroupManager::getCustomerGroupsByCommunicationItemId($this->communicationItemId);
        }
        return $this->aCustomerGroups;
    }
    
    /**
     * check if groups are connected
     * @return boolean
     */
    public function hasCustomerGroupsConnected() {
        self::getCustomerGroups();
        if(count($this->aCustomerGroups) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * get all customers
     */
    public function getSendCustomers() {
        if(is_null($this->aSendCustomers)) {
            $this->aSendCustomers = CommunicationItemManager::getSendCustomersForCommunicationItemById($this->communicationItemId);
        }
        return $this->aSendCustomers;
    }
    
    /**
     * check whether CommunicationItem is linked to the CustomerGroup
     * @param int $iCustomerGroupId
     * @return boolean
     */
    public function isLinkedToCustomerGroup($iCustomerGroupId) {
        
        # return by default false
        $bResult = false;
        
        # when not yet set, get linked CustomerGroups
        if(empty($this->aCustomerGroups)) {
            $this->getCustomerGroups();
        }
        
        # loop through linked CustomerGroups
        foreach($this->aCustomerGroups as $oCustomerGroup) {
            # check whether user is linked to customGroup
            if($oCustomerGroup->customerGroupId == $iCustomerGroupId) {
                $bResult = true;
            }
        }
        
        # return result
        return $bResult;
    }
    
    /**
     * replace template variables with real values
     * @param mixed $oObject
     */
    public function replaceVariables($oObject, $aKeysSkipEncoding = array()) {

        $aKeys = array();
        $aValues = array();

        if ($oObject instanceof Customer) {
            $oCustomer = $oObject;
        }

        # customer related
        if (!empty($oCustomer)) {
            $aKeys[] = '[customer_fullName]';
            if(in_array('[customer_fullName]', $aKeysSkipEncoding)) {
                $aValues[] = $oCustomer->getFullName();
            } else {
                $aValues[] = _e($oCustomer->getFullName());
            }

            $aKeys[] = '[customer_confirmCode]';
            $aValues[] = _e($oCustomer->confirmCode);

            $aKeys[] = '[customer_email]';
            $aValues[] = _e($oCustomer->email);
        }
        
        $aArticles = $this->getIncludedArticles();

        if (!empty($aArticles)) {

            $sArticles = '<table border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            <table border="0" width="100%" cellspacing="0" cellpadding="0" align="left" bgcolor="#FFFFFF">
                                                
                                                    <tr>
                                                        <td style="padding: 0 10px;" align="left" bgcolor="#e7e4db"><a href="http://buildingthebridge.eu" target="_blank"><img style="display: block;width:290px!important;" src="http://buildingthebridge.eu/files/communication/images/header-btb-small.jpg?v1" alt="" width="290"/></a></td>
                                                    </tr>

                                                    <tr>
                                                        <td style="font-size: 0; line-height: 0;">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-size: 0; line-height: 0; border-top: 3px solid #ffffff;" bgcolor="#383838" height="30">&nbsp;</td>
                                                    </tr>                                                    
                                                    <tr>
                                                        <td style="padding: 10px 10px 20px 10px;">
                                                            <table border="0" width="100%" cellspacing="0" cellpadding="0" align="left">
                                                                
                                                                    <tr>
                                                                        <td>' . $this->message . '</td>
                                                                    </tr>
                                                                
                                                            </table>
                                                        </td>
                                                    </tr>                                                
                                            </table>
                                        </td>
                                    </tr>';                                                    
            foreach ($aArticles AS $oArticle) {
                $oImage = $oArticle->getImages('first-online');
                $oImageFile = null;

                if (!empty($oImage)) {
                    $oImageFile = $oImage->getImageFileByReference('detail');
                }

                $sArticles .= '<tr><td><hr style="    
    display: block; 
    height: 0px !important;
    border: 0px; 
    background: transparent;
    border-bottom: 1px solid #eee;    
    color: #eee !important;
    margin: 0px;
    padding: 0; "></td></tr>';
                $sArticles .= '<tr><td style="padding: 30px 10px 20px 10px;">';
                $sArticles .= '         <table border="0" cellspacing="0" cellpadding="0" width="100%">';
                $sArticles .= '             <tr><td><h3>' . $oArticle->title . '</h3></td></tr>';  
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>';                
                $sArticles .= '             <tr><td class="responsive-image" align="left">' . (!empty($oImageFile) ? '<img src="' . CLIENT_HTTP_URL . $oImageFile->link . '" alt="' . $oArticle->title . '" width="290" />' : '') . '</td></tr>';
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>';
                $sArticles .= '             <tr><td><p>' . firstXCharacters($oArticle->intro, 181) . '</td></tr>';
                $sArticles .= '             <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>';                
                $sArticles .= '             <tr><td><table>'
                        . '                     <tr><td style="background: #208ed3; padding: 4px;">'
                        . '                         <div class="readmore">'
                        . '                             <a style="color: #ffffff; text-decoration: none;" href="' . CLIENT_HTTP_URL . $oArticle->getUrlPath() . '" target="_blank">READ MORE</a>'
                        . '                         </div></td></tr>'
                        . '                     </table></td></tr>'
                        . '             </table></td></tr>';                
            }
            $sArticles .= '
                            <tr>
                                <td class="responsive-image">&nbsp;</td>
                           </tr>
                                <tr>
                                <td style="padding: 0 10px 0 10px; color: #ffffff;" align="left" bgcolor="#eeece3" height="30">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                <td style="color: #ffffff;">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0" class="responsive-image-footericons">
                                <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top"><strong><span style="font-family: Arial, Verdana, sans-serif; font-size: 15px; color: #444444;">Building the Bridge</span></strong></td>
                                <td>&nbsp;</td>
                                <td rowspan="3" align="right"><a style="text-decoration: none;" href="http://www.linkedin.com/company/building-the-bridge" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-linkedin.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="https://www.facebook.com/pages/Building-the-Bridge/664545083667211" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-facebook.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="https://twitter.com/abraham9333" target="_blank"><img src="https://buildingthebridge.eu/files/communication/images/footer-icon-twitter.jpg" alt="" width="32" height="32" border="0" /></a> &nbsp; <a style="text-decoration: none;" href="http://buildingthebridge.eu/rss" target="_blank"><img src="http://buildingthebridge.eu/files/communication/images/footer-icon-rss.jpg" alt="" width="32" height="32" border="0" /></a></td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="top">&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">
                                <p style="font-size: 12px; font-family: Arial, Verdana, sans-serif; color: #ffffff;"><a style="color: #444444;" href="mailto:info@buildingthebridge.eu">info@buildingthebridge.eu</a></p>
                                </td>
                                <td align="left" valign="top">&nbsp;</td>
                                </tr>
                                <tr>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="top">&nbsp;</td>
                                <td align="left" valign="middle">&nbsp;</td>
                                </tr>
                                </table>
                                </td>
                                </tr>
                                </table>
                                </td>
                                </tr>
                                </table>
                                </td>
                                </tr>
                                </table>';

            $this->message = $sArticles;
        } else {
            /* template waarden vervangen */
            $this->message = str_replace($aKeys, $aValues, $this->message);
            $this->messageWithRedirectUrls = str_replace($aKeys, $aValues, $this->messageWithRedirectUrls);
        }
    }
    
    /**
     * Get the articles included in this item
     * 
     * @return array Article
     */
    public function getIncludedArticles() {
        $aArticleFilter = array();
        $aArticleFilter['communicationItemId'] = $this->communicationItemId;
        $aArticleFilter['onlyIncluded'] = 1;
        return ArticleManager::getArticlesByFilter($aArticleFilter);
    }

    /**
     * get all images by specific list name for a communicationItem
     * @param string $sList
     * @return Image or array Images
     */
    public function getImages($sList = 'all') {
        if (!isset($this->aImages[$sList])) {
            switch ($sList) {
                case 'all':
                    $this->aImages[$sList] = CommunicationItemManager::getImagesByFilter($this->communicationItemId, array('showAll' => true));
                    break;
                default:
                    die('no option');
                    break;
            }
        }
        return $this->aImages[$sList];
    }
    
    /*
     * get amount of customers which opened the email
     * @return array Customer
     */
    public function getConversionStats() {
        if($this->aConversionItems === null) {
            $this->aConversionItems =  CommunicationItemManager::getConversionStats($this->communicationItemId);
        }
        return $this->aConversionItems;
    }
    
    /*
     * get amount of blacklisted customers for an email
     * @return array Customer
     */
    public function getBlacklistItems() {
        if($this->aBlacklistedCustomers === null) {
            $this->aBlacklistedCustomers = CommunicationItemManager::getBlacklistedCustomers($this->communicationItemId);
        }
        return $this->aBlacklistedCustomers;
    }
    
    /*
     * get amount of blacklisted customers for an email
     * @return array Customer
     */
    public function getBouncedItems() {
        if($this->aBouncedCustomers === null) {
            $this->aBouncedCustomers = CommunicationItemManager::getBouncedCustomersByCommunicationItemId($this->communicationItemId);
        }
        return $this->aBouncedCustomers;
    }
    
    /**
     * Set the included articles in the communication item as sent
     *
     */
    public function setIncludedArticlesAsSent() {
        $aArticles = $this->getIncludedArticles();

        foreach ($aArticles as $oArticle) {
            CommunicationItemManager::setArticleAsSentforCommunicationItem($oArticle->articleId, $this->communicationItemId);
        }
    }
    
    /*
     * get redirect links of an email
     * @return array Customer
     */
    public function getAmountOfRedirectLinks() {
        if($this->aRedirectLinks === null) {
            $this->aRedirectLinks = CommunicationItemLinkRedirectManager::getCommunicationItemLinkRedirectsByCommunicationItemId($this->communicationItemId);
        }
        return $this->aRedirectLinks;
    }
    
    /*
     * check if SPF record is set
     * @return boolean
     */
    public function hasValidSPFrecord() {
        # get sender domain
        $aExplodedEmailDomain = array_reverse(explode('@', $this->senderEmail));
        $sExplodedEmailDomain = $aExplodedEmailDomain[0];
        # get DNS record
        $aRecordInfo = dns_get_record($sExplodedEmailDomain, DNS_TXT);
        # validation
        if(!empty($aRecordInfo[0]['txt']) && strpos($aRecordInfo[0]['txt'], self::SPF_RULE)) {
            return true;
        } else {
            return false;
        }
    }
    
}
