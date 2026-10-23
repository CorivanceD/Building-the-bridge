<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="7"><h2><?= sysTranslations::get('petitions_all') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('petitions_add_tooltip') ?>" alt="<?= sysTranslations::get('petitions_add_tooltip') ?>"><?= sysTranslations::get('petitions_add') ?></a></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted" style="width: 60px;"><?= sysTranslations::get('petitions_active') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 100px;"><?= sysTranslations::get('global_date') ?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_author') ?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('global_title') ?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('petitions_required_signatures') ?></th>
            <th class="{sorter:false} nonSorted"><?= sysTranslations::get('petitions_total_signatures') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 100px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aPetitions AS $oPetition) {
            echo '<td>';
            # online offline button
            echo '<a id="petition_' . $oPetition->petitionId . '_online_1" title="'.sysTranslations::get('petition_set_offline_tooltip').'" class="action_icon ' . ($oPetition->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oPetition->petitionId . '/?online=0"></a>';
            echo '<a id="petition_' . $oPetition->petitionId . '_online_0" title="'.sysTranslations::get('petition_set_online_tooltip').'" class="action_icon ' . ($oPetition->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oPetition->petitionId . '/?online=1"></a>';
            echo '</td>';
            echo '<td>' . Date::strToDate($oPetition->created)->format('%d-%m-%Y') . '</td>';
            echo '<td>' . $oPetition->author . '</td>';
            echo '<td>' . _e($oPetition->title) . '</td>';
            echo '<td>' . $oPetition->requiredSignatures . '</td>';
            echo '<td>' . count($oPetition->getSignatures()) . '</td>'; 
            echo '<td>';
        if ($oPetition->isEditable())
            echo '<a title="Petition edition" class="action_icon edit_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oPetition->petitionId . '"></a>';
        else
            echo '<a title="Petition can\'t be edited" class="action_icon edit_icon grey" href="#"></a>';
        echo '<a title="Report creation" class="action_icon copy_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/export/' . $oPetition->petitionId . '"></a>';
        # delete button
        if ($oPetition->isDeletable())
            echo '<a onclick="return confirmChoice(\'petition ' . $oPetition->title . '\');" class="action_icon delete_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oPetition->petitionId . '"></a>';
        else
            echo '<a class="action_icon delete_icon grey" href="#" title="Petition cannot be deleted"></a>';            
            echo '</td>';
            echo '</tr>';
        }
        if (count($aPetitions) == 0) {
            echo '<tr><td colspan="7"><i>'.sysTranslations::get('petitions_no_petitions').'</i></td></tr>';
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
<?
# create necessary javascript
$sPetitionOnlineMsg = sysTranslations::get('petition_online');
$sPetitionOfflineMsg = sysTranslations::get('petition_offline');
$sPetitionNotChangedMsg = sysTranslations::get('petition_not_changed');
$sController = http_get('controller');
$sBottomJavascript = <<<EOT
<script>
    $("a.online_icon, a.offline_icon").click(function(e){
        $.ajax({
            type: "GET",
            url: $(this).attr('href'),
            data: 'ajax=1',
            async: true,
            success: function(data){
                var dataObj = eval('(' + data + ')');

                /* On success */
                if (dataObj.success == true){
                    $("#petition_"+dataObj.petitionId+"_online_0").hide(); // hide offline button
                    $("#petition_"+dataObj.petitionId+"_online_1").hide(); // hide online button
                    $("#petition_"+dataObj.petitionId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)    
                        showStatusUpdate("$sPetitionOfflineMsg");
                    if(dataObj.online == 1)    
                        showStatusUpdate("$sPetitionOnlineMsg");                            
                } else {
                    showStatusUpdate("$sPetitionNotChangedMsg");
                }
            }
        });
        e.preventDefault();
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>