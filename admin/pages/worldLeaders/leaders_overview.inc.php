<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px; width: 49%">
        <legend><?= sysTranslations::get('global_filter') ?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_name') ?></td>
                <td><input type="text" name="leaderFilter[name]" value="<?= _e($aLeaderFilter['name']) ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel"><label for="leaderFilter[regionId]"><?= ucfirst(sysTranslations::get('global_region')) ?></label></td>
                <td>
                    <select id="filterRegionId" name="leaderFilter[regionId]"  title="">
                        <option value="">-- <?= sysTranslations::get('global_region_tooltip') ?>--</option>
                        <?php foreach (CountryManager::getRegions() AS $oRegion) { ?>
                            <option value="<?= $oRegion->regionId ?>" <?= $oRegion->regionId == $aLeaderFilter['regionId'] ? 'selected' : '' ?>><?= $oRegion->name ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>              
            <tr>
                <td class="withLabel"><label for="leaderFilter[countryId]"><?= ucfirst(sysTranslations::get('customer_country')) ?></label></td>
                <td>
                    <select id="filterCountryId" name="leaderFilter[countryId]"  title="<?= sysTranslations::get('customer_choose_country') ?>">
                        <option value="">-- <?= sysTranslations::get('customer_choose_country') ?> --</option>
                        <?php
                        if (!empty($aCountries)) {
                            foreach ($aCountries AS $oCountry) {
                                ?>
                                <option value="<?= $oCountry->countryId ?>" <?= $oCountry->countryId === $aLeaderFilter['countryId'] ? 'selected' : '' ?> ><?= $oCountry->name ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>             
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterLeaders" value="<?= sysTranslations::get('leaders_filter') ?>" /> 
                    <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" />
                </td>
            </tr>
        </table>
    </fieldset>
</form>
<div class="float-left">
    <fieldset style="margin-bottom: 20px; width: 49%">
        <legend>Import</legend>
        <form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST" enctype="multipart/form-data" id="importFileForm" name="importFileForm">
            <br>
            <label for="regionId"><?= sysTranslations::get('global_region') ?>  *</label>
            <select class="{validate:{required:true}}" id="regionId" name="regionId">
                <option value="">-- <?= sysTranslations::get('global_region_tooltip') ?>--</option>
                <?php foreach (CountryManager::getRegions() as $oRegion) { ?>
                    <option value="<?= $oRegion->regionId ?>" <?= $oRegion->regionId == http_post('regionId') ? 'selected' : '' ?>><?= $oRegion->name ?></option>
                <?php } ?>
            </select>            
            <br><br> 
            <input type="hidden" name="action" value="import" />            
            <label for="importFile"><b>File (.xlsx)</b></label>&nbsp;&nbsp;<input type="file" name="importFile" id="importFile" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
            <br><br><input type="submit" value="Import" name="import" id="import"/><br><br>
        </form>
        <?php if (!empty($aErrorLog)) { ?>
            <br/>    
            <em>
                <b>Errors</b><br/>
                <?php foreach ($aErrorLog as $sLog) { ?>
                    <?= $sLog ?><br/>
                <?php }
                ?> 
            </em>
            <br/>
            <?php
        }
        ?>        
    </fieldset>

</div> 
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="2">
                <h2><?= sysTranslations::get('leaders_all') ?></h2>
                <div class="right" style="text-align:right">                   
                    <a class="addBtn textLeft"  href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('leader_add') ?>" alt="<?= sysTranslations::get('leader_add') ?>"><?= sysTranslations::get('leader_add') ?></a><br>                    
                </div>
            </td>
        </tr>
        <tr>
            <th ><?= sysTranslations::get('global_name') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aLeaders AS $oLeader) {
            echo '<tr>';
            echo '<td>' . _e($oLeader->getCompleteName()) . '</td>';
            echo '<td>';
            echo '<a title="Leader edition" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oLeader->worldLeaderId . '"></a>';
            # delete button
            if ($oLeader->isDeletable())
                echo '<a onclick="return confirmChoice(\'leader ' . $oLeader->name . '\');" class="action_icon delete_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oLeader->worldLeaderId . '"></a>';
            else
                echo '<a class="action_icon delete_icon grey" href="#" title="Verwijder leider"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aLeaders) == 0) {
            echo '<tr><td colspan="2"><i>' . sysTranslations::get('leaders_no_leaders') . '</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="2">
                <form method="POST">
                    <?= generatePaginationHTML($iPageCount, $iCurrPage) ?>
                    <input type="hidden" name="setPerPage" value="1" />
                    <select name="perPage" onchange="$(this).closest('form').submit();">
                        <option value=""><?= sysTranslations::get('global_all') ?></option>
                        <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
                        <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
                        <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
                        <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
                    </select> <?= sysTranslations::get('global_per_page') ?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>
<?php
$sUrl = getCurrentUrl();
$sTooltip = sysTranslations::get('customer_choose_country');
$sJavascript = <<<EOT
<script>
$('#filterRegionId').change(
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
                    $('#filterCountryId').html(options);
                }            
            });
        }
        else {
            var options = '<option value="">-- $sTooltip --</option>';
            $('#filterCountryId').html(options);        
        }
       
    }
);
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>