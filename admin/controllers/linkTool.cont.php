<?php

# check if controller is required by index.php
if (!defined('ACCESS'))
    die;

$oPageLayout = new PageLayout();
$oPageLayout->sWindowTitle = sysTranslations::get('link_tool');
$oPageLayout->sModuleName = sysTranslations::get('link_tool');

# get status update from session
$oPageLayout->sStatusUpdate = http_session("statusUpdate");
unset($_SESSION['statusUpdate']); //remove statusupdate, always show once
# handle linkFilter
$aLinkFilter = http_session('linkFilter');
if (http_post('filterForm')) {
    $aLinkFilter = http_post('linkFilter');
    $_SESSION['linkFilter'] = $aLinkFilter;
}

if (http_post('resetFilter') || empty($aLinkFilter)) {
    unset($_SESSION['linkFilter']);
    $aLinkFilter = array();
    $aLinkFilter['q'] = '';
    $aLinkFilter['linkCategoryId'] = '';
    $aLinkFilter['linkSubCategoryId'] = '';
}

# handle perPage
if (http_post('setPerPage')) {
    $_SESSION['linksPerPage'] = http_post('perPage');
}

#handle add/edit
if (http_get("param1") == 'bewerken' || http_get("param1") == 'toevoegen') {
    # set crop referrer for this module

    if (http_get("param1") == 'bewerken' && is_numeric(http_get("param2"))) {
        $oCustomerLink = CustomerLinkManager::getCustomerLinkById(http_get("param2"));
        if (empty($oCustomerLink))
            http_redirect(ADMIN_FOLDER . "/");
    } else {
        $oCustomerLink = new CustomerLink();
    }


    
    $aCategories = $oCustomerLink->getCategories();
    $iMainCategoryId = !empty($aCategories) ? $aCategories[0]->customerLinkCategoryId : '';
    $iSubCategoryId = (!empty($aCategories) && count($aCategories) > 1) ? $aCategories[1]->customerLinkCategoryId  : '';
    $oParentCategory = http_post('linkCategoryId') ? CustomerLinkCategoryManager::getCustomerLinkCategoryById(http_post('linkCategoryId')) : (!empty($aCategories) ? $aCategories[0] : null);    
    # action = save
    if (http_post("action") == 'save') {
        # load data in object
        $oCustomerLink->_load($_POST);
        $aCategoryIds = array();

        if (!empty($oParentCategory)) {

            if ($oParentCategory->getSubCategories() != null && http_post('linkSubCategoryId')) {
                $aCategoryIds[] = http_post('linkCategoryId');
                $aCategoryIds[] = http_post('linkSubCategoryId');
            } elseif ($oParentCategory->getSubCategories() == null) {
                $aCategoryIds[] = http_post('linkCategoryId');
            }
        }

        $iMainCategoryId = http_post('linkCategoryId');
        $iSubCategoryId = http_post('linkSubCategoryId');
        $oCustomerLink->setCategories($aCategoryIds);

        # if object is valid, save
        if ($oCustomerLink->isValid()) {
            CustomerLinkManager::saveCustomerLink($oCustomerLink); //save object
            //$_SESSION['statusUpdate'] = 'Link is opgeslagen'; //save status update into session
            $oPageLayout->sStatusUpdate = 'Link is opgeslagen';
            //http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomerLink->CustomerLinkId);
        } else {
            Debug::logError("", "Link tool module php validate error", __FILE__, __LINE__, "Tried to save a Link with wrong values despite javascript check.<br />" . _d($_POST, 1, 1), Debug::LOG_IN_EMAIL);
            //$_SESSION['statusUpdate'] = 'Link is niet opgeslagen, niet alle velden zijn (juist) ingevuld';
            $oPageLayout->sStatusUpdate = 'Link is niet opgeslagen, niet alle velden zijn (juist) ingevuld';
        }
    }

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/linkTool/linkTool_form.inc.php';
}
# delete object
elseif (http_get("param1") == 'verwijderen' && is_numeric(http_get("param2"))) {
    if (is_numeric(http_get("param2")))
        $oCustomerLink = CustomerLinkManager::getCustomerLinkById(http_get("param2"));

    if (!empty($oCustomerLink) && CustomerLinkManager::deleteCustomerLink($oCustomerLink)) {
        $_SESSION['statusUpdate'] = 'Link is verwijderd'; //save status update into session
    } else {
        $_SESSION['statusUpdate'] = 'Link kan niet worden verwijderd'; //save status update into session
    }
    http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
}
# set object online/offline
elseif (http_get("param1") == 'ajax-setOnline') {
    $bOnline = http_get("online", 0); //no value, set offline by default
    $bAjax = http_get("ajax", false); //controller requested by ajax
    $iCustomerLinkId = http_get("param2");
    $oResObj = new stdClass(); //standard class for json feedback
    # update online for object
    if (is_numeric($iCustomerLinkId)) {
        $oCustomerLink = CustomerLinkManager::getCustomerLinkById($iCustomerLinkId);
        if (!empty($oCustomerLink)) {
            $oResObj->success = $oCustomerLink->setOnline($bOnline ? true : false);
            $oResObj->customerLinkId = $iCustomerLinkId;
            $oResObj->online = $bOnline;
        }
    }

    # redirect to overview page if this isn't AJAX
    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '');
    }

    die(json_encode($oResObj));
}
# get the link subcategories
elseif (http_post("action") == 'getSubCategories') {
    $bAjax = http_post("ajax", false); //controller requested by ajax
    $iCategoryId = http_post("linkCategoryId");
    $oResObj = new stdClass(); //standard class for json feedback
    $oResObj->success = false;

    if (is_numeric($iCategoryId)) {
        $oCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById($iCategoryId);

        if (!empty($oCategory)) {
            $oResObj->success = true;
            $oResObj->subCategories = $oCategory->getSubCategories();
        }
    }

    # redirect to overview page if this isn't AJAX
    if (!$bAjax) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller') . '');
    }

    die(json_encode($oResObj));
}
# display overview
else {
    $iPerPage = http_session('linksPerPage', 10);
    $iCurrPage = http_get('page', 1);
    $iStart = (($iCurrPage - 1) * $iPerPage);
    if (!is_numeric($iCurrPage) || $iCurrPage <= 0) {
        http_redirect(ADMIN_FOLDER . '/' . http_get('controller'));
    }

    $aLinkFilter['showAll'] = 1;
    $iFoundRows = 0;

    if (!empty($aLinkFilter['linkCategoryId'])) {
        $oParentCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById($aLinkFilter['linkCategoryId']);
    }

    $aCustomerLinks = CustomerLinkManager::getCustomerLinksByFilter($aLinkFilter, $iPerPage, $iStart, $iFoundRows, array('created' => 'DESC'));

//    # get links of the subcategories
//    if (is_numeric($aLinkFilter['linkCategoryId'])) {
//        $oParentLinkCategory = CustomerLinkCategoryManager::getCustomerLinkCategoryById($aLinkFilter['linkCategoryId']);
//        
//        if (!empty($oParentLinkCategory)) {
//           
//            foreach ($oParentLinkCategory->getSubCategories() as $oSubCategory) {
//                
//                $aLinks = $oSubCategory->getCustomerLinks();
//                
//                if (count($aLinks) > 0) {
//                    $aCustomerLinks = array_merge($aCustomerLinks, $aLinks);
//                }
//            }
//        }
//    }


    $iPageCount = !empty($iPerPage) ? (ceil($iFoundRows / $iPerPage)) : 0;

    $oPageLayout->sPagePath = ADMIN_PAGES_FOLDER . '/linkTool/linkTool_overview.inc.php';
}

# include template
include_once ADMIN_TEMPLATES_FOLDER . '/default.tmpl.php';
?>