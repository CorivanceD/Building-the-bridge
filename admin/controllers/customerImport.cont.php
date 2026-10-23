<?

$iStart = microtime(true);
# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = 'Klanten import';
$oPageLayout->sTemplateName = 'Klanten import';

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
// column mapping. Columms are always in the language chosen and are mapped to the right fields in the database
$aImportFields = array(
    array('prop' => 'name', 'column' => 'name', 'required' => true),
    array('prop' => 'mobilePhone', 'column' => 'mobilePhone', 'required' => false),
    array('prop' => 'email', 'column' => 'email', 'required' => true)
);

$aRequiredColumns = array('email', 'name');
$aSupportedColumns = array();
foreach ($aImportFields AS $aImportField) {
    $aSupportedColumns[$aImportField['column']] = $aImportField['prop'];
}

// flip cols to use when invalid objects get tried to be saved
$aSupportedProps = array_flip($aSupportedColumns);

$aLogs = array();
if (http_post('action') == 'import') {

    $iTotal = 0;
    $iSaved = 0;
    $iErrors = 0;
    $iWarnings = 0;

    include DOCUMENT_ROOT . '/libs/PHPExcel/PHPExcel.php';

    $bTestMode = http_get('testMode', false);
    if (!empty($_FILES['file']) && $_FILES['file']['error'] === 0) {

        $sFile = $_FILES['file']['name'];
        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        $sFileName = strtolower(pathinfo($sFile, PATHINFO_FILENAME));
        $sFileLocation = $_FILES['file']['tmp_name'];

        try {
            switch ($sExtension) {
                case 'xls':
                    $oReader = PHPExcel_IOFactory::createReader('Excel5');
                    $oReader->setReadDataOnly(true);
                    $oPHPExcel = $oReader->load($sFileLocation);
                    break;
                case 'xlsx':
                    $oReader = PHPExcel_IOFactory::createReader('Excel2007');
                    $oReader->setReadDataOnly(true);
                    $oPHPExcel = $oReader->load($sFileLocation);
                    break;
                default:
                    $oPHPExcel = PHPExcel_IOFactory::load($sFileLocation);
                    break;
            }
        } catch (PHPExcel_Reader_Exception $e) {
            $aErrors[] = 'Bestand kon niet worden gelezen';
        }

        if (!empty($oPHPExcel)) {
            $oSheet = $oPHPExcel->getSheet(0);

            // prepare rows
            $aColumns = array();
            $aValues = array();
            foreach ($oSheet->getRowIterator() as $oRow) {
                $oCellIterator = $oRow->getCellIterator();
                $oCellIterator->setIterateOnlyExistingCells(false);
                if ($oRow->getRowIndex() == 1) {
                    $bSetColumns = true;
                } else {
                    $bSetColumns = false;
                }
                foreach ($oCellIterator as $oCell) {
                    if ($bSetColumns) {
                        $aColumns[$oCell->getColumn()] = $oCell->getValue();
                        continue;
                    }
                    $aValues[$oCell->getRow()][$aColumns[$oCell->getColumn()]] = $oCell->getValue();
                }
            }

            $bIsMissingRequired = false;
            foreach ($aRequiredColumns AS $sColumn) {
                if (!in_array($sColumn, $aColumns)) {
                    $aErrors[] = 'Verplichte kolom `' . $sColumn . '` mist';
                    $bIsMissingRequired = true;
                }
            }

            // not missing required fields?
            if (!$bIsMissingRequired) {
                $aColumnsByName = array_flip($aColumns);
                
                foreach ($aValues AS $iRow => $aValue) {

                    // show all fields from one row
                    //_d($aValue);
                    //die;

                    $sEmail = $aValue['email'];
                    if (($oCustomer = CustomerManager::getCustomerByEmail($sEmail))) {
                        $aCustomerGroups = $oCustomer->getCustomerGroups();
                    } else {
                        $oCustomer = new Customer();
                        $oCustomer->setDefaultCsvImportValues();
                        $aCustomerGroups = array();
                    }

                    foreach ($aValue AS $sColumn => $sValue) {
                        
                        if (array_key_exists($sColumn, $aSupportedColumns)) {
                            $sProp = $aSupportedColumns[$sColumn];
                            $oCustomer->$sProp = $sValue;
                            
                        } else {
                            $aLogs[$iRow]['warnings'][] = 'Kolom is niet ondersteund en wordt niet opgeslagen `' . $sColumn . '`';
                            $iWarnings++;
                        }
                    }

                    // add customer groups, no problem that there are already some groups.
                    foreach (http_post('customerGroupIds', array()) AS $iCustomerGroupId) {
                        $aCustomerGroups[] = new CustomerGroup(array('customerGroupId' => $iCustomerGroupId));
                    }

                    // set groups in customer
                    $oCustomer->setCustomerGroups($aCustomerGroups);
                    if ($oCustomer->isValid()) {
                        if (!empty($oCustomer->customerId)) {
                            $aLogs[$iRow]['saved'][] = 'Klant gevonden';
                        } else {
                            $aLogs[$iRow]['saved'][] = 'Nieuwe klant';
                        }
                        if (!$bTestMode) {
                            CustomerManager::saveCustomer($oCustomer);
                        }
                        $aLogs[$iRow]['saved'][] = 'Klant opgeslagen `' . $oCustomer->getFullName() . '`';
                        $iSaved++;
                    } else {
                        foreach ($oCustomer->getInvalidProps() AS $sInvalidProp) {
                            //not a supported property, so do fill in another way
                            if (empty($aSupportedProps[$sInvalidProp])) {
                                // some properties are not supported to fill by import file. Do set in another way because like this they are not valid.
                                _d('Property not supported by import, fill another way');
                                _d($sInvalidProp);
                                continue;
                            }
                            $sColumn = $aSupportedProps[$sInvalidProp];
                            $aLogs[$iRow]['errors'][] = 'Waarde `' . $oCustomer->$sInvalidProp . '` is niet juist voor kolom`' . $sColumn . '`';
                            $iErrors++;
                        }
                        $aLogs[$iRow]['errors'][] = 'Klant kan niet worden opgeslagen `' . $oCustomer->getFullName() . '`';
                    }
                    $iTotal++;
                }
            }
        }
    } else {
        $aErrors[] = 'Fout bij uploaden bestand';
    }
}

$oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customerImport/customerImport_form.inc.php';

# include default template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>