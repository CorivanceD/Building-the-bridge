<?

# set document root
define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

# get parameters
$iCommunicationItemId = http_get('communicationItemId');
$iCustomerId = http_get('customerId');

if(is_numeric($iCommunicationItemId) && is_numeric($iCustomerId)) {
    $oStatistic = CommunicationItemManager::getCustomerCommunicationItemStat($iCommunicationItemId, $iCustomerId);
    if(empty($oStatistic)) {
        # save stat
        CommunicationItemManager::saveCommunicationItemStat($iCommunicationItemId, $iCustomerId);
    }
}

header('Content-Type: image/png');
readfile(DOCUMENT_ROOT . '/files/communication/images/communication-pixel.png');

?>