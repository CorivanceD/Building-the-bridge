<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter') ?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_search_word') ?></td>
                <td><input type="text" name="formBackupFilter[q]" value="<?= _e($aFormBackupFilter['q']) ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel"><?= sysTranslations::get('global_date') ?></td>
                <td><input class="datePickerDefault hasDatePicker" type="text" name="formBackupFilter[created]" value="<?= $aFormBackupFilter['created'] ? Date::strToDate($aFormBackupFilter['created'])->format('%d-%m-%Y') : '' ?>" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterFormBackups" value="<?= sysTranslations::get('formbackups_filter') ?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter') ?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="3"><h2><?= sysTranslations::get('formbackups_all') ?></h2></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_date_time') ?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_name') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 30px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aFormBackups AS $oFormBackup) {
            echo '<tr>';
            echo '<td>' . Date::strToDate($oFormBackup->created)->format('%d-%m-%Y %H:%M:%S') . '</td>';
            echo '<td>' . $oFormBackup->name . '</td>';
            echo '<td><a class="action_icon fancyBoxLink fancybox.ajax magnifying_glass_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getDetails/' . $oFormBackup->formBackupId . '"></a></td>';
            echo '</tr>';
        }
        if (count($aFormBackups) == 0) {
            echo '<tr><td colspan="3"><i>'.sysTranslations::get('formbackups_no_formbackups').'</i></td></tr>';
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