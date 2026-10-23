<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('modules_module') ?></legend>
            <form method="POST" action="" class="validateForm">
                <input type="hidden" value="save" name="action" />
                <table class="withForm">
                    <tr>
                        <td class="withLabel" style="width: 160px;"><label for="name"><?= sysTranslations::get('modules_name') ?> *</label></td>
                        <td><input id="name" class="{validate:{required:true}} autofocus" title="<?= sysTranslations::get('modules_name_tooltip')?>" type="text" autocomplete="off" name="name" value="<?= $oModule->name ?>" /></td>
                        <td><span class="error"><?= $oModule->isPropValid("name") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="linkName"><?= sysTranslations::get('modules_link_name') ?> *</label></td>
                        <td><input id="linkName" class="{validate:{required:true}}" title="<?= sysTranslations::get('modules_link_name_tooltip')?>" type="text" autocomplete="off" name="linkName" value="<?= $oModule->linkName ?>" /></td>
                        <td><span class="error"><?= $oModule->isPropValid("linkName") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="parentModuleId"><?= sysTranslations::get('modules_main') ?></label></td>
                        <td>
                            <select name="parentModuleId" id="parentModuleId">
                                <option value="">-- <?= sysTranslations::get('modules_no_main') ?> --</option>
                                <?php
                                foreach (ModuleManager::getModulesByFilter(array('showAll' => 1, 'parentModuleId' => -1)) AS $oParentModule) {
                                    if ($oParentModule->moduleId == $oModule->moduleId)
                                        continue; //skip current module
                                    echo '<option ' . ($oParentModule->moduleId == $oModule->parentModuleId ? 'SELECTED' : '') . ' value="' . $oParentModule->moduleId . '">' . sysTranslations::get($oParentModule->linkName) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="order"><?= sysTranslations::get('global_sequence') ?> *</label></td>
                        <td><input id="order" class="{validate:{required:true}}" title="<?= sysTranslations::get('global_sequence_tooltip') ?>" type="text" autocomplete="off" name="order" value="<?= $oModule->order ?>" /></td>
                        <td><span class="error"><?= $oModule->isPropValid("order") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="hidden" name="showInMenu" value="0" /><?php // extra hidden field for workaround with checkboxes and _load function in controller        ?>
                            <input class="alignCheckbox" type="checkbox" id="showInMenu" name="showInMenu" <?= ($oModule->showInMenu ? 'CHECKED' : '') ?> value="1" /> <label for="showInMenu"><?= sysTranslations::get('modules_show_menu') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
            </form>
        </fieldset>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>