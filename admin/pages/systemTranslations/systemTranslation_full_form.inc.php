<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<form method="POST">
    <input name="action" type="hidden" value="save" />
    <table class="sorted" style="width: 100%;">
        <thead>
            <tr class="topRow">
                <td colspan="3">
                    <h2><?= sysTranslations::get('sysTrans_all_translations_for_language') ?> <?= $oSystemLanguage->name ?></h2>
                    <div style="float: right;">
                        <?= sysTranslations::get('sysTrans_compare_with_language') ?>
                        <select id="compareSystemLanguageId" name="compareSystemLanguageId">
                            <option value="">-- <?= sysTranslations::get('sysTrans_choose_language') ?> --</option>
                            <?
                            foreach (SystemLanguageManager::getLanguagesByFilter() AS $oLanguage) {
                                if ($oLanguage->systemLanguageId == $oSystemLanguage->systemLanguageId && $oLanguage->systemLanguageId != $oCompareSystemLanguage->systemLanguageId)
                                    continue;
                                echo '<option ' . ($oLanguage->systemLanguageId == $oCompareSystemLanguage->systemLanguageId ? 'selected' : '') . ' value="' . $oLanguage->systemLanguageId . '">' . $oLanguage->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                </td>
            </tr>
            <tr>
                <th style="width: 33.33333%;"><?= sysTranslations::get('sysTrans_label') ?></th>
                <th><?= sysTranslations::get('sysTrans_text') ?> <?= $oSystemLanguage->name ?></th>
                <? if (!empty($oCompareSystemLanguage)) { ?>
                    <th  style="width: 33.33333%;"><?= sysTranslations::get('sysTrans_text') ?> <?= $oCompareSystemLanguage->name ?></th>
                <? } ?>
            </tr>
        </thead>
        <tbody>
            <?
            foreach ($aSystemTranslations AS $oSystemTranslation) {
                // take text or if empty and not empty compare, take that one
                $sText = !empty($oSystemTranslation->text) ? $oSystemTranslation->text : (!empty($aCompareTextsByLabel[$oSystemTranslation->label]) ? $aCompareTextsByLabel[$oSystemTranslation->label] : '');

                preg_match_all('#\n#', $sText, $aMatches);
                $iRows = 1;
                if (isset($aMatches[0])) {
                    $iRows += count($aMatches[0]);
                }
                echo '<tr>';
                echo '<td>' . $oSystemTranslation->label . '</td>';
                echo '<td>';
                echo '<input type="hidden" name="systemTranslations[labels][]" value="' . $oSystemTranslation->label . '" />';
                echo '<textarea name="systemTranslations[texts][]" style="height: ' . (15 * $iRows) . 'px; width: 95%;">' . $oSystemTranslation->text . '</textarea>';
                echo '</td>';
                // set text to compare if wanted
                if (!empty($oCompareSystemLanguage)) {
                    echo '<td>' . (!empty($aCompareTextsByLabel[$oSystemTranslation->label]) ? nl2br(_e($aCompareTextsByLabel[$oSystemTranslation->label])) : '') . '</td>';
                }
                echo '</tr>';
            }
            if (count($aSystemTranslations) == 0) {
                echo '<tr><td colspan="3">' . sysTranslations::get('sysTrans_no_systemTranslations') . '</i></td></tr>';
            }
            ?>
        </tbody>
        <? if (count($aSystemTranslations) > 0) { ?>
            <tfoot>
                <tr class="bottomRow">
                    <td colspan="3">
                        <input type="submit" value="<?= sysTranslations::get('global_save') ?>" />
                    </td>
                </tr>
            </tfoot>
        <? } ?>
    </table>
</form>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?
$sConfirm = sysTranslations::get('sysTrans_change_language_will_empty_form');
$sCurrentUrlPath = getCurrentUrlPath();
$sJavascript = <<<EOT
<script>
    var lastCompareSystemLanguageId = $('#compareSystemLanguageId').val();
    $('#compareSystemLanguageId').change(function(e) {
        if (confirm('$sConfirm')) {
        window.location = '$sCurrentUrlPath?compareSystemLanguageId=' + $('#compareSystemLanguageId').val();
        }else{
            $('#compareSystemLanguageId').val(lastCompareSystemLanguageId);
           e.preventDefault();
        }
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>