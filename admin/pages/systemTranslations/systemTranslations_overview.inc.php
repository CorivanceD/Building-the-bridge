<div class="cf">
    <div class="contentColumn">
        <form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
            <input type="hidden" name="filterForm" value="1" />
            <fieldset style="margin-bottom: 20px;">
                <legend><?= sysTranslations::get('global_filter') ?></legend>
                <table class="withForm">
                    <tr>
                        <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('sysTrans_label') ?></td>
                        <td><input type="text" name="systemTranslationFilter[label]" value="<?= _e($aSystemTranslationFilter['label']) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><?= sysTranslations::get('sysTrans_text') ?></td>
                        <td><input type="text" name="systemTranslationFilter[text]" value="<?= _e($aSystemTranslationFilter['text']) ?>" /></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><?= sysTranslations::get('global_language') ?></td>
                        <td>
                            <select name="systemTranslationFilter[systemLanguageId]">
                                <option value="">-- <?= sysTranslations::get('sysTrans_all_languages') ?> --</option>
                                <?
                                foreach (SystemLanguageManager::getLanguagesByFilter() AS $oSystemLanguage) {
                                    echo '<option ' . ($oSystemLanguage->systemLanguageId == $aSystemTranslationFilter['systemLanguageId'] ? 'selected' : '') . ' value="' . $oSystemLanguage->systemLanguageId . '">' . $oSystemLanguage->name . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" name="filterSystemTranslations" value="<?= sysTranslations::get('sysTrans_filter_systemTranslations') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('sysTrans_full_system_translations') ?></legend>
            <ul style="padding-left: 15px; margin-bottom: 10px;">
                <?
                foreach (SystemLanguageManager::getLanguagesByFilter() AS $oSystemLanguage) {
                    echo '<li><a href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/edit-full-system-translation/' . $oSystemLanguage->systemLanguageId . '">' . $oSystemLanguage->name . '</a></li>';
                }
                ?>
            </ul>
        </fieldset>
    </div>
</div>
<table class="sorted" style="width: 100%;">
    <thead>
        <tr class="topRow">
            <td colspan="4"><h2><?= sysTranslations::get('sysTrans_all') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('sysTrans_add_tooltip') ?>" alt="<?= sysTranslations::get('sysTrans_add_tooltip') ?>"><?= sysTranslations::get('sysTrans_add') ?></a></div></td>
        </tr>
        <tr>
            <th><?= sysTranslations::get('sysTrans_language') ?></th>
            <th><?= sysTranslations::get('sysTrans_label') ?></th>
            <th><?= sysTranslations::get('sysTrans_text') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aSystemTranslations AS $oSystemTranslation) {
            echo '<tr>';
            echo '<td>' . $oSystemTranslation->getLanguage()->name . '</td>';
            echo '<td>' . $oSystemTranslation->label . '</td>';
            echo '<td>' . nl2br(_e($oSystemTranslation->text)) . '</td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="' . sysTranslations::get('sysTrans_edit_systemTranslation') . '" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oSystemTranslation->systemTranslationId . '"></a>';
            echo '<a class="action_icon delete_icon" title="' . sysTranslations::get('sysTrans_delete_systemTranslation') . '" onclick="return confirmChoice(\'' . strtolower(sysTranslations::get('sysTrans_systemTranslation')) . ' ' . $oSystemTranslation->label . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oSystemTranslation->systemTranslationId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aSystemTranslations) == 0) {
            echo '<tr><td colspan="4">' . sysTranslations::get('sysTrans_no_systemTranslations') . '</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="6">
                <form method="POST">
                    <?= generatePaginationHTML($iPageCount, $iCurrPage) ?>
                    <input type="hidden" name="setPerPage" value="1" />
                    <select name="perPage" onchange="$(this).closest('form').submit();">
                        <option value=""><?= sysTranslations::get('global_all')?></option>
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