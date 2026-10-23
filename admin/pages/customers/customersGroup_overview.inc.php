<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="4"><h2><?= sysTranslations::get('customer_group_found') ?></h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="<?= sysTranslations::get('customer_group_add') ?>" alt="<?= sysTranslations::get('customer_group_add') ?>"><?= sysTranslations::get('customer_group_add') ?></a></div></td>
        </tr>
        <tr>
            <th><?= sysTranslations::get('global_name') ?></th>
            <th class="{sorter:false} nonSorted" style="width: 90px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aCustomerGroups AS $oCustomerGroup) {
            $iCustomers = $oCustomerGroup->getAmountOfClients();
            $iOnlineCustomers = $oCustomerGroup->getAmountOfClients(true);
            $iBouncedCustomers = $oCustomerGroup->getAmountOfBouncedClients(false, true);
            echo '<tr>';
            echo '<td>' . _e($oCustomerGroup->title) . ' (' . $iCustomers . ' ' . sysTranslations::get('customer_client_lowercase') . ($iCustomers != 1 ? sysTranslations::get('customer_client_plural') : '' ) . ', ' . $iOnlineCustomers . ' ' . sysTranslations::get('customer_client_lowercase') . ($iOnlineCustomers != 1 ? sysTranslations::get('customer_client_plural') : '' ) . ' ' . strtolower(sysTranslations::get('global_active_lowercase')) . ', '.$iBouncedCustomers.' e-mail(s) bounced)</td>';
            echo '<td>';
            echo '<a class="action_icon edit_icon" title="'.sysTranslations::get('customer_group_edit').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCustomerGroup->customerGroupId . '"></a>';
            echo '<a class="action_icon add_icon" title="'.sysTranslations::get('customer_add_to_group').'" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/klanten-toevoegen/' . $oCustomerGroup->customerGroupId . '"></a>';
            if($oCustomerGroup->isDeletable()) {
                echo '<a class="action_icon delete_icon" title="'.sysTranslations::get('customer_group_delete').'" onclick="return confirmChoice(\'' . _e($oCustomerGroup->title) . '\', \''.sysTranslations::get('customer_delete_warning').'\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oCustomerGroup->customerGroupId . '"></a>';
            } else {
                echo '<span class="action_icon delete_icon grey" title="'.sysTranslations::get('customer_group_not_delete').'"></span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        if (empty($aCustomerGroups)) {
            echo '<tr><td colspan="4"><i>'.sysTranslations::get('customer_no_group').'</i></td></tr>';
        }
        ?>
    </tbody>
</table>