<?php

// Models and managers used by this class
require_once 'CommunicationItem.class.php';

class CommunicationItemManager {

    /**
     * get a CommunicationItem by id
     * @param int $iCommunicationItemId
     * @return CatalogProductType
     */
    public static function getCommunicationItemById($iCommunicationItemId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItems`
                    WHERE
                        `communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "CommunicationItem");
    }

    /**
     * get all CommunicationItem objects
     * @param array $aFilter
     * @return array CommunicationItem
     */
    public static function getAllCommunicationItems(array $aFilter = array()) {
        
        $sWhere = '';
        if(isset($aFilter['sended'])) {
            if($aFilter['sended'] == 0) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ') . '`ci`.`sended` = 0 ';
            }
            if($aFilter['sended'] == 1) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ') . '`ci`.`sended` = 1 ';
            }
        }
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItems` as `ci` ';
         $sQuery .= $sWhere;
         $sQuery .= ' ORDER BY
                        `ci`.`communicationItemId` DESC
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CommunicationItem");
    }
    
    /**
     * get all CommunicationItem objects
     * @param string $sDate
     * @return array CommunicationItem
     */
    public static function getCommunicationItemsByMonthAndYear($sDate) {
        
        $sWhere = '';
        $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ') . '`cil`.`month` = MONTH(' . db_date($sDate) . ') ';
        $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ') . '`cil`.`year` = YEAR(' . db_date($sDate) . ') ';
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerCommunicationItemLogs` as `cil` ';
        $sQuery .= $sWhere;
         
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "CommunicationItem");
    }
    
    /**
     * get customers of sended CommunicationItem
     * @param CommunicationItem $oCommunicationItem
     */
    public static function getSendCustomersForCommunicationItemById($iCommunicationItemId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerCommunicationItemLogs` as `ccil`
                    LEFT JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `ccil`.`communicationItemId` = ' . db_int($iCommunicationItemId);

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }

    /**
     * get cronjobs of communicationItems
     * @param array $aFilter
     */
    public static function getCronjobCommunicationItems($aFilter = array()) {
        
        $sWhere = '';
        
        if(isset($aFilter['isSentToApi']) && is_numeric($aFilter['isSentToApi'])) {
            if($aFilter['isSentToApi'] == 0) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' isSentToApi = 0';
            } elseif($aFilter['isSentToApi'] == 1) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' isSentToApi = 1';
            }
        }
        
        if(isset($aFilter['isSendingData']) && is_numeric($aFilter['isSendingData'])) {
            if($aFilter['isSendingData'] == 0) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' isSendingData = 0';
            } elseif($aFilter['isSendingData'] == 1) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' isSendingData = 1';
            }
        }
        
        if(isset($aFilter['sending']) && is_numeric($aFilter['sending'])) {
            if($aFilter['sending'] == 0) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' sending = 0 ';
            } elseif($aFilter['sending'] == 1) {
                $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ' ) . ' sending = 1 ';
            }
        }
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemCronjobs` ';
        $sQuery.= $sWhere;

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_ASSOC);
    }
    
    /**
     * save a CommunicationItem
     * @param CommunicationItem $oCommunicationItem
     */
    public static function saveCommunicationItem(CommunicationItem $oCommunicationItem) {
        $sQuery = ' INSERT INTO `communicationItems`(
                        `communicationItemId`,
                        `type`,
                        `templateId`,
                        `title`,
                        `subject`,
                        `senderName`,
                        `senderEmail`,
                        `message`,
                        `pendingInBulkCron`,
                        `hasBulkSendingError`,
                        `sending`,
                        `sended`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCommunicationItem->communicationItemId) . ',
                        ' . db_str($oCommunicationItem->type) . ',
                        ' . db_int($oCommunicationItem->templateId) . ',
                        ' . db_str($oCommunicationItem->title) . ',
                        ' . db_str($oCommunicationItem->subject) . ',
                        ' . db_str($oCommunicationItem->senderName) . ',
                        ' . db_str($oCommunicationItem->senderEmail) . ',
                        ' . db_str($oCommunicationItem->message) . ',
                        ' . db_int($oCommunicationItem->pendingInBulkCron) . ',
                        ' . db_int($oCommunicationItem->hasBulkSendingError) . ',
                        ' . db_int($oCommunicationItem->sending) . ',
                        ' . db_int($oCommunicationItem->sended) . ',
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        `type`=VALUES(`type`),
                        `templateId`=VALUES(`templateId`),
                        `title`=VALUES(`title`),
                        `subject`=VALUES(`subject`),
                        `senderName`=VALUES(`senderName`),
                        `senderEmail`=VALUES(`senderEmail`),
                        `message`=VALUES(`message`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oCommunicationItem->communicationItemId === null)
            $oCommunicationItem->communicationItemId = $oDb->insert_id;
        
        self::saveCustomerGroupRelation($oCommunicationItem);
    }
    
    /**
     * get images for communicationItem by filter
     * @param int $iCommunicationItemId
     * @param array $aFilter
     * @param int $iLimit
     * @return array Image
     */
    public static function getImagesByFilter($iCommunicationItemId, array $aFilter = array(), $iLimit = null) {
        $sWhere = '';
        if (empty($aFilter['showAll'])) {
            $sWhere .= ' AND `i`.`online` = 1';
        }

        $sQuery = ' SELECT
                        `i`.*
                    FROM
                        `images` AS `i`
                    JOIN
                        `communicationItemsImages` AS `cii` USING (`imageId`)
                    WHERE
                        `cii`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    ' . $sWhere . '
                    ORDER BY
                        `i`.`order` ASC, `i`.`imageId` ASC
                    ' . ($iLimit ? 'LIMIT ' . db_int($iLimit) : '') . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Image');
    }
    
    /**
     * save connection between a newsItem and an image
     * @param int $iCommunicationItemId
     * @param int $iImageId
     */
    public static function saveCommunicationItemImageRelation($iCommunicationItemId, $iImageId) {
        $sQuery = ' INSERT IGNORE INTO
                        `communicationItemsImages`
                    (
                        `communicationItemId`,
                        `imageId`
                    )
                    VALUES
                    (
                        ' . db_int($iCommunicationItemId) . ',
                        ' . db_int($iImageId) . '
                    )
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * Save the CustomerGroup relations of a CommunicationItem
     * @param CustomerGroup object
     */
    private static function saveCustomerGroupRelation(CommunicationItem $oCommunicationItem) {
        $aCustomerGroups = $oCommunicationItem->getCustomerGroups();
        
        // Delete all CustomerGroup relations of this customer
        $sQuery = "DELETE FROM `communicationItemCustomerGroupRelation` WHERE `communicationItemId` = " . db_int($oCommunicationItem->communicationItemId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        // Insert all customerGroup relations of this customer
        $sQueryValues = '';
        foreach ($aCustomerGroups AS $oCustomerGroup) {
            $sQueryValues.= (!empty($sQueryValues) ? ',' : '') . '(' . db_int($oCommunicationItem->communicationItemId) . ',' . db_int($oCustomerGroup->customerGroupId) . ')';
        }

        /* save User Module relation */
        if (!empty($sQueryValues)) {
            $sQuery = " INSERT IGNORE INTO
                            `communicationItemCustomerGroupRelation`
                        (
                            `communicationItemId`,
                            `customerGroupId`
                        )
                        VALUES " . $sQueryValues . "
                        ;";
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }
    
    /**
     * save a Customer CommunicationItemLog
     * @param Customer $oCustomer
     * @param CommunicationItem $oCommunicationItem
     */
    public static function saveCommunicationItemLog($oCustomer, $oCommunicationItem) {
        
        # determine which date to fetch
        switch($oCommunicationItem->type) {
            case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
                $sReceivedDestination = $oCustomer->email;
                break;
            case CommunicationItem::COMMUNICATION_TYPE_SMS:
                $sReceivedDestination = $oCustomer->mobilePhone;
                break;
        }
        
        $sQuery = ' INSERT INTO `customerCommunicationItemLogs`(
                        `communicationItemId`,
                        `customerId`,
                        `name`,
                        `receivedDestination`,
                        `type`,
                        `subject`,
                        `messageParts`,
                        `senderName`,
                        `senderEmail`,
                        `month`,
                        `year`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCommunicationItem->communicationItemId) . ',
                        ' . db_int($oCustomer->customerId) . ',
                        ' . db_str($oCustomer->getFullName()) . ',
                        ' . db_str($sReceivedDestination) . ',
                        ' . db_str($oCommunicationItem->type) . ',
                        ' . db_str($oCommunicationItem->subject) . ',
                        ' . db_int($oCommunicationItem->messageParts) . ',
                        ' . db_str($oCommunicationItem->senderName) . ',
                        ' . db_str($oCommunicationItem->senderEmail) . ',
                        ' . db_int(date('m')) . ',
                        ' . db_int(date('Y')) . ',
                        NOW()
                    )
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

    }
    
    /**
     * save a copy of the CommunicationItem with hashed URL's
     * @param CommunicationItem $oCommunicationItem
     */
    public static function updateCommunicationItemRedirectUrlVersion($oCommunicationItem) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `messageWithRedirectUrls` = ' . db_str($oCommunicationItem->messageWithRedirectUrls) . '
                    WHERE
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }
    
    /**
     * save the message of the CommunicationItem
     * @param CommunicationItem $oCommunicationItem
     */
    public static function updateCommunicationItemMessage($oCommunicationItem) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `message` = ' . db_str($oCommunicationItem->message) . '
                    WHERE
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }
    
    /**
     * update status of the cronjob of a CommunicationItem 
     * @param CommunicationItem $oCommunicationItem
     * @param int $iStatus
     */
    public static function updateCommunicationItemCronjobReadingStatus($oCommunicationItem, $iStatus) {
        $sQuery = ' UPDATE
                        `communicationItemCronjobs`
                    SET
                        `isSentToApi` = ' . db_int($iStatus) . '
                    WHERE
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }
    
    /**
     * save a CommunicationItem/Customer relation
     * @param CommunicationItem $oCommunicationItem
     * @param $aCustomers array
     */
    public static function saveCommunicationItemReceivers($oCommunicationItem, $aCustomers = array()) {
        
        # get total amount of customers
        $iCustomerCount = count($aCustomers);
        
        # break limit
        $iQueryBreakLimit = 25;

        $sQueryPart = '';
        $iTotalCounter = 0;
        $iQueryCounter = 1;
        $iQueryPartsCounter = 0;
        $aQueryParts = array();
        
        # foreach given customer, make query
        foreach($aCustomers as $oCustomer) {
        
            # add one to total counter
            $iTotalCounter++;
            
            $sQueryPart .= '(
                            ' . db_int($oCommunicationItem->communicationItemId) . ',
                            ' . db_int($oCustomer->customerId) . ',
                            0,
                            NOW()
                        ),';
            
            # loop and assign/reset data
            if( $iQueryCounter == $iQueryBreakLimit || $iCustomerCount == $iTotalCounter ) {
                
                # reset counter
                $iQueryCounter = 1;
                
                # add sql part
                $aQueryParts[$iQueryPartsCounter] = $sQueryPart;
                
                # update query parts counter
                $iQueryPartsCounter++;
                
                # reset query part
                $sQueryPart = '';

            } else {
                # update global counter
                $iQueryCounter++;
            }

        }

        # split query (and trim last comma)
        foreach($aQueryParts as $sQueryPart) {
                        
            # trim last comma
            $sQueryPart = rtrim($sQueryPart, ',').';';
            
            # save relation
            $sQuery = ' INSERT INTO `communicationItemsSendingProgress`(
                            `communicationItemId`,
                            `customerId`,
                            `hasReceivedMessage`,
                            `created`
                        )
                        VALUES
                        ' . $sQueryPart . '
                        ';

            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
        }

    }
    
    /**
     * mark a Customer as 'has received message`
     * @param CommunicationItem $oCommunicationItem
     * @param Customer $oCustomer
     */
    public static function markCustomerAsHasReceivedMessage($oCommunicationItem, $oCustomer) {
        $sQuery = ' UPDATE
                        `communicationItemsSendingProgress`
                    SET
                        `hasReceivedMessage` = 1
                    WHERE
                        `customerId` = ' . db_int($oCustomer->customerId) . '
                    AND
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * mark CommunicationItem state as sending
     * @param CommunicationItem $oCommunicationItem
     */
    public static function markCommunicationItemAsSending($oCommunicationItem) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `sending` = 1,
                        `pendingInBulkCron` = 0
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
        $sQuery = ' UPDATE
                        `communicationItemCronjobs`
                    SET
                        `isSendingData` = 0,
                        `sending` = 1
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * mark CommunicationItem state as having error
     * @param CommunicationItem $oCommunicationItem
     */
    public static function markCommunicationItemAsHavingBulkSendingError($oCommunicationItem) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `hasBulkSendingError` = 1
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * mark CommunicationItem state as sending
     * @param CommunicationItem $oCommunicationItem
     * @param int $iPendingStatus
     */
    public static function updateCommunicationItemPendingStatus($oCommunicationItem, $iPendingStatus) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `pendingInBulkCron` = ' . db_int($iPendingStatus) . '
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * mark CommunicationItem state as sending to API
     * @param CommunicationItem $oCommunicationItem
     * @param int $iSendingStatus
     */
    public static function updateCommunicationItemApiSendingStatus($oCommunicationItem, $iSendingStatus) {
        $sQuery = ' UPDATE
                        `communicationItemCronjobs`
                    SET
                        `isSendingData` = ' . db_int($iSendingStatus) . '
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * mark CommunicationItem state as sended
     * @param CommunicationItem $oCommunicationItem
     */
    public static function markCommunicationItemAsSended($oCommunicationItem) {
        $sQuery = ' UPDATE
                        `communicationItems`
                    SET
                        `sended` = 1,
                        `sending` = 0,
                        `pendingInBulkCron` = 0,
                        `sendDate` = NOW()
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        
       // delete instances of CommunicationItem from sendlist 
        self::deleteCommunicationItemFromSendlist($oCommunicationItem);
    }
    
    /**
     * delete instances of CommunicationItem from sendlist
     * @param CommunicationItem $oCommunicationItem
     */
    public static function deleteCommunicationItemFromSendlist($oCommunicationItem) {
        $sQuery = ' DELETE FROM
                        `communicationItemsSendingProgress`
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    
    /**
     * delete cronjob instances of CommunicationItem from sendlist
     * @param CommunicationItem $oCommunicationItem
     */
    public static function deleteCronjobFromCronjoblist($oCommunicationItem) {
        $sQuery = ' DELETE FROM
                        `communicationItemCronjobs`
                    WHERE 
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    LIMIT 
                        1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    
    /**
     * get customers of sending CommunicationItem
     * @param Customer $oCustomer
     */
    public static function getUnsendedCustomersFromSendlistByCommunicationId($iCommunicationItemId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemsSendingProgress` as `cisp`
                    JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cisp`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    AND
                        `cisp`.`hasReceivedMessage` = 0
                    ';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }
    
    /**
     * get customers of sending CommunicationItem
     * @param int $iCommunicationItemId
     * @param boolean $bReturnSendedReceiver
     */
    public static function getTotalAmountOfCustomersFromSendlistByCommunicationId($iCommunicationItemId, $bReturnSendedReceiver = false) {
        
        # filter received persons
        $sWhere = '';
        if($bReturnSendedReceiver === true) {
            $sWhere .= 'AND `cisp`.`hasReceivedMessage` = 1';
        }
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemsSendingProgress` as `cisp`
                    JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cisp`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    '.$sWhere.'
                    ';

        $oDb = DBConnections::get();
        $aCustomers = $oDb->query($sQuery, QRY_OBJECT, "Customer");
        return count($aCustomers);
        
    }
    
    /**
     * get a CommunicationItem statistic from customer & communicationItem
     * @param int $iCommunicationItemId
     * @param int $iCustomerId
     */
    public static function getCustomerCommunicationItemStat($iCommunicationItemId, $iCustomerId) {
        $sQuery = ' SELECT 
                        *
                    FROM
                        `communicationItemStats` as `cis`
                    LEFT JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cis`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    AND
                        `customerId` = ' . db_int($iCustomerId) . '
                    LIMIT
                        1
                    ';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }
    
    /**
     * save a CommunicationItem statistic
     * @param int $iCommunicationItemId
     * @param int $iCustomerId
     */
    public static function saveCommunicationItemStat($iCommunicationItemId, $iCustomerId) {
        
        # get customer
        $oCustomer = CustomerManager::getCustomerById($iCustomerId);
        
        # when existing user, save stat
        if(!empty($oCustomer)) {
            $sQuery = ' INSERT INTO `communicationItemStats`(
                            `communicationItemId`,
                            `customerId`,
                            `name`,
                            `email`,
                            `created`
                        )
                        VALUES (
                            ' . db_int($iCommunicationItemId) . ',
                            ' . db_int($iCustomerId) . ',
                            ' . db_str($oCustomer->getFullName()) . ',
                            ' . db_str($oCustomer->email) . ',
                            NOW()
                        )
                        ;';

            $oDb = DBConnections::get();
            return $oDb->query($sQuery, QRY_NORESULT);
        }

    }

    /**
     * get amount of conversion pixels by communicationId
     * @param int $iCommunicationItemId
     * @return int $iOpened
     */
    public static function getConversionStats($iCommunicationItemId) {

        $sQuery = ' SELECT
                        `cis`.*,
                        `c`.`email` as `clientEmail`,
                        `c`.`name` as `name`,
                        `c`.`created` as `clientCreated`
                    FROM
                        `communicationItemStats` as `cis`
                    LEFT JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cis`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }
    
    /**
     * get blacklisted customers by communicationId
     * @param int $iCommunicationItemId
     * @return int $iBlacklistAmount
     */
    public static function getBlacklistedCustomers($iCommunicationItemId) {
        
        $sQuery = ' SELECT
                        `cicb`.`communicationItemId` as `communicationItemId`,
                        `cicb`.`name` as `name`,
                        `cicb`.`email` as `email`,
                        `c`.`created` as `clientCreated`
                    FROM
                        `communicationItemCustomersBlacklist` as `cicb`
                    LEFT JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cicb`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }
    
    /**
     * set a customer on the communicationItem blacklist
     * @param CommunicationItem $oCommunicationItem
     * @param int $iCommunicationItemId
     */
    public static function setCustomerOnCommunicationBlacklist($oCustomer, $iCommunicationItemId) {
        $sQuery = ' INSERT INTO `communicationItemCustomersBlacklist`(
                        `communicationItemId`,
                        `customerId`,
                        `name`,
                        `email`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($iCommunicationItemId) . ',
                        ' . db_int($oCustomer->customerId) . ',
                        ' . db_str($oCustomer->getFullName()) . ',
                        ' . db_str($oCustomer->email) . ',
                        NOW()
                    )
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * get bounced emailadresses by communicationId
     * @param int $iCommunicationItemId
     * @return array Customers
     */
    public static function getBouncedCustomersByCommunicationItemId($iCommunicationItemId) {
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `communicationItemCustomersBouncelist` as `cicb`
                    WHERE
                        `cicb`.`communicationItemId` = ' . db_int($iCommunicationItemId) . '
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }
    
    /**
     * sets a CommunicationItem in the cron table
     * @param CommunicationItem $oCommunicationItem
     * @param int $iMailingAppCustomerId (from mailing application)
     * @param int $iMailingAppCustomerAccountId (from mailing application)
     * @param int $iSending default 0
     */
    public static function setCommunicationItemInCron($oCommunicationItem, $iMailingAppCustomerId, $iMailingAppCustomerAccountId, $iSending = 0) {

        $sQuery = ' INSERT INTO `communicationItemCronjobs`(
                        `communicationItemId`,
                        `mailingCustomerId`,
                        `mailingCustomerAccountId`,
                        `sending`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCommunicationItem->communicationItemId) . ',
                        ' . db_int($iMailingAppCustomerId) . ',
                        ' . db_int($iMailingAppCustomerAccountId) . ',
                        ' . db_int($iSending) . ',
                        NOW()
                    )';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;

    }
    
    /**
     * set a customer on the communicationItem bouncelist
     * @param string $sCustomerEmail
     * @param int $iBounceRuleId
     * @param string $sBounceRuleTitle
     * @param string $sBounceRuleDescription
     * $param int $iCommunicationItemId
     */
    public static function setCustomerOnCommunicationBouncelist($sCustomerEmail, $iBounceRuleId, $sBounceRuleTitle, $sBounceRuleDescription, $iCommunicationItemId) {
        $sQuery = ' INSERT INTO `communicationItemCustomersBouncelist`(
                        `communicationItemId`,
                        `email`,
                        `bounceRuleId`,
                        `bounceRuleTitle`,
                        `bounceRuleDescription`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($iCommunicationItemId) . ',
                        ' . db_str($sCustomerEmail) . ',
                        ' . db_int($iBounceRuleId) . ',
                        ' . db_str($sBounceRuleTitle) . ',
                        ' . db_str($sBounceRuleDescription) . ',
                        NOW()
                    )
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * delete a CommunicationItem
     * @param CommunicationItem $oCommunicationItem
     * @return bool true
     */
    public static function deleteCommunicationItem(CommunicationItem $oCommunicationItem) {
        if ($oCommunicationItem->isDeletable()) {
            
            # delete images
            foreach($oCommunicationItem->getImages() as $oImage) {
                ImageManager::deleteImage($oImage);
            }
            
            # delete object
            $sQuery = ' DELETE FROM
                        `communicationItems`
                    WHERE
                        `communicationItemId` = ' . db_int($oCommunicationItem->communicationItemId) . '
                    LIMIT 1
                    ;';
            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * 
     * @param int $iArticleId
     * @param int $iCommunicationItemId
     * @param int $bInclude
     * @return boolean
     */
    public static function addArticleToCommunicationItem($iArticleId, $iCommunicationItemId, $bInclude) {
        $sQuery = ' INSERT INTO `communicationItemArticles`  (
                        `communicationItemId`, 
                        `articleId`, 
                        `included`
                    ) 
                    VALUES 
                    ('
                    .db_int($iCommunicationItemId) . ', '
                    .db_int($iArticleId) . ','
                    .db_int($bInclude) .
                    ') 
                    ON DUPLICATE KEY UPDATE 
                        `included`=VALUES(`included`)';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
}

    /**
     * Set an article as already sent in a communication item
     * 
     * @param int $iArticleId
     * @param int $iCommunicationItemId
     * @return boolean
     */
    public static function setArticleAsSentforCommunicationItem($iArticleId, $iCommunicationItemId) {
        $sQuery = 'UPDATE `communicationItemArticles` SET `alreadySent` = 1 WHERE `articleId` = '.db_int($iArticleId) . ' AND `communicationItemId` = '.db_int($iCommunicationItemId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0 ;
    }
}

?>