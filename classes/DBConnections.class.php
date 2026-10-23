<?php

/**
 * class to store DBconnections and retreive a connections
 */
class DBConnections {

    private static $aDBConnections = array();

    /**
     * get a stored connection
     * @param string $sName name of the connection
     * @return DBConnection object
     */
    public static function get($sName = 'default') {
        if (!isset(self::$aDBConnections[$sName])) {
            self::set();
        }

        return self::$aDBConnections[$sName];
    }

    /**
     * set a connection or make a default one
     * @param DBConnection $oDb connection object
     * @param string $sName connection name
     */
    public static function set(DBConnection $oDb = null, $sName = 'default') {
        if ($oDb instanceof DBConnection) {
            self::$aDBConnections[$sName] = $oDb;
        } else {
            self::$aDBConnections[$sName] = new DBConnection();
        }
    }

}

?>
