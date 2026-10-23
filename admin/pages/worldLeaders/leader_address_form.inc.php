<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/bewerken/<?= $oWorldLeaderAddress->worldLeaderId ?>"><?=  sysTranslations::get('global_go_back') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <fieldset>
                <legend><?= sysTranslations::get('global_address') ?></legend>
                <table class="withForm">
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_description') ?> * </label></td>
                        <td><input class="{validate:{required:true}}" size="50" id="name" type="text" name="name" value="<?= _e($oWorldLeaderAddress->name) ?>" /></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("name") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="regionId"><?= sysTranslations::get('global_region') ?> * </label></td>
                        <td><select class="{validate:{required:true}}" id="regionId" name="regionId">
                                <option value="">-- <?= sysTranslations::get('global_region_tooltip')?>-- </option>
                                <? foreach (CountryManager::getRegions() as $oRegion) {?>
                                <option value="<?= $oRegion->regionId ?>" <?= $oRegion->regionId == $oWorldLeaderAddress->regionId ? 'selected' : '' ?>><?= $oRegion->name ?></option>
                                <? } ?>
                            </select></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("regionId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>                       
                    <tr>
                        <td class="withLabel"><label for="countryId"><?= ucfirst(sysTranslations::get('customer_region_country')) ?></label> *</td>
                             <td><select class="{validate:{required:true}}" id="countryId" name="countryId">
                                <option value="">-- <?= sysTranslations::get('customer_choose_country')?>-- </option>
                                <? foreach ($aCountries as $oCountry) {?>
                                <option value="<?= $oCountry->countryId ?>" <?= $oCountry->countryId == $oWorldLeaderAddress->countryId ? 'selected' : '' ?>><?= $oCountry->name ?></option>
                                <? } ?>
                            </select></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("countryId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>                     
                    <tr>
                        <td class="withLabel"><label for="address"><?= sysTranslations::get('customer_company_address') ?> * </label></td>
                        <td><input class="{validate:{required:true}}"  size="50" id="address" type="text" name="address" value="<?= _e($oWorldLeaderAddress->address) ?>" /></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("address") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="postalCode"><?= sysTranslations::get('customer_company_postal_code') ?> * </label></td>
                        <td colspan="2"><input size="50" class="{validate:{required:true}}" id="postalCode" type="text" name="postalCode" value="<?= _e($oWorldLeaderAddress->postalCode) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="city"><?= sysTranslations::get('customer_company_city') ?> * </label></td>
                        <td><input class="{validate:{required:true}}" size="50" id="city" type="text" name="city" value="<?= _e($oWorldLeaderAddress->city) ?>" /></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("city") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr> 
                    <tr>
                        <td class="withLabel"><label for="addressCountryId"><?= ucfirst(sysTranslations::get('customer_country')) ?></label> *</td>
                             <td><select class="{validate:{required:true}}" id="addressCountryId" name="addressCountryId">
                                <option value="">-- <?= sysTranslations::get('customer_choose_country')?>-- </option>
                                <? foreach (CountryManager::getCountries() as $oCountry) {?>
                                <option value="<?= $oCountry->countryId ?>" <?= $oCountry->countryId == $oWorldLeaderAddress->addressCountryId ? 'selected' : '' ?>><?= $oCountry->name ?></option>
                                <? } ?>
                            </select></td>
                        <td><span class="error"><?= $oWorldLeaderAddress->isPropValid("addressCountryId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
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
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/bewerken/<?= $oWorldLeaderAddress->worldLeaderId ?>"><?=  sysTranslations::get('global_go_back') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?
$sUrl = getCurrentUrl();
$sTooltip = sysTranslations::get('customer_choose_country');
$sJavascript = <<<EOT
<script>
$('#regionId').change(
    function () {            
                
        if ($(this).val() != '') {
            $.ajax({
                type: "POST",
                url: "$sUrl",
                data: 'action=ajax-getRegionCountries&regionId=' + $(this).val(),
                async: true,
                success: function(data){
                    var dataObj = eval('(' + data + ')');
                    var options = '<option value="">-- $sTooltip --</option>';
                    /* On success */
                    if (dataObj.countries != null){
                        $.each(
                            dataObj.countries, 
                            function (index, country) {
                                options += '<option value="' + country.countryId + '">' + country.name + '</option>';
                            }
                        );
                    } 
                    $('#countryId').html(options);
                }            
            });
        }
        else {
            var options = '<option value="">-- $sTooltip --</option>';
            $('#countryId').html(options);        
        }
       
    }
);
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>