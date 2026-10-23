<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?=  sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <input type="hidden" value="<?= $oCustomer->didSignOffForEmail ?>" name="didSignOffForEmail" />
            <fieldset>
                <legend><?= sysTranslations::get('customer_client') ?></legend>
                <table class="withForm">
                    <tr>
                        <td><?= sysTranslations::get('global_online') ?> *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_set_online') ?>" type="radio" <?= $oCustomer->online ? 'CHECKED' : '' ?> id="online_1" name="online" value="1" /> <label for="online_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_set_online') ?>" type="radio" <?= !$oCustomer->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                        <td><span class="error"><?= $oCustomer->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel" style="width: 120px;"><label for="email"><?= sysTranslations::get('global_email') ?> *</label></td>
                        <td><input size="50" id="email" class="autofocus {validate:{required:true,email:true,remote:'<?= ADMIN_FOLDER . '/' . http_get('controller') ?>?ajax=checkEmail<?= $oCustomer->customerId === null ? '' : '&customerId=' . $oCustomer->customerId ?>',messages:{required:'<?= sysTranslations::get('global_email_required_tooltip') ?>',email:'<?= sysTranslations::get('global_valid_email_tooltip') ?>',remote:'<?= sysTranslations::get('global_email_required_tooltip') ?>'}}}" title="<?= sysTranslations::get('global_enter_email_tooltip') ?>" type="text" autocomplete="off" name="email" value="<?= _e($oCustomer->email) ?>" /></td>
                        <?
                        $sEmailPropError = '';
                        if (!$oCustomer->isPropValid("email"))
                            $sEmailPropError .= ($sEmailPropError == '' ? '' : ', ') . sysTranslations::get('global_field_not_completed');
                        if (!$oCustomer->isPropValid("emailExists"))
                            $sEmailPropError .= ($sEmailPropError == '' ? '' : ', ') . sysTranslations::get('global_email_in_use_2');
                        ?>
                        <td><span class="error"><?= $sEmailPropError ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="password"><?= sysTranslations::get('global_new_password') ?> **</label></td>
                        <td><input id="password" class="<?= $oCustomer->customerId === null ? '{validate:{required:true,password:true}}' : '{validate:{password:true}}' ?>" title="<?= sysTranslations::get('user_secure_password_tooltip') ?>" autocomplete="off" type="text" name="password" value="" /></td>
                        <td><em>&nbsp;(** <?= sysTranslations::get('user_password_empty') ?>)</em> <span class="error"><?= $oCustomer->isPropValid("password") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyName"><?= sysTranslations::get('global_company_name') ?></label></td>
                        <td colspan="2"><input size="50" id="companyName" type="text" name="companyName" value="<?= _e($oCustomer->companyName) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyAddress"><?= sysTranslations::get('customer_company_address') ?></label></td>
                        <td colspan="2"><input size="50" id="companyAddress" type="text" name="companyAddress" value="<?= _e($oCustomer->companyAddress) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyPostalCode"><?= sysTranslations::get('customer_company_postal_code') ?></label></td>
                        <td colspan="2"><input size="50" id="companyPostalCode" type="text" name="companyPostalCode" value="<?= _e($oCustomer->companyPostalCode) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyCity"><?= sysTranslations::get('customer_company_city') ?></label></td>
                        <td colspan="2"><input size="50" id="companyCity" type="text" name="companyCity" value="<?= _e($oCustomer->companyCity) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyEmail"><?= sysTranslations::get('customer_company_email') ?></label></td>
                        <td colspan="2"><input size="50" id="companyEmail" class="{validate:{required:false,email:true,messages:{required:'<?= sysTranslations::get('global_email_required_tooltip') ?>',email:'<?= sysTranslations::get('global_valid_email_tooltip') ?>'}}}" type="text" name="companyEmail" value="<?= _e($oCustomer->companyEmail) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyPhone"><?= sysTranslations::get('customer_company_phone') ?></label></td>
                        <td colspan="2"><input size="50" id="companyPhone" type="text" name="companyPhone" value="<?= _e($oCustomer->companyPhone) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="companyWebsite"><?= sysTranslations::get('customer_company_website') ?></label></td>
                        <td colspan="2"><input size="50" id="companyWebsite" type="text" name="companyWebsite" value="<?= _e($oCustomer->companyWebsite) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_name') ?> *</label></td>
                        <td><input size="50" id="name" class="{validate:{required:true}}" title="<?= sysTranslations::get('global_first_name_tooltip') ?>" type="text" name="name" value="<?= _e($oCustomer->name) ?>" /></td>
                        <td><span class="error"><?= $oCustomer->isPropValid("name") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td><?= sysTranslations::get('customer_gender') ?> *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_choose_gender') ?>" type="radio" <?= $oCustomer->gender == 'M' ? 'CHECKED' : '' ?> id="gender_M" name="gender" value="M" /> <label for="gender_M"><?= sysTranslations::get('customer_gender_male') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_choose_gender') ?>" type="radio" <?= $oCustomer->gender == 'F' ? 'CHECKED' : '' ?> id="gender_F" name="gender" value="F" /> <label for="gender_F"><?= sysTranslations::get('customer_gender_female') ?></label>
                        </td>
                        <td><span class="error"><?= $oCustomer->isPropValid("gender") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>  
                    <tr>
                        <td class="withLabel"><label for="age"><?= sysTranslations::get('customer_age') ?> *</label></td>
                        <td>
                            <select id="age" name="age" class="{validate:{required:true}}" >
                                <option value="">-- <?= sysTranslations::get('customer_choose_age') ?> --</option>
                                <option value="<?= Customer::AGE_UNDER_12 ?>" <?= intval($oCustomer->age) === Customer::AGE_UNDER_12 ? 'selected' : '' ?>>< 12</option>
                                <option value="<?= Customer::AGE_12_17 ?>" <?= intval($oCustomer->age) === Customer::AGE_12_17 ? 'selected' : '' ?>>12-17</option>
                                <option value="<?= Customer::AGE_18_24 ?>" <?= intval($oCustomer->age) === Customer::AGE_18_24 ? 'selected' : '' ?>>18-24</option>
                                <option value="<?= Customer::AGE_25_34 ?>" <?= intval($oCustomer->age) === Customer::AGE_25_34 ? 'selected' : '' ?>>25-34</option>
                                <option value="<?= Customer::AGE_35_44 ?>" <?= intval($oCustomer->age) === Customer::AGE_35_44 ? 'selected' : '' ?>>35-44</option>
                                <option value="<?= Customer::AGE_45_54 ?>" <?= intval($oCustomer->age) === Customer::AGE_45_54 ? 'selected' : '' ?>>45-54</option>
                                <option value="<?= Customer::AGE_55_64 ?>" <?= intval($oCustomer->age) === Customer::AGE_55_64 ? 'selected' : '' ?>>55-64</option>
                                <option value="<?= Customer::AGE_65_74 ?>" <?= intval($oCustomer->age) === Customer::AGE_65_74 ? 'selected' : '' ?>>65-74</option>
                                <option value="<?= Customer::AGE_75_OLDER ?>" <?= intval($oCustomer->age) === Customer::AGE_75_OLDER ? 'selected' : '' ?>>> 75</option>
                            </select>
                        </td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="countryId"><?= sysTranslations::get('global_country') ?> *</label></td>  
                        <td colspan="2"><select name="countryId">
                                <option value="">-- <?= sysTranslations::get('customer_choose_country') ?> --</option>
                                <?
                                foreach (CountryManager::getCountries() AS $oCountry) {
                                    echo '<option ' . ($oCustomer->countryId === $oCountry->countryId ? 'selected' : '') . ' value="' . $oCountry->countryId . '">' . $oCountry->name . '</option>';
                                }
                                ?>
                            </select></td>     
                        <td><span class="error"><?= $oCustomer->isPropValid("countryId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="city"><?= sysTranslations::get('global_city') ?> *</label></td>
                        <td><input size="50" id="city" class="{validate:{required:true}}" title="<?= sysTranslations::get('global_city_tooltip') ?>" type="text" name="city" value="<?= _e($oCustomer->city) ?>" /></td>
                        <td><span class="error"><?= $oCustomer->isPropValid("city") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="mobilePhone"><?= sysTranslations::get('global_mobile_number') ?></label></td>
                        <td colspan="2"><input size="50" id="mobilePhone" type="text" name="mobilePhone" value="<?= _e($oCustomer->mobilePhone) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="phone"><?= sysTranslations::get('global_phone') ?></label></td>
                        <td colspan="2"><input size="50" id="phone" type="text" name="phone" value="<?= _e($oCustomer->phone) ?>" /></td>
                    </tr>

                    <tr>
                        <td colspan="3">
                            <hr>
                            <legend style="margin-left: -10px;"><?= sysTranslations::get('global_contact_settings') ?></legend>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <?= sysTranslations::get('customer_signed_group') ?>
                            <br>
                            <? foreach(CustomerGroupManager::getAllCustomerGroups() as $oCustomerGroup) { ?>
                                <label for="customerGroup<?= $oCustomerGroup->customerGroupId ?>">
                                    <input type="checkbox" name="customerGroupIds[]" id="customerGroup<?= $oCustomerGroup->customerGroupId ?>" value="<?= $oCustomerGroup->customerGroupId ?>" <?= $oCustomer->isLinkedToCustomerGroup($oCustomerGroup->customerGroupId) ? 'checked="checked"' : '' ?>><?= _e($oCustomerGroup->title) ?>
                                </label>
                                <br>
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= sysTranslations::get('customer_contact_sms') ?> *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_contact_sms_tooltip') ?>" type="radio" <?= $oCustomer->contactBySms ? 'CHECKED' : '' ?> id="contactBySms_1" name="contactBySms" value="1" /> <label for="contactBySms_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_contact_sms_tooltip') ?>" type="radio" <?= !$oCustomer->contactBySms ? 'CHECKED' : '' ?> id="contactBySms_0" name="contactBySms" value="0" /> <label for="contactBySms_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                        <td><span class="error"><?= $oCustomer->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td><?= sysTranslations::get('customer_contact_email') ?> *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_contact_email_tooltip') ?>" type="radio" <?= $oCustomer->contactByEmail ? 'CHECKED' : '' ?> id="contactByEmail_1" name="contactByEmail" value="1" /> <label for="contactByEmail_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_contact_email_tooltip') ?>" type="radio" <?= !$oCustomer->contactByEmail ? 'CHECKED' : '' ?> id="contactByEmail_0" name="contactByEmail" value="0" /> <label for="contactByEmail_0"><?= sysTranslations::get('global_no') ?></label>
                            <? if($oCustomer->didSignOffForEmail) { ?>
                            <span class="importantMessage"><?= sysTranslations::get('customer_client_logoff') ?></span>
                            <? } ?>
                            <? if(is_numeric($oCustomer->bounceRuleId) && !empty($oCustomer->bounceRuleTitle) && !empty($oCustomer->bounceRuleDescription)) { ?>
                            <span class="importantMessage">(De klant staat op de bouncelijst vanwege de reden "<?= _e($oCustomer->bounceRuleTitle) ?>" en omschrijving "<?= _e($oCustomer->bounceRuleDescription) ?>)"</span>
                            <? } ?>
                        </td>
                        <td><span class="error"><?= $oCustomer->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td><?= sysTranslations::get('customer_receive_verification') ?> *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_receive_verification_tooltip') ?>" type="radio" <?= $oCustomer->receiveCommunicationVerification ? 'CHECKED' : '' ?> id="receiveCommunicationVerification_1" name="receiveCommunicationVerification" value="1" /> <label for="receiveCommunicationVerification_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('customer_receive_verification_tooltip') ?>" type="radio" <?= !$oCustomer->receiveCommunicationVerification ? 'CHECKED' : '' ?> id="receiveCommunicationVerification_0" name="receiveCommunicationVerification" value="0" /> <label for="receiveCommunicationVerification_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_images') ?></legend>
                <?
                if ($oCustomer->customerId !== null) {
                    $oImageManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_images_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>    
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?=  sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?
# create necessary javascript
$sBottomJavascript = <<<EOT
<script type="text/javascript">
    // randompassword to password field
    $("#password").randomPass();
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>