<?php

# get values from URL
$iCustomerId = http_get('customerid');
$iCommunicationItemId = http_get('communicationItemId');
$sToken = http_get('token');

# check whether all required fields are passed
if(is_numeric($iCustomerId) && !empty($sToken)) {
    
    # get customer
    $oCustomer = CustomerManager::getCustomerById($iCustomerId);

    if (!empty($oCustomer)) {
        # create hash to verify user
        $sVerifyToken = hashPasswordForDb($oCustomer->customerId.'hash'.$oCustomer->email);
        if($sVerifyToken == $sToken) {
            # check setting before updating
            if($oCustomer->contactByEmail == 1 && $oCustomer->didSignOffForEmail == 0) {
                # mark customer preference for not getting e-mail
                CustomerManager::updateEmailPreferenceByCustomerId($oCustomer->customerId);

                # get communicationItem and log customer into blacklist
                if(is_numeric($iCommunicationItemId)) {
                    CommunicationItemManager::setCustomerOnCommunicationBlacklist($oCustomer, $iCommunicationItemId);
                }
                $iMessageNumber = 1;
            } else {
                $iMessageNumber = 2;
            }
        } else {
            $iMessageNumber = 3;
        }
    } else {
        $iMessageNumber = 4;
    }
    
} else {
    $iMessageNumber = 4;
}

include_once TEMPLATES_FOLDER . '/afmelden.tmpl.php';

?>