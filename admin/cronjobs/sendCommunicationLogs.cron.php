<?php

set_time_limit(0);
ini_set('memory_limit', '-1');

// set document root
if (!defined('DOCUMENT_ROOT')) {
    define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
}
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

$sReferenceName = 'sendCommunicationLogs';

# placeholders
$aTypeEmail = array();
$aTypeSms = array();

# get sended communicationitems of last month
$sDateLastMonth = date("Y-m-d", strtotime("-1 months"));
$iTimeStamp = strtotime($sDateLastMonth);

# get name of last month
$sLastMonth = legacy_strftime('%B', $iTimeStamp);

# fetch result
$aSendedCommunicationItems = CommunicationItemManager::getCommunicationItemsByMonthAndYear($sDateLastMonth);

# determine how much sms/email were send last month
foreach($aSendedCommunicationItems as $oSendedCommunicationItem) {
    # check whether message was split up
    for($i = 1; $i <= $oSendedCommunicationItem->messageParts; $i++) {
        switch($oSendedCommunicationItem->type) {
            case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
                # Mail is sended by mailnetwork, so DON'T count local send e-mail
                if($oSendedCommunicationItem->created <= '2015-05-20 00:00:00') {
                $aTypeEmail[] = $oSendedCommunicationItem;
                }
                break;
            case CommunicationItem::COMMUNICATION_TYPE_SMS:
                $aTypeSms[] = $oSendedCommunicationItem;
                break;
        }
    }
}

# calculate costs
$iBillEmail = count($aTypeEmail) * COMMUNICATION_COST_EMAIL;
$iBillSms = count($aTypeSms) * COMMUNICATION_COST_SMS;

# set up mail
$sMailLayout = 'style="font-size: 12px; font-family: Arial"';
$sMail = '
    <table>
        <tr>
            <td colspan="3" ' . $sMailLayout . '><strong>CommunicationLog klant \'' . CLIENT_NAME . '\'</strong></td>
        </tr>
        <tr>
            <td colspan="3" ' . $sMailLayout . '>&nbsp;</td>
        </tr>
        <tr>
            <td ' . $sMailLayout . '><i>Klantnaam</i></td>
            <td ' . $sMailLayout . '><i>' . CLIENT_NAME . '</i></td>
            <td ' . $sMailLayout . '><i>Totale kosten</i></td>
        </tr>
        <tr>
            <td ' . $sMailLayout . '>Datum</td>
            <td ' . $sMailLayout . '>' . $sLastMonth . '</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td ' . $sMailLayout . '>Verzonden e-mails (Mail wordt via het verzendnetwerk verzonden en wordt vanaf daar gerapporteerd)</td>
            <td ' . $sMailLayout . '><center>' . count($aTypeEmail) . '</center></td>
            <td ' . $sMailLayout . '>Kosten: €' . number_format($iBillEmail, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td ' . $sMailLayout . '>Verzonden SMSjes</td>
            <td ' . $sMailLayout . '><center>' . count($aTypeSms) . '</center></td>
            <td ' . $sMailLayout . '>Kosten: €' . number_format($iBillSms, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td colspan="3" ' . $sMailLayout . '>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" ' . $sMailLayout . '>Totale factuur maand ' . $sLastMonth . ': <strong>€' . number_format($iBillEmail+$iBillSms, 2, ',', '.').'</strong></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>
';

if (DEBUG) {
    echo $sMail;
}

if (!empty($aTypeSms)) {
# send report
MailManager::sendMail(COMMUNICATION_EMAIL_REPORT, 'CommunicationLog ' . CLIENT_NAME . ' maand ' . $sLastMonth, $sMail);
}