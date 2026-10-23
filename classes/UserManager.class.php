<?

/* Models and managers used by the UserManager model */
require_once 'User.class.php';

class UserManager {

    /**
     * get a user by userId
     * @param int $iUserId
     * @return User
     */
    public static function getUserById($iUserId) {
        $sQuery = " SELECT
                        `u`.*
                    FROM
                        `users` AS `u`
                    WHERE `u`.`userId` = " . db_int($iUserId) . "
                ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "User");
    }

    /**
     * get a user by username
     * @param string $sUsername
     * @return User
     */
    public static function getUserByUsername($sUsername) {
        $sQuery = " SELECT
                        `u`.*
                    FROM
                        `users` AS `u`
                    WHERE `u`.`username` = " . db_str($sUsername) . "
                ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "User");
    }

    /**
     * get a user by email
     * @param string $sEmail
     * @return User
     */
    public static function getUserByEmail($sEmail) {
        $sQuery = " SELECT
                        `u`.*
                    FROM
                        `users` AS `u`
                    WHERE `u`.`email` = " . db_str($sEmail) . "
                ;";

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "User");
    }
    
    /**
     * 
     * @return User
     */
//    public static function getUserEditor() {
//        $sQuery = 'SELECT * FROM `users` WHERE `users`.`editor` = 1 LIMIT 1';
//        $oDb = DBConnections::get();
//        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'User');
//    }
    
    /**
     * 
     * @return User
     */
    public static function getUserSuperEditor() {
        $sQuery = 'SELECT * FROM `users` WHERE `users`.`superEditor` = 1 LIMIT 1';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'User');
    }    

    /**
     * return users filtered by a few options
     * @param array $aFilter filter properties
     * @param int $iLimit limit number of records returned
     * @param int $iStart start from this record
     * @param int $iFoundRows foundRows when there was no limit (default = false so doesn't check by default)
     * @param array $aOrderBy array(database coloumn name => order) add order by columns and orders
     * @return array Module
     */
    public static function getUsersByFilter(array $aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`u`.`userId`' => 'ASC')) {
        global $oCurrentUser;
        $sFrom = '';
        $sWhere = '`u`.`userId` != '.db_int(User::ANONYMOUS_USER_ID); //hide anonymous use;

        // only admin can see admin
        if ($oCurrentUser->userId != 1) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`u`.`userId` != 1';
        }

        if (!empty($aFilter['editorId'])) {
            $sWhere .= ($sWhere != '' ? ' AND ' : '') . '`u`.`editorId` = '.db_int($aFilter['editorId']) . ' AND `u`.`userProfile` = '.db_int(User::USER_WRITER);
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

        $sQuery = ' SELECT ' . ($iFoundRows !== false ? 'SQL_CALC_FOUND_ROWS' : '') . '
                        `u`.*
                    FROM
                        `users` AS `u`
                    ' . $sFrom . '
                    ' . ($sWhere != '' ? 'WHERE ' . $sWhere : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aUsers = $oDb->query($sQuery, QRY_OBJECT, "User");
        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }

        return $aUsers;
    }

    /**
     * get user by email and pass and set in session
     * @param string $sUsername
     * @param string $sPassword
     * @return mixed User/false
     */
    public static function login($sUsername, $sPassword) {
        $sQuery = " SELECT
                        `u`.*
                    FROM
                        `users` as `u`
                    WHERE
                        `u`.`username` = " . db_str($sUsername) . "
                    AND
                        `u`.`password` = " . db_str(hashPasswordForDb($sPassword)) . "
                    ;";

        $oDb = DBConnections::get();
        $oUser = $oDb->query($sQuery, QRY_UNIQUE_OBJECT, "User");

        if ($oUser) {
            self::loginUser($oUser);
            self::updateLastLogin($oUser->userId); //update last login date and time
            return true;
        }

        /* no user found return false */
        return false;
    }

    /**
     * do actually login user in session and mask password
     * @param User $oUser
     */
    private static function loginUser(User $oUser) {
        $oUser->maskPass(); // mask pass XXX for session
        sysTranslations::reset(); // reset system translations
        $_SESSION['oCurrentUser'] = $oUser; // set user in session
    }

    /**
     * Logout user
     * @param string $sRedirect (redirect location)
     */
    public static function logout($sRedirectLocation) {

        unset($_SESSION['oCurrentUser']);
        sysTranslations::reset(); // reset system translations
        http_redirect($sRedirectLocation); //go to redirect location
    }

    /**
     * save User object
     * @param User $oUser
     */
    public static function saveUser(User $oUser) {

        $oDb = DBConnections::get();
        /* new user */
        if (!$oUser->userId) {
            $sQuery = " INSERT INTO
                            `users`
                            (
                                `name`,
                                `username`,
                                `userProfile`,
                                `administrator`,
                                `seo`,
                                `systemLanguageId`,
                                `created`,
                                `password`,
                                `email`,
                                `editorId`,
                                `description`                               
                            )
                            VALUES
                            ("
                    . db_str($oUser->name) . ", "
                    . db_str($oUser->username) . ", "
                    . db_int($oUser->userProfile) . ", "
                    . db_int($oUser->getAdministrator()) . ", "
                    . db_int($oUser->getSeo()) . ", "
                    . db_int($oUser->systemLanguageId) . ", "
                    . "NOW()" . ", "
                    . db_str(hashPasswordForDb($oUser->password)) . ", "
                    . db_str($oUser->email) . ", "
                    . db_int($oUser->editorId ) . ", "
                    . db_str($oUser->description) . ");";

            $oDb->query($sQuery, QRY_NORESULT);

            /* get new userId */
            $oUser->userId = $oDb->insert_id;
        } else {
            /* Password needs a hash!! */
            $sQuery = " UPDATE
                            `users`
                        SET
                            `name` = " . db_str($oUser->name) . ",
                            `username` = " . db_str($oUser->username) . ",
                            `userProfile` = " . db_str($oUser->userProfile) . ",                                
                            `administrator` = " . db_int($oUser->getAdministrator()) . ",
                            `seo` = " . db_int($oUser->getSeo()) . ",
                            `systemLanguageId` = " . db_str($oUser->systemLanguageId) . ",
                            `password` = " . db_str($oUser->password) . ",
                            `email` = " . db_str($oUser->email) . ",
                            `editorId` = " . db_int($oUser->editorId) . ",
                            `description` = " . db_str($oUser->description) . "
                        WHERE
                            `userId` = " . db_int($oUser->userId) . "
                        ;";
            $oDb->query($sQuery, QRY_NORESULT);
        }

        /* save module relations */
        self::saveModules($oUser);

        global $oCurrentUser;
        if ($oCurrentUser->userId === $oUser->userId) {
            self::loginUser($oUser);
        }
    }
    
    /**
     * 
     * @param int $iUserId
     * @param int $iImageId
     */
    public static function saveUserImageRelation($iUserId, $iImageId) {
        $oUser = UserManager::getUserById($iUserId);      
        
        if (!empty($oUser->imageId)) {
            ImageManager::deleteImage($oUser->getImage());
        }
        
        $sQuery = 'UPDATE `users` SET `imageId` = '.db_int($iImageId) . ' WHERE `userId` = '.db_int($iUserId);
        
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * delete user
     * @param User $oUser
     * @return boolean
     */
    public static function deleteUser(User $oUser) {    
        
        if (!empty($oUser->imageId)) {
            ImageManager::deleteImage($oUser->getImage());
        }
        
        $oDb = DBConnections::get();

        $sQuery = "DELETE FROM `users` WHERE `userId` = " . db_int($oUser->userId) . ";";
        $oDb->query($sQuery, QRY_NORESULT);

        return $oDb->affected_rows > 0;
    }

    /**
     * Save the module relations of a user
     * @param Module object
     */
    private static function saveModules(User $oUser) {
        // Delete all module relations of this user
        $sQuery = "DELETE FROM `usersModules` WHERE `userId` = " . db_int($oUser->userId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        // Insert all modules relations of this user
        $sQueryValues = '';
        foreach ($oUser->getModules('active-all') AS $oModule) {
            $sQueryValues.= (!empty($sQueryValues) ? ', ' : '') . '(' . db_int($oUser->userId) . ', ' . db_int($oModule->moduleId) . ')

            ';
        }

        /* save User Module relation */
        if (!empty($sQueryValues)) {
            $sQuery = " INSERT IGNORE INTO
                            `usersModules`
                        (
                            `userId`,
                            `moduleId`
                        )
                        VALUES " . $sQueryValues . "
                        ;";
            $oDb->query($sQuery, QRY_NORESULT);
        }
    }

    /**
     * update last login timestamp
     * @param int $iUserId
     */
    private static function updateLastLogin($iUserId) {
        $sQuery = " UPDATE
                        `users`
                    SET
                        `lastLogin` = NOW(),
                        `modified` = `modified`
                    WHERE
                        `userId` = " . db_int($iUserId) . "
                    ;";
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
    }
    
    /**
     * 
     * @return array User
     */
    public static function getEditors()  {
        $sQuery = " SELECT
                        `u`.*
                    FROM
                        `users` AS `u`
                    WHERE `u`.`userProfile` = ".db_int(User::USER_EDITOR);

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, "User");        
    }

}

?>