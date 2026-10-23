<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>" method="POST">
    <input type="hidden" name="filterForm" value="1" />
    <fieldset style="margin-bottom: 20px;">
        <legend><?= sysTranslations::get('global_filter')?></legend>
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 116px;"><?= sysTranslations::get('global_name')?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('customer_filter_name_tooltip')?>">&nbsp;</div></td>
                <td><input type="text" name="customerFilter[name]" value="<?= $aCustomerFilter['name'] ?>" /></td>
            </tr>
            <tr>
                <td class="withLabel"><?= sysTranslations::get('global_show_inactive')?></td>                
                <td>
                    <input <?= !empty($aCustomerFilter['showAll']) ? 'checked' : '' ?> type="checkbox" name="customerFilter[showAll]" value="1" />
                </td>
            </tr>             
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="filterCustomers" value="<?= sysTranslations::get('customer_filter_customer')?>" /> <input type="submit" name="resetFilter" value="<?= sysTranslations::get('global_reset_filter')?>" /></td>
            </tr>
        </table>
    </fieldset>
</form>
<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="4"><h2><?= sysTranslations::get('customer_found')?> (<?= (count($aCustomers) != 0 ? ($iStart + 1) : $iStart) . '-' . ($iStart + count($aCustomers)) ?>/<?= $iFoundRows ?>)</h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('customer_add')?>" alt="<?= sysTranslations::get('customer_add')?>"><?= sysTranslations::get('customer_add')?></a></div></td>
        </tr>
        <tr>
            <th class="{sorter:false} nonSorted onlineOffline"></th>
            <th><?= sysTranslations::get('global_name')?></th>
            <th><?= sysTranslations::get('global_email')?></th>
            <th class="{sorter:false} nonSorted" style="width: 30px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($aCustomers AS $oCustomer) {
            echo '<tr>';
            echo '<td>';
            # online offline button
            echo '<a id="customer_' . $oCustomer->customerId . '_online_1" title="Put customer offline" class="action_icon ' . ($oCustomer->online ? '' : 'hide') . ' online_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oCustomer->customerId . '/?online=0"></a>';
            echo '<a id="customer_' . $oCustomer->customerId . '_online_0" title="Put customer online" class="action_icon ' . ($oCustomer->online ? 'hide' : '') . ' offline_icon" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-setOnline/' . $oCustomer->customerId . '/?online=1"></a>';
            echo '</td>';
            echo '<td>' . _e($oCustomer->name) . '</td>';
            echo '<td>' . _e($oCustomer->email) . '</td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('customer_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomer->customerId . '"></a>';            
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aCustomers)) {
            echo '<tr><td colspan="7"><i>'.sysTranslations::get('customer_no_customers').'</i></td></tr>';
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
<?
# create necessary javascript
$sController = http_get('controller');
$sCustomersOnline = sysTranslations::get('customer_online');
$sCustomersOffline = sysTranslations::get('customer_offline');
$sCustomersStatusNotChanged = sysTranslations::get('customer_status_not_changed');
        
$sBottomJavascript = <<<EOT
<script type="text/javascript">
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
                    $("#customer_"+dataObj.customerId+"_online_0").hide(); // hide offline button
                    $("#customer_"+dataObj.customerId+"_online_1").hide(); // hide online button
                    $("#customer_"+dataObj.customerId+"_online_"+dataObj.online).css('display', 'inline-block'); // show button based on online value
                    if(dataObj.online == 0)    
                        showStatusUpdate("$sCustomersOffline");
                    if(dataObj.online == 1)    
                        showStatusUpdate("$sCustomersOnline");                            
                } else {
                    showStatusUpdate("$sCustomersStatusNotChanged");
                }
            }
        });
        e.preventDefault();
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>
