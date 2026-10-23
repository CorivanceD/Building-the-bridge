<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter')?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_name')?></td>
                <td><input type="text" name="newsSourceLogoFilter[sourceName]" value="<?= $aNewsSourceLogoFilter['sourceName'] ?>" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterNewsSourceLogos" value="<?= sysTranslations::get('global_filter')?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter')?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="2"><h2><?= sysTranslations::get('news_source_logo_found')?> (<?= (count($aNewsSourceLogos) != 0 ? ($iStart + 1) : $iStart) . '-' . ($iStart + count($aNewsSourceLogos)) ?>/<?= $iFoundRows ?>)</h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('news_source_logo_add')?>" alt="<?= sysTranslations::get('news_source_logo_add')?>"><?= sysTranslations::get('news_source_logo_add')?></a></div></td>
        </tr>
        <tr>
            <th><?= sysTranslations::get('global_name')?></th>
            <th class="{sorter:false} nonSorted" style="width: 60px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aNewsSourceLogos AS $oNewsSourceLogo) {
            echo '<tr>';
            echo '<td>' . _e($oNewsSourceLogo->sourceName) . '</td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('news_source_logo_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oNewsSourceLogo->newsSourceLogoId . '"></a>';
            echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('news_source_logo_delete').'" onclick="return confirmChoice(\'' . _e($oNewsSourceLogo->sourceName) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oNewsSourceLogo->newsSourceLogoId . '"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aNewsSourceLogos)) {
            echo '<tr><td colspan="2"><i>'.sysTranslations::get('news_source_no_logo').'</i></td></tr>';
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="bottomRow">
            <td colspan="7">
                <form method="POST">
                    <?= generatePaginationHTML($iPageCount, $iCurrPage) ?>
                    <input type="hidden" name="setPerPage" value="1" />
                    <select name="perPage" onchange="$(this).closest('form').submit();">
                        <option value=""><?= sysTranslations::get('global_all')?></option>
                        <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
                        <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
                        <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
                        <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
                    </select> <?= sysTranslations::get('global_per_page')?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>
