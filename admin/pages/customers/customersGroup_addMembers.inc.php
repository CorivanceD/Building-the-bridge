<form action="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/<?= http_get('param1') ?>/<?= http_get('param2') ?>" method="POST">
    <input type="hidden" name="action" value="addMembersToCustomerGroup" />
    <table class="sorted">
        <thead>
            <tr class="topRow">
                <td colspan="6"><h2><?= sysTranslations::get('customer_clients_not_on_this_list')?></h2></td>
            </tr>
            <tr>
                <th class="{sorter:false}"><?= sysTranslations::get('global_copy')?></th>
                <th><?= sysTranslations::get('global_name')?></th>
                <th><?= sysTranslations::get('global_email')?></th>
                <th><?= sysTranslations::get('global_mobile_number')?></th>
                <th><?= sysTranslations::get('customer_customer_email')?></th>
                <th><?= sysTranslations::get('customer_customer_sms')?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            # has unlinked customers
            if(!empty($aCustomersNotInThisGroup)) {
                # loop through instances
                foreach ($aCustomersNotInThisGroup AS $oCustomer) {
                    echo '<tr>';
                        echo '<td><input type="checkbox" value="' . $oCustomer->customerId . '" name="aCustomerIds[]"></td>';
                        echo '<td>' . _e($oCustomer->name) . '</td>';
                        echo '<td>' . _e($oCustomer->email) . '</td>';
                        echo '<td>' . _e($oCustomer->mobilePhone) . '</td>';
                        echo '<td>' . ($oCustomer->contactByEmail == 1 ? sysTranslations::get('global_yes') : sysTranslations::get('global_no')) . '</td>';
                        echo '<td>' . ($oCustomer->contactBySms == 1 ? sysTranslations::get('global_yes') : sysTranslations::get('global_no')) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                echo '<table>';
                    echo '<tr>';
                        echo '<td colspan="6">';
                        echo '<br><input type="submit" value="'.sysTranslations::get('customer_add_to_group').'" name="save" />';
                        echo '</td>';
                    echo '</tr>';
                echo '</table>';
            }
            # no instances
            else {
                echo '<tr><td colspan="6"><i>' . sysTranslations::get('customers_no_clients_to_add') . '</i></td></tr>';
                echo '</tbody></table>';
            }
           ?>
        </tbody>
    </table>
</form>

<?php
# create necessary javascript
$sBottomJavascript = <<<EOT
<script type="text/javascript">
    // make row clickable
    $('tr').click(function(event) {
        if (event.target.type !== 'checkbox') {
            $(':checkbox', this).trigger('click');
        }
    });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sBottomJavascript);
?>