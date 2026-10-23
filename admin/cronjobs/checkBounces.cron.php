<?php

set_time_limit(0);
ini_set('memory_limit', '-1');

// set docuemnt root
if (!defined('DOCUMENT_ROOT')) {
    define("DOCUMENT_ROOT", '/var/www/vhosts/buildingthebridge.eu/domains/buildingthebridge.eu/public_html');
}
include_once DOCUMENT_ROOT . '/inc/config.inc.php';

$iInstanceCounter = 0;
$aCustomers = CustomerManager::getCustomersByFilter(array('online' => true, 'contactByEmail' => true, 'isBounced' => false));

# calculate amount of sending parts
$iMaxCustomersPerApiCall = 250;
$iPartsCounter = ceil(count($aCustomers) / $iMaxCustomersPerApiCall);

# placeholders
$aApiCallParts = array();
$iApiCallPart = 1;
$iApiCallPartCounter = 1;

# seperate customers for API call
foreach($aCustomers as $oCustomer) {
    $aApiCallParts[$iApiCallPart][] = $oCustomer;
    if($iApiCallPartCounter == $iMaxCustomersPerApiCall) {
        $iApiCallPart++;
        $iApiCallPartCounter = 1;
    } else {
        $iApiCallPartCounter++;
    }
}

# split API call
for($iPartCounter = 1; $iPartCounter <= $iPartsCounter; $iPartCounter++) {

    # set emailaddress data
    $aReceiversData = array();
    
    # set template for customer data
    foreach($aApiCallParts[$iPartCounter] as $oCustomer) {
        $iInstanceCounter++;
        # collect data from user
        $aReceiversData[] = array(
            'emailaddress'          => $oCustomer->email,
            'bounceRuleId'          => '',
            'bounceRuleTitle'       => '',
            'bounceRuleDescription' => ''
         );
    }
    
    # make JSON template and give command to sent mail
    $aData = 
    array(
        'action' => 'checkbounces',
        'token' => substr(hash('sha512', '22-bridgeBuilder!2015'), 0, 50), // hash from customerId + '-' sign + prefix from mailserver
        'customerId' => COMMUNICATION_SENDBULK_CUSTOMER_ID,
        'customerName' => CLIENT_NAME,
        'emailaddresses' => $aReceiversData
    );


    $sJsonData = json_encode($aData);
    if($sJsonData === false) {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' has not sent JSON content while checking bounces', CLIENT_NAME . ' has not sent JSON content while checking bounces');
    }

    # set cUrl settings
    $ch = curl_init(COMMUNICATION_SENDBULK_EMAIL_URL);                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $sJsonData);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',
        'Content-Length: ' . mb_strlen($sJsonData)
        )
    );

    # execute cUrl call
    $result = curl_exec($ch);
    
    # error send wrong/incomplete token
    if($result == 1) {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-checkbounces : Wrong token customer', 'bulk-checkbounces : Wrong token customer');
    }
    # error offline customer
    elseif($result == 2) {
        MailManager::sendMail(DEFAULT_ERROR_EMAIL, CLIENT_NAME . ' - bulk-checkbounces : Offline customer', 'bulk-checkbounces : Offline customer');
    } else {
        
        # check for result and decode JSON data
        if(!empty($result)) {
            
            # decode data into array
            $aResults = json_decode($result, true);
            
            # loop through result
            if(is_array($aResults)) {
                foreach($aResults as $aResult) {
                    # when bouncerule is set, update customers profile
                    if(is_numeric($aResult['bounceRuleId']) && !empty($aResult['emailaddress']) && !empty($aResult['bounceRuleTitle']) && !empty($aResult['bounceRuleDescription']) && !empty($aResult['communicationItemId'])) {

                        # update customer record
                        CustomerManager::saveCustomerBounceRuleDescriptionByCustomerEmail($aResult['emailaddress'], $aResult['bounceRuleId'], $aResult['bounceRuleTitle'], $aResult['bounceRuleDescription']);

                        # update bounce amount logging
                        CommunicationItemManager::setCustomerOnCommunicationBouncelist($aResult['emailaddress'], $aResult['bounceRuleId'], $aResult['bounceRuleTitle'], $aResult['bounceRuleDescription'], $aResult['communicationItemId']);

                    }

                }
            }
        }
        
    }

}

?>