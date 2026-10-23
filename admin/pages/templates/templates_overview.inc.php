<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter')?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_name')?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('templates_tooltip_filter_name')?>">&nbsp;</div></td>
                <td><input type="text" name="templateFilter[description]" value="<?= $aTemplateFilter['description'] ?>" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterTemplates" value="Filter templates" /> <input type="submit" name="resetFilter" value="Reset filter" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="6"><h2><?= sysTranslations::get('templates_found_templates')?> (<?= (count($aTemplates) != 0 ? ($iStart + 1) : $iStart) . '-' . ($iStart + count($aTemplates)) ?>/<?= $iFoundRows ?>)</h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('templates_add_new_template')?>" alt="<?= sysTranslations::get('templates_add_new_template')?>"><?= sysTranslations::get('templates_add_new_template')?></a></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted">#</th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_name')?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_group')?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_type')?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('templates_technical_name')?></th>
            <th class="{sorter:false} nonSorted" style="width: 120px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aTemplates AS $oTemplate) {
            echo '<tr>';
            echo '<td>' . $oTemplate->templateId . '</td>';
            echo '<td>' . $oTemplate->description . '</td>';
            echo '<td>' . ($oTemplate->getTemplateGroup() ? $oTemplate->getTemplateGroup()->templateGroupName : sysTranslations::get('global_unknown')) . '</td>';
            echo '<td>' . $oTemplate->type . '</td>';
            echo '<td>' . $oTemplate->name . '</td>';
            echo '<td>';
            if ($oTemplate->isEditable()) {
                echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('templates_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oTemplate->templateId . '"></a>';
            } else {
                echo '<span class="action_icon edit_icon grey"></span>';
            }
            if ($oTemplate->isDeletable()) {
                echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('templates_remove').'" onclick="return confirmChoice(\'template '. $oTemplate->description . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oTemplate->templateId . '"></a>';
            } else {
                echo '<span class="action_icon delete_icon grey"></span>';
            }
            echo '<a class="action_icon copy_icon" title="'.sysTranslations::get('templates_copy').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/toevoegen?copyFrom=' . $oTemplate->templateId . '"></a>';
            echo '<a class="action_icon mail_icon fancyBoxLink" onclick="$(\'#sendTestTemplateId\').val(\'' . $oTemplate->templateId . '\');" title="'.sysTranslations::get('templates_send_test').'" href="#sendTestForm"></a>';
            echo '</td>';
            echo '</tr>';
        }
        if (count($aTemplates) == 0) {
            echo '<tr><td colspan="6"><i>'.sysTranslations::get('templates_no_templates').'</i></td></tr>';
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
                    </select><?= sysTranslations::get('global_per_page') ?>
                </form>
            </td>
        </tr>
    </tfoot>
</table>
<div class="hide">
    <div id="sendTestForm">
        <h2><?= sysTranslations::get('templates_test') ?></h2>
        <form onsubmit="sendTest(this);
                return false;" method="POST">
            <input type="hidden" id="sendTestTemplateId" name="templateId" value="" />
            <table class="withForm">
                <tr>
                    <td style="width: 60px;" class="withLabel"><label for="to"><?= sysTranslations::get('global_naar') ?></label></td>
                    <td><input type="text" size="30" name="to" id="to" value="" /></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Test verzenden" /></td>
                </tr>
            </table>
        </form>
    </div>
</div>
<?

$sJavascript = <<<EOT
<script>
    function sendTest(form){
        $.ajax({
            type: "POST",
            url: '/admin/templates-beheer/ajax-sendTest',
            data: $(form).serialize(),
            success: function(data){
                var dataObj = eval('('+data+')');
                if(dataObj.success){
                    alert('Test email is verzonden naar: `' + dataObj.to + '`');
                    $.fancybox.close();
                }else{
                    alert('Test email kon niet worden verzonden naar: `' + dataObj.to + '`');
                }
            }
        });
    }
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>