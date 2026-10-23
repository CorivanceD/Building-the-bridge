<?php

require_once 'Country.class.php';

class CountryManager {

    /**
     * 
     * @param int $iCountryId
     * @return Country
     */
    public static function getCountry($iCountryId) {
        $sQuery = ' SELECT
                        `co`.*
                    FROM
                        `countries` AS `co`
                    WHERE
                        `co`.`countryId` = ' . db_int($iCountryId) . ' LIMIT 1;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Country');
    }

    /**
     * 
     * @return array Country
     */
    public static function getCountries() {
        $sQuery = ' SELECT
                        `co`.*
                    FROM
                        `countries` AS `co`;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Country');
    }

    /**
     * 
     * @return array Country
     */
    public static function getCountriesInWorldLeaders() {
        $aCountries = array();

        foreach (self::getCountries() AS $oCountry) {
            $aResult = ArticleCategoryManager::getArticleCategoriesByFilter(array('name' => $oCountry->name, 'parentPageId' => Page::WORLD_LEADER_PAGE));

            if (!empty($aResult)) {
                $aCountries[] = $oCountry;
            }
        }

        return $aCountries;
    }

    /**
     * Get a country by name
     * @param string $sCountryName
     * @return Country
     */
    public static function getCountryByName($sCountryName) {
        $sQuery = ' SELECT * 
                    FROM `countries` 
                    WHERE `name` LIKE ' . db_str('%' . $sCountryName . '%') . '
                        OR `name` LIKE ' . db_str($sCountryName . '%') . '
                    LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Country');
    }

    /**
     * Get the regions
     * 
     * @return array Region     
     */
    public static function getRegions() {
        $sQuery = ' SELECT `r`.*
                    FROM `regions` AS `r`
                    ORDER BY `r`.`order` ASC;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Region');
    }

    /**
     * Get a Region object by id
     * 
     * @param int $iRegionId
     * @return Region
     */
    public static function getRegionById($iRegionId) {
        $sQuery = ' SELECT `r`.*
                    FROM `regions` AS `r`
                    WHERE `r`.`regionId` = ' . db_int($iRegionId)
                . ' LIMIT 1;';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Region');
    }

    /**
     * Get the countries of a region
     * 
     * @param int $iRegionId
     * @return array Country     
     */
    public static function getCountriesByRegionId($iRegionId) {
        $sQuery = ' SELECT DISTINCT `c`.*
                        FROM `countries` AS `c`
                        JOIN `worldLeaderAddresses` AS `wla` USING (`countryId`)
                        WHERE `wla`.`regionId` = ' . db_int($iRegionId)
                . ' ORDER BY `c`.`name` ASC;';

        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'Country');
    }

}

?>