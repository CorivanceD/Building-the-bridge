<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <fieldset>
                <legend><?= sysTranslations::get('global_leader') ?></legend>
                <table class="withForm">
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_prefix') ?></label></td>
                        <td coslpan="2"><input size="50" id="prefix" title="" type="text" name="prefix" value="<?= _e($oWorldLeader->prefix) ?>" /></td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_name') ?> *</label></td>
                        <td><input size="50" id="name" class="{validate:{required:true}}" title="" type="text" name="name" value="<?= _e($oWorldLeader->name) ?>" /></td>
                        <td><span class="error"><?= $oWorldLeader->isPropValid("name") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="position"><?= sysTranslations::get('leader_position') ?></label></td>
                        <td colspan="2"><input size="50" id="position" title="" type="text" name="position" value="<?= _e($oWorldLeader->position) ?>" /></td>
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
    <?php if (!empty($oWorldLeader->worldLeaderId)) { ?>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_address') ?></legend>        
            <table class="sorted">
                <thead>
                    <tr class="topRow">
                        <td colspan="4">
 
                            <div class="right" style="text-align:right">
                                <a class="addBtn textLeft"  href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/adres-toevoegen/<?= $oWorldLeader->worldLeaderId ?>" title="<?= sysTranslations::get('leader_address_add') ?>" alt="<?= sysTranslations::get('leader_address_add') ?>"><?= sysTranslations::get('leader_address_add') ?></a><br>                    
                            </div>
                        </td>
                    </tr>                    
                    <tr>
                        <th ><?= sysTranslations::get('global_region') ?></th>
                        <th ><?= sysTranslations::get('customer_country') ?></th>
                        <th ><?= sysTranslations::get('global_address') ?></th>                        
                        <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($oWorldLeader->getAddresses() AS $oAddress) {
                        echo '<tr>';
                        echo '<td>' . _e($oAddress->getRegion()->name) . '</td>';
                        echo '<td>' . _e($oAddress->getCountry()->name) . '</td>';
                        echo '<td>' . _e($oAddress->name) . '</td>';
                        echo '<td>';
                        echo '<a title="Address edition" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/adres-bewerken/' . $oAddress->worldLeaderAddressId . '"></a>';
                        # delete button
                        if ($oAddress->isDeletable())
                            echo '<a onclick="return confirmChoice(\'adres ' . $oAddress->name . '\');" class="action_icon delete_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/adres-verwijderen/' . $oAddress->worldLeaderAddressId . '"></a>';
                        else
                            echo '<a class="action_icon delete_icon grey" href="#" title="Verwijder adres"></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    if (count($oWorldLeader->getAddresses()) == 0) {
                        echo '<tr><td colspan="4"><i>' . sysTranslations::get('leaders_no_address') . '</i></td></tr>';
                    }
                    ?>                    
                </tbody>
            </table>
            <br>
        </fieldset>
    </div>
    <?php } ?>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>