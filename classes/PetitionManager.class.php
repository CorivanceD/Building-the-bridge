<?php

class PetitionManager {

    /**
     * 
     * @param int $iPetitionId
     * @return Petition
     */
    public static function getPetitionById($iPetitionId) {
        $sQuery = 'SELECT *  FROM `petitions` WHERE `petitionId` = ' . db_int($iPetitionId) . ';';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'Petition');
    }

    /**
     * 
     * @param int $iPetitionId
     * @param boolean $bFlag
     */
    public static function setActivePetition($iPetitionId, $bFlag) {
        $oDb = DBConnections::get();
        $sQuery = 'UPDATE `petitions` SET `online` = ' . ($bFlag ? 1 : 0) . ' WHERE `petitionId` = ' . db_int($iPetitionId);
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
    }

    /**
     * 
     * @param Petition $oPetition
     */
    public static function savePetition($oPetition) {
        $sQuery = 'INSERT INTO `petitions` (
                        `petitionId`,
                        `author`,
                        `email`,
                        `phone`,
                        `countryId`,
                        `title`,
                        `intro`,
                        `imageId`,
                        `description`,
                        `requiredSignatures`,
                        `online`,
                        `created`)
                   VALUES('
                . db_int($oPetition->petitionId) . ', '
                . db_str($oPetition->author) . ', '
                . db_str($oPetition->email) . ', '
                . db_str($oPetition->phone) . ', '
                . db_int($oPetition->countryId) . ', '
                . db_str($oPetition->title) . ', '
                . db_str($oPetition->intro) . ', '
                . db_int($oPetition->imageId) . ', '
                . db_str($oPetition->description) . ', '
                . db_int($oPetition->requiredSignatures) . ', '
                . db_int($oPetition->online) . ',
                        NOW())
                   ON DUPLICATE KEY UPDATE
                        `author`=VALUES(`author`),
                        `email`=VALUES(`email`),
                        `phone`=VALUES(`phone`),
                        `countryId`=VALUES(`countryId`),
                        `title`=VALUES(`title`),
                        `intro`=VALUES(`intro`),
                        `imageId`=VALUES(`imageId`),
                        `description`=VALUES(`description`),
                        `requiredSignatures`=VALUES(`requiredSignatures`),
                        `online`=VALUES(`online`)
                   ';

        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);

        if (empty($oPetition->petitionId)) {
            $oPetition->petitionId = $oDb->insert_id;
        }
    }

    public static function getPetitions($aFilter = array(), $iLimit = null, $iStart = 0, &$iFoundRows = false, $aOrderBy = array('`p`.`created`' => 'DESC')) {
        $sWhere = '';
        $sGroupBy = '';

        # return only the active ones by default
        if (empty($aFilter['showAll'])) {
            $sWhere .= (empty($sWhere) ? ' WHERE ' : ' AND ') . '`p`.`online` = 1';
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
                        `p`.*
                    FROM
                        `petitions` AS `p`
                    ' . $sWhere . '
                    ' . ($sGroupBy != '' ? 'GROUP BY ' . $sGroupBy : '') . '
                    ' . $sOrderBy . '
                    ' . $sLimit . '
                    ;';

        $oDb = DBConnections::get();
        $aPetitions = $oDb->query($sQuery, QRY_OBJECT, "Petition");

        if ($iFoundRows !== false) {
            $iFoundRows = $oDb->query('SELECT FOUND_ROWS() AS `found_rows`;', QRY_UNIQUE_OBJECT)->found_rows;
        }


        return $aPetitions;
    }

    /**
     * 
     * @param int $iPetitionId
     * @return array PetitionSignature
     */
    public static function getPetitionSignatures($iPetitionId) {
        $sQuery = 'SELECT * FROM `petitionSignatures` WHERE `petitionId` = ' . db_int($iPetitionId) . ' ORDER BY `date` DESC';
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_OBJECT, 'PetitionSignature');
    }

    /**
     * 
     * @param int $iPetitionId
     * @param int $iCustomerId
     * @return PetitionSignature
     */
    public static function getPetitionSignatureById($iPetitionId, $iCustomerId) {
        $sQuery = 'SELECT * FROM `petitionSignatures` WHERE `petitionId` = ' . db_int($iPetitionId) . ' AND `customerId` = ' . db_int($iCustomerId) . ' LIMIT 1;';
        $oDb = DBconnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'PetitionSignature');
    }

    /**
     * 
     * @param PetitionSignature $oPetitionSignature
     */
    public static function savePetitionSignature($oPetitionSignature) {
        $sQuery = 'INSERT INTO `petitionSignatures` (
                        `petitionId`,
                        `customerId`,
                        `comment`,
                        `date`)
                   VALUES ('
                . db_int($oPetitionSignature->petitionId) . ', '
                . db_int($oPetitionSignature->customerId) . ', '
                . db_str($oPetitionSignature->comment) . ',
                        NOW());';

        $oDb = DBConnections::get();

        $oDb->query($sQuery, QRY_NORESULT);
    }

    /**
     * 
     * @param int $iCustomerId
     * @param int $iPetitionId
     * @return PetitionSignature
     */
    public static function getCustomerPetitionSignature($iCustomerId, $iPetitionId) {
        $sQuery = 'SELECT * FROM `petitionSignatures` WHERE `petitionId` = ' . db_int($iPetitionId) . ' AND `customerId` = ' . db_int($iCustomerId);
        $oDb = DBConnections::get();
        return $oDb->query($sQuery, QRY_UNIQUE_OBJECT, 'PetitionSignature');
    }

    /**
     * 
     * @param int $iPetitionId
     * @param int $iImageId
     * @return boolean
     */
    public static function savePetitionImageRelation($iPetitionId, $iImageId) {
        $sQuery = 'UPDATE `petitions` SET `imageId` = ' . db_int($iImageId) . ' WHERE `petitionId` = ' . db_int($iPetitionId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
    }

    /**
     * 
     * @param Petition $oPetition
     */
    public static function deletePetition($oPetition) {
        if (!empty($oPetition->imageId)) {
            ImageManager::deleteImage(v);
        }

        $sQuery = 'DELETE FROM `petitions` WHERE `petitionId` = ' . db_int($oPetition->petitionId);
        $oDb = DBConnections::get();
        $oDb->query($sQuery, QRY_NORESULT);
        return $oDb->affected_rows > 0;
    }

}

?>