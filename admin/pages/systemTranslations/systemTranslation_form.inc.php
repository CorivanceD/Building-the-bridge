<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('sysTrans_systemTranslation') ?></legend>
            <form method="POST" action="<?= getCurrentUrl() ?>" class="validateForm">
                <input type="hidden" value="save" name="action" />
                <table class="withForm">
                    <tr>
                        <td class="withLabel"><label for="systemLanguageId"><?= sysTranslations::get('sysTrans_language') ?> *</label></td>
                        <td>
                            <select class="{validate:{required:true}} default" id="systemLanguageId" name="systemLanguageId" title="<?= _e(sysTranslations::get('sysTrans_enter_language_tooltip')) ?>">
                                <option value="">-- <?= sysTranslations::get('sysTrans_choose_language') ?> --</option>
                                <?
                                foreach (SystemLanguageManager::getLanguagesByFilter() AS $oSystemLanguage) {
                                    echo '<option ' . ($oSystemLanguage->systemLanguageId == $oSystemTranslation->systemLanguageId ? 'selected' : '') . ' value="' . $oSystemLanguage->systemLanguageId . '">' . $oSystemLanguage->name . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td><span class="error"><?= $oSystemTranslation->isPropValid("systemLanguageId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel" style="width: 120px;"><label for="label"><?= sysTranslations::get('sysTrans_label') ?> *</label></td>
                        <td><input id="label" class="{validate:{required:true}} default" maxlength="50" name="label" style="width: 400px;" title="<?= _e(sysTranslations::get('sysTrans_enter_label_tooltip')) ?>" type="text" value="<?= $oSystemTranslation->label ?>" /></td>
                        <td><span class="error"><?= $oSystemTranslation->isPropValid("label") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="text"><?= sysTranslations::get('sysTrans_text') ?> *</label></td>
                        <td>
                            <textarea class="{validate:{required:true}} default" id="text" name="text" style="width: 600px; height: 17px;" title="<?= _e(sysTranslations::get('sysTrans_enter_text_tooltip')) ?>"><?= $oSystemTranslation->text ?></textarea>
                        </td>
                        <td><span class="error"><?= $oSystemTranslation->isPropValid("text") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <input type="submit" value="<?= _e(sysTranslations::get('global_save')) ?>" name="save" />
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
<?
$sAdminFolder = ADMIN_FOLDER;
$sController = http_get('controller');
$iSystemTranslationId = $oSystemTranslation->systemTranslationId;
$sSystemTranslationRemoteError = sysTranslations::get('sysTrans_already_exists');
$sJavascript = <<<EOT
<script>
    $('#label').rules('add', {
        remote: {
            url: '$sAdminFolder/$sController/ajax-checkLabel?systemTranslationId=$iSystemTranslationId',
            data: {
                systemLanguageId: function() {
                    return $('#systemLanguageId').val();
                }
            }
        },
         messages: {
            remote: "$sSystemTranslationRemoteError"
        }
    });
        
    $('#systemLanguageId').rules('add', {
        remote: {
            url: '$sAdminFolder/$sController/ajax-checkLabel?systemTranslationId=$iSystemTranslationId',
            data: {
                label: function() {
                    return $('#label').val();
                }
            }
        },
         messages: {
            remote: "$sSystemTranslationRemoteError"
        }
    });        
</script>        
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>