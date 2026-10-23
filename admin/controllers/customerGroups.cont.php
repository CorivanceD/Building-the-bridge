<?php

# check if controller is required by index.php 
if (!defined('ACCESS'))
    die;

# set page layout properties
$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('customer_groups');
$oPageLayout->sModuleName = sysTranslations::get('customer_groups');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once

# handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {
    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oCustomerGroup = CustomerGroupManager::getCustomerGroupById(http_get("param2"));
        if (empty($oCustomerGroup))
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oCustomerGroup = new CustomerGroup();
    }

    # action = save
    if (http_post("action") == 'save') {

        # load data in object
        $oCustomerGroup->_load($_POST);
        
        # if object is valid, save
        if ($oCustomerGroup->isValid()) {
            CustomerGroupManager::saveCustomerGroup($oCustomerGroup); //save object
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_group_saved'); //save status update into session
            http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomerGroup->customerGroupId);
        } else {
            Debug::logError("", "Customer group module php validate error", __FILE__, __LINE__, "Tried to save Customer group with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_group_not_saved');
        }
    }

    $aClients = $oCustomerGroup->getClients(true);
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customers/customerGroup_form.inc.php';
}

# delete object
elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2"))) {
        $oCustomerGroup = CustomerGroupManager::getCustomerGroupById(http_get("param2"));
    }
    
    # trigger error
    if(empty($oCustomerGroup)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('customer_group_not_deleted'); //save status update into session
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }
    
    # get linked customers
    $aLinkedCustomers = $oCustomerGroup->getClients();
    if(!empty($aLinkedCustomers)) {
        # delete linked customers
        foreach($aLinkedCustomers as $oGroupCustomer) {
            CustomerGroupManager::deleteCustomerFromCustomerGroup($oCustomerGroup->customerGroupId, $oGroupCustomer->customerId);
        }
    }

    if(!empty($oCustomerGroup) && CustomerGroupManager::deleteCustomerGroup($oCustomerGroup)) {
        $_SESSION['statusUpdate'] = sysTranslations::get('customer_group_deleted'); //save status update into session
    } else {
        $_SESSION['statusUpdate'] = sysTranslations::get('customer_group_not_deleted'); //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}

elseif (http_get("param1") == 'klanten-toevoegen' && is_numeric(http_get("param2"))) {

    # action = save
    if (http_post("action") == 'addMembersToCustomerGroup') {
        $aCustomerIds = http_post('aCustomerIds', array());
        foreach($aCustomerIds as $iCustomerId) {
            CustomerManager::saveCustomerGroupRelation($iCustomerId, http_get('param2'));
        }
        if(!empty($aCustomerIds)) {
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_added'); //save status update into session
        } else {
            $_SESSION['statusUpdate'] = sysTranslations::get('customer_not_added'); //save status update into session
        }
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') .  '/' . http_get('param1') .  '/' . http_get('param2'));
        
    }
    
    # get all customer instances
    $aCustomers = CustomerManager::getAllCustomers();
    
    # get unique customerid's of all customers
    $aCustomersIds = array();
    foreach($aCustomers as $oCustomers) {
        $aCustomersIds[] = $oCustomers->customerId;
    }
    
    # get current group
    $oCustomerGroup = CustomerGroupManager::getCustomerGroupById(http_get("param2"));
    
    # get clients of group
    $aCustomersOfGroup = $oCustomerGroup->getClients();
    
    # get unique customerid's of all customers
    $aCustomerIdsOfGroup = array();
    foreach($aCustomersOfGroup as $oCustomerOfGroup) {
        $aCustomerIdsOfGroup[] = $oCustomerOfGroup->customerId;
    }
    
    # collect objects of customers not on this list
    $aCustomersNotInThisGroup = array();
    
    # compare customer id's to check the differences
    $aCustomerIdsNotInThisGroup = array_diff($aCustomersIds, $aCustomerIdsOfGroup);
    foreach($aCustomerIdsNotInThisGroup as $iCustomerIdNotInThisGroup) {
        $oCustomer = CustomerManager::getCustomerById($iCustomerIdNotInThisGroup);
        $aCustomersNotInThisGroup[] = $oCustomer;
    }
    
    # load view
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customers/customersGroup_addMembers.inc.php';
}

# display overview
else {
    $aCustomerGroups = CustomerGroupManager::getAllCustomerGroups();
    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/customers/customersGroup_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>