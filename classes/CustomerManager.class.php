<?php

// Models and managers used by this class
require_once 'Customer.class.php';

class CustomerManager {

    /**
     * get a Customer by id
     * @param int $iCustomerId
     * @return Customer
     */
    public static function getCustomerById($iCustomerId) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customers`
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Customer");
    }

    /**
     * get a Customer by email
     * @param string $sEmail
     * @return Customer 
     */
    public static function getCustomerByEmail($sEmail) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customers`
                    WHERE
                        `email` = ' . db_str($sEmail) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Customer");
    }
    
    /**
     * get customers by customerGroupId
     * @param int $iCustomerGroupId
     * @param boolean $bFilterOnline
     * @param boolean $bExcludeBounced
     * @param boolean $bFilterBounced
     * @return Customer 
     */
    public static function getCustomersByCustomerGroupId($iCustomerGroupId, $bFilterOnline = false, $bExcludeBounced = false, $bFilterBounced = false) {
        
        $sQuery = ' SELECT
                        *
                    FROM
                        `customerGroupRelations` as `cgr`
                    JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cgr`.`customerGroupId` = ' . db_int($iCustomerGroupId);
        
        # filter online property
        if($bFilterOnline) {
            $sQuery .= ' AND `c`.`online` = 1 ';
        }
        
        # exclude bounced property
        if($bExcludeBounced) {
            $sQuery .= ' AND `c`.`bounceRuleId` IS NULL ';
        }
        
        # filter bounced property
        if($bFilterBounced) {
            $sQuery .= ' AND `c`.`bounceRuleId` IS NOT NULL ';
        }

        $sQuery .= ';';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }


    /**
     * get amount of customers by customerGroupId
     * @param int $iCustomerGroupId
     * @param boolean $bFilterOnline
     * @param boolean $bFilterBounced
     * @return Customer 
     */
    public static function getAmountOfCustomersByCustomerGroupId($iCustomerGroupId, $bFilterOnline = false, $bFilterBounced = false) {
        
        $sQuery = ' SELECT
                        COUNT(*) as `amount`
                    FROM
                        `customerGroupRelations` as `cgr`
                    JOIN
                        `customers` as `c`
                    USING
                        (`customerId`)
                    WHERE
                        `cgr`.`customerGroupId` = ' . db_int($iCustomerGroupId);
        
        # filter online property
        if($bFilterOnline) {
            $sQuery .= ' AND `c`.`online` = 1 ';
        }
        
        # filter bounced property
        if($bFilterBounced) {
            $sQuery .= ' AND `c`.`bounceRuleId` IS NOT NULL ';
        }

        $sQuery .= ';';
        $oDb = DBConnections::get();
        $aResult = $oDb->query($sQuery, QRY_UNIQUE_ARRAY);
        $iAmountOfCustomers = $aResult['amount'];
        return $iAmountOfCustomers;
    }
    
    /**
     * get a Customer by confirmCode and Email
     * @param string $sConfirmCode
     * @param string $sEmail
     * @return Customer 
     */
    public static function getCustomerByConfirmCodeAndEmail($sConfirmCode, $sEmail) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customers`
                    WHERE
                        `confirmCode` = ' . db_str($sConfirmCode) . '
                    AND
                        `email` = ' . db_str($sEmail) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Customer");
    }

    /**
     * get all Customer objects
     * @return array Customer
     */
    public static function getAllCustomers() {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customers`
                    ORDER BY
                        `created` DESC
                    ;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "Customer");
    }

    /**
     * return customers filtered by a few options
     * @param array $aFilter filter properties (q)
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Customer objects 
     */
    public static function getCustomersByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`c`.`name`' => 'ASC')) {

        $sWhere = '';
        $sFrom = '';
        
        # show offline accounts
        if (empty($aFilter['showAll'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`online` = 1 ';
        }
        
        # search for q
        if (!empty($aFilter['name'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '(`c`.`name` LIKE ' . db_str('%' . $aFilter['name'] . '%') . ' OR `c`.`companyName` LIKE ' . db_str('%' . $aFilter['name'] . '%') . ')';
        }

        # search for customers which may receive test e-mail from communication module
        if (!empty($aFilter['receiveCommunicationVerification'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`receiveCommunicationVerification` = 1 ';
        }
        
        # check online yes/no
        if (!empty($aFilter['online'])) {
            if($aFilter['online'] === true) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`online` = 1 ';    
            } elseif ($aFilter['online'] === false) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`online` = 0 ';
            }
        }
        
        # check for contactByEmail yes/no
        if (!empty($aFilter['contactByEmail'])) {
            if($aFilter['contactByEmail'] === true) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`contactByEmail` = 1 ';    
            } elseif ($aFilter['contactByEmail'] === false) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`contactByEmail` = 0 ';
            }
        }
        
        # check for bounced yes/no
        if (isset($aFilter['isBounced'])) {
            if($aFilter['isBounced'] === true) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`bounceRuleDescription` IS NOT NULL ';
            } elseif($aFilter['isBounced'] === false) {
                $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`c`.`bounceRuleDescription` IS NULL ';
            }
        }

        # handle order by
        $sOrderBy = '';
        if (count($aOrderBy) > 0) {
            foreach ($aOrderBy AS $sColumn => $sOrder) {
                $sOrderBy .= ($sOrderBy !== '' ? ',' : '') . $sColumn . ' ' . $sOrder;
            }
        }
        $sOrderBy = ($sOrderBy !== '' ? 'ORDER BY ' : '') . $sOrderBy;

        # handle start,limit
        $sLimit = '';
        if (is_numeric($iLimit)) {
            $sLimit .= db_int($iLimit);
        }
        if ($sLimit !== '') {
            $sLimit = (is_numeric($iStart) ? db_int($iStart) . ',' : '0,') . $sLimit;
        }
        $sLimit = ($sLimit !== '' ? 'LIMIT ' : '') . $sLimit;

        $sQuery = ' SELECT '.($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '').'
                        `c`.*
                    FROM
                        `customers` AS `c`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aProducts = $oDb->query($sQuery, QRY_OBJECT, "Customer");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }
        return $aProducts;
    }

    /**
     * check if the email address exists
     * @param string $sEmail
     * @param int $iCustomerId
     * @return bool 
     */
    public static function emailExists($sEmail, $iCustomerId = null) {
        $oCustomer = self::getCustomerByEmail($sEmail);
        if (!empty($oCustomer)) {
            if ($iCustomerId === null || $oCustomer->customerId != $iCustomerId) {
                return true;
            }
        }
        return false;
    }

    /**
     * save a Customer
     * @param Customer $oCustomer
     */
    public static function saveCustomer(Customer $oCustomer, $bAddUserToGeneralCustomerAccount = true) {
        $sQuery = ' INSERT INTO `customers`(
                        `customerId`,
                        `companyName`,
                        `companyAddress`,
                        `companyPostalCode`,
                        `companyCity`,
                        `companyEmail`,
                        `companyPhone`,
                        `companyWebsite`,
                        `name`,
                        `age`,
                        `countryId`,
                        `city`,
                        `mobilePhone`,
                        `phone`,
                        `email`,
                        `gender`,
                        `password`,
                        `contactBySms`,
                        `contactByEmail`,
                        `didSignOffForEmail`,
                        `confirmCode`,
                        `online`,
                        `receiveCommunicationVerification`,
                        `created`
                    )
                    VALUES (
                        ' . db_int($oCustomer->customerId) . ',
                        ' . db_str($oCustomer->companyName) . ',
                        ' . db_str($oCustomer->companyAddress) . ',
                        ' . db_str($oCustomer->companyPostalCode) . ',
                        ' . db_str($oCustomer->companyCity) . ',
                        ' . db_str($oCustomer->companyEmail) . ',
                        ' . db_str($oCustomer->companyPhone) . ',
                        ' . db_str($oCustomer->companyWebsite) . ',
                        ' . db_str($oCustomer->name) . ',
                        ' . db_str($oCustomer->age) . ',
                        ' . db_str($oCustomer->countryId) . ',
                        ' . db_str($oCustomer->city) . ',
                        ' . db_str($oCustomer->mobilePhone) . ',
                        ' . db_str($oCustomer->phone) . ',
                        ' . db_str($oCustomer->email) . ',
                        ' . db_str($oCustomer->gender) . ',
                        ' . db_str($oCustomer->password) . ',
                        ' . db_int($oCustomer->contactBySms) . ',
                        ' . db_int($oCustomer->contactByEmail) . ',
                        ' . db_int($oCustomer->didSignOffForEmail) . ',
                        ' . db_str($oCustomer->confirmCode) . ',
                        ' . db_int($oCustomer->online) . ',
                        ' . db_int($oCustomer->receiveCommunicationVerification) . ',
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        `companyName`=VALUES(`companyName`),
                        `companyAddress`=VALUES(`companyAddress`),
                        `companyPostalCode`=VALUES(`companyPostalCode`),
                        `companyCity`=VALUES(`companyCity`),
                        `companyEmail`=VALUES(`companyEmail`),
                        `companyPhone`=VALUES(`companyPhone`),
                        `companyWebsite`=VALUES(`companyWebsite`),
                        `name`=VALUES(`name`),
                        `age`=VALUES(`age`),
                        `countryId`=VALUES(`countryId`),
                        `city`=VALUES(`city`),
                        `mobilePhone`=VALUES(`mobilePhone`),
                        `phone`=VALUES(`phone`),
                        `email`=VALUES(`email`),
                        `gender`=VALUES(`gender`),
                        `password`=VALUES(`password`),
                        `contactBySms`=VALUES(`contactBySms`),
                        `contactByEmail`=VALUES(`contactByEmail`),
                        `didSignOffForEmail`=VALUES(`didSignOffForEmail`),
                        `confirmCode`=VALUES(`confirmCode`),
                        `online`=VALUES(`online`),
                        `receiveCommunicationVerification`=VALUES(`receiveCommunicationVerification`)
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if ($oCustomer->customerId === null) {
            $oCustomer->customerId = $oDb->insert_id;
            # by default, set customer in general group
            if($bAddUserToGeneralCustomerAccount) {
                $oCustomer->setCustomerGroups(array(CustomerGroupManager::getCustomerGroupById(CustomerGroup::CUSTOMERGROUP_NEWSLETTER)));
            }
        }
        
        self::saveCustomerGroups($oCustomer);
    }
    
    /**
     * Save the customerGroup relations of a customer
     * @param CustomerGroup object
     */
    private static function saveCustomerGroups(Customer $oCustomer) {
        $aCustomerGroups = $oCustomer->getCustomerGroups();
        
        // Delete all customerGroup relations of this customer
        $sQuery = "DELETE FROM `customerGroupRelations` WHERE `customerId` = " . db_int($oCustomer->customerId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        // Insert all customerGroup relations of this customer
        $sQueryValues = '';
        foreach ($aCustomerGroups AS $oCustomerGroup) {
            $sQueryValues.= (!empty($sQueryValues) ? ',' : '') . '(' . db_int($oCustomerGroup->customerGroupId) . ',' . db_int($oCustomer->customerId) . ')';
        }

        /* save User Module relation */
        if (!empty($sQueryValues)) {
            $sQuery = " INSERT IGNORE INTO
                            `customerGroupRelations`
                        (
                            `customerGroupId`,
                            `customerId`
                        )
                        VALUES " . $sQueryValues . "
                        ;";
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }
    
    /**
     * save a Customers bounce rule id & bounce title/description
     * @param string $sCustomerEmail
     * @param int $iBounceRuleId
     * @param string $sBounceRuleTitle
     * @param string $sBounceRuleDescription
     * @param int $iContactByEmail = 0
     */
    public static function saveCustomerBounceRuleDescriptionByCustomerEmail($sCustomerEmail, $iBounceRuleId, $sBounceRuleTitle, $sBounceRuleDescription, $iContactByEmail = 0) {
        $sQuery = ' UPDATE
                        `customers`
                    SET
                        `bounceRuleId` = ' . db_int($iBounceRuleId) . ',
                        `bounceRuleTitle` = ' . db_str($sBounceRuleTitle) . ',
                        `bounceRuleDescription` = ' . db_str($sBounceRuleDescription) . ',
                        `contactByEmail` = ' . db_int($iContactByEmail) . '
                    WHERE
                        `email` = ' . db_str($sCustomerEmail) . '
                    ;';
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * Save the customerGroup relations of a customer
     * @param int $iCustomerId
     * @param int $iCustomerGroupId
     */
    public static function saveCustomerGroupRelation($iCustomerId, $iCustomerGroupId) {
        
        $sQuery = " INSERT IGNORE INTO
                        `customerGroupRelations`
                    (
                        `customerGroupId`,
                        `customerId`
                    )
                    VALUES (
                        " . db_int($iCustomerGroupId) . ",
                        " . db_int($iCustomerId) . "
                    )
                    ;";
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * update online status of Customer by id
     * @param int $bOnline
     * @param int $iCustomerId
     * @return bool
     */
    public static function updateOnlineByCustomerId($bOnline, $iCustomerId) {
        $sQuery = ' UPDATE
                        `customers`
                    SET
                        `online` = ' . db_int($bOnline) . '
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }
    
    /**
     * update status of Customer setting for e-mail settings
     * @param int $iCustomerId
     * @return bool
     */
    public static function updateEmailPreferenceByCustomerId($iCustomerId) {
        $sQuery = ' UPDATE
                        `customers`
                    SET
                        `contactByEmail` = 0,
                        `didSignOffForEmail` = 1
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        # check if something happened
        return $oDb->affected_rows > 0;
    }

    /**
     * confirm a Customer by confirmCode and email (make him online)
     * @param string $sConfirmCode
     * @param string $sEmail
     * @return bool 
     */
    public static function confirmCustomerByConfirmCodeAndEmail($sConfirmCode, $sEmail) {
        # get the Customer by confirmCode
        $oCustomer = self::getCustomerByConfirmCodeAndEmail($sConfirmCode, $sEmail);

        # check if Customer exists
        if (empty($oCustomer)) {
            return false;
        } else {
            # update the Customer
            $sQuery = ' UPDATE
                            `customers`
                        SET
                            `confirmCode` = NULL,
                            `online` = 1
                        WHERE
                            `customerId` = ' . db_int($oCustomer->customerId) . '
                        LIMIT 1
                        ;';

            $oDb = DBConnections::get();
            $oDb->query($sQuery, QRY_NORESULT);

            self::updateLastLoginByCustomerId($oCustomer->customerId); //update last login date and time
            self::setCustomerInSession($oCustomer); // set Customer in session

            # check if something happened
            return $oDb->affected_rows > 0;
        }
    }

    /**
     * delete a Customer
     * @param Customer $oCustomer
     * @return bool true
     */
    public static function deleteCustomer(Customer $oCustomer) {
        $sQuery = ' DELETE FROM
                        `customers`
                    WHERE
                        `customerId` = ' . db_int($oCustomer->customerId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return true;
    }

    /**
     * update last login timestamp and reset the confirmCode
     * @param int $iCustomerId
     */
    public static function updateLastLoginByCustomerId($iCustomerId) {
        $sQuery = ' UPDATE
                        `customers`
                    SET
                        `confirmCode` = NULL,
                        `lastLogin` = NOW(),
                        `modified` = `modified`
                    WHERE
                        `customerId` = ' . db_int($iCustomerId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * set Customer in session
     * @param Customer $oCustomer 
     */
    public static function setCustomerInSession(Customer $oCustomer) {
        $oCustomer->maskPass(); // mask pass XXX for session
        $_SESSION['oCurrentCustomer'] = $oCustomer; // set Customer in session
    }

    /**
     * get Customer by email and pass and set in session
     * @param string $sEmail
     * @param string $sPassword
     * @return mixed Customer/false
     */
    public static function login($sEmail, $sPassword) {
        $sQuery = ' SELECT
                        *
                    FROM
                        `customers`
                    WHERE
                        `email` = ' . db_str($sEmail) . ' AND
                        `password` = ' . db_str(hashPasswordForDb($sPassword)) . ' AND
                        `online` = 1
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        $oCustomer = $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "Customer");

        if (!empty($oCustomer)) {
            self::updateLastLoginByCustomerId($oCustomer->customerId); //update last login date and time
            self::setCustomerInSession($oCustomer); // set Customer in session

            return $oCustomer;
        }

        # no Customer found return false
        return false;
    }

    /**
     * update a Customer's password and then login
     * @param Customer $oCustomer
     * @return mixed Customer/false
     */
    public static function updatePasswordAndLogin(Customer $oCustomer) {
        # update the Customer's password
        $sQuery = ' UPDATE
                        `customers`
                    SET
                        `password` = ' . db_str($oCustomer->password) . ',
                        `confirmCode` = NULL,
                        `lastLogin` = NOW()
                    WHERE
                        `customerId` = ' . db_int($oCustomer->customerId) . '
                    LIMIT 1
                    ;';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        # login
        self::setCustomerInSession($oCustomer); // set Customer in session
    }

    /**
     * logout Customer
     * @param string $sRedirectLocation (redirect location)
     */
    public static function logout($sRedirectLocation) {
        unset($_SESSION['oCurrentCustomer']);
        http_redirect($sRedirectLocation); //go to redirect location
    }

}

?>