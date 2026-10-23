<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="6"><h2>Gemaakte berichten (<?= (count($aCommunicationItemsNotSended)) ?>)</h2><div class="right"><a class="addBtn textLeft" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>/toevoegen" title="Bericht toevoegen" alt="Bericht toevoegen">Bericht toevoegen</a></div></td>
        </tr>
        <tr>
            <th>Naam</th>
            <th>Subject</th>
            <th>Type</th>
            <th>Status</th>
            <th>Gewijzigd</th>
            <th class="{sorter:false} nonSorted" style="width: 90px;">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aCommunicationItemsNotSended AS $oCommunicationItem) {
            echo '<tr>';
            echo '<td>' . _e($oCommunicationItem->title) . '</td>';
            echo '<td><a class="pointer fancyBoxLinkIframe fancybox.iframe" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getMessage?communicationItemId=' . $oCommunicationItem->communicationItemId . '">' . _e($oCommunicationItem->subject) . '</a></td>';
            echo '<td>' . _e($oCommunicationItem->type) . '</td>';
            
            # for SMS
            if($oCommunicationItem->sending == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
                $sStatus = '<span style="color: #f00;">Mislukt. Continueren door verder te versturen.</span>';
            } elseif($oCommunicationItem->sending == 0 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
                $sStatus = 'Nog niet verzonden';
            }
            # for bulk email
            if($oCommunicationItem->hasBulkSendingError == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                $sStatus = '<span style="color: #f00;">Mislukt. Contact A-side media.</span>';
            } elseif($oCommunicationItem->pendingInBulkCron == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                $sStatus = 'Wordt verstuurd';
            } elseif($oCommunicationItem->hasBulkSendingError == 0 && $oCommunicationItem->sending == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                $sStatus = 'Wordt verstuurd';
            } elseif($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                $sStatus = 'Nog niet verzonden';
            }
            echo '<td>' . $sStatus . '</td>';
            
            echo '<td>' . $oCommunicationItem->modified . '</td>';
            echo '<td>';

            # check if customer groups are connected
            if($oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->sending == 0 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
                echo '<a class="action_icon mail_icon" title="Verzend bericht" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verzenden/' . $oCommunicationItem->communicationItemId . '"></a>';
            } elseif($oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->sending == 0 && $oCommunicationItem->pendingInBulkCron == 0 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                echo '<a class="action_icon mail_icon" title="Verzend bericht" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verzenden/' . $oCommunicationItem->communicationItemId . '"></a>';
            } elseif($oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->sending == 0 && $oCommunicationItem->pendingInBulkCron == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                echo '<span class="action_icon mail_icon grey" title="Bericht wacht op reactie mailserver"></span>';
            } elseif($oCommunicationItem->hasCustomerGroupsConnected() && ($oCommunicationItem->sending == 1 && $oCommunicationItem->pendingInBulkCron == 0) && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                echo '<span class="action_icon mail_icon grey" title="Bericht wordt verzonden naar mailserver"></span>';
            } elseif(!$oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
                echo '<span class="action_icon mail_icon grey" title="Aan dit bericht zijn nog geen ontvangers gekoppeld"></span>';
            } elseif(!$oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) {
                echo '<span class="action_icon mail_icon grey" title="Aan dit bericht zijn nog geen ontvangers gekoppeld"></span>';
            } elseif($oCommunicationItem->hasCustomerGroupsConnected() && $oCommunicationItem->sending == 1 && $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_SMS) {
                echo '<a class="action_icon mail_icon" title="Verder verzenden bericht" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verzenden/' . $oCommunicationItem->communicationItemId . '"></a>';
            } else {
                echo '<span class="action_icon mail_icon grey" title="Aan dit bericht zijn nog geen ontvangers gekoppeld"></span>';
            }

            if($oCommunicationItem->sending == 0) {
                echo '<a class="action_icon edit_icon" title="Bewerk bericht" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/bewerken/' . $oCommunicationItem->communicationItemId . '"></a>';
                echo '<a class="action_icon delete_icon" title="Verwijder bericht" onclick="return confirmChoice(\'' . _e($oCommunicationItem->title) . '\');" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/verwijderen/' . $oCommunicationItem->communicationItemId . '"></a>';                
            } else {
                echo '<span class="action_icon edit_icon grey"></span>';
                echo '<span class="action_icon delete_icon grey"></span>';
            }

            echo '</td>';
            echo '</tr>';
        }
        if (empty($aCommunicationItemsNotSended)) {
            echo '<tr><td colspan="6"><i>Er zijn geen berichten om weer te geven</i></td></tr>';
        }
        ?>
    </tbody>
</table>

<br><br>

<table class="sorted">
    <thead>
        <tr class="topRow">
            <td colspan="5"><h2>Verzonden berichten (<?= (count($aCommunicationItemsSended)) ?>)</h2></td>
        </tr>
        <tr>
            <th>Naam</th>
            <th>Subject</th>
            <th>Verzonden</th>
            <th>Type</th>
            <th class="{sorter:false} nonSorted" style="width: 30px;">Ontvangers</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aCommunicationItemsSended AS $oCommunicationItem) {
            echo '<tr>';
            echo '<td>' . _e($oCommunicationItem->title) . '</td>';
            echo '<td><a class="pointer fancyBoxLinkIframe fancybox.iframe" href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getMessage?communicationItemId=' . $oCommunicationItem->communicationItemId . '">' . _e($oCommunicationItem->subject) . '</a></td>';
            echo '<td>' . (empty($oCommunicationItem->sendDate) ? '' : Date::strToDate($oCommunicationItem->sendDate)->format('%d-%m-%Y') ) . '</td>';
            echo '<td>' . _e($oCommunicationItem->type) . '</td>';
            echo '<td><a href="' . ADMIN_FOLDER . '/' . http_get('controller') . '/gebruikers/' . $oCommunicationItem->communicationItemId . '" class="action_icon users_icon"></a></td>';
            echo '</tr>';
        }
        if (empty($aCommunicationItemsSended)) {
            echo '<tr><td colspan="5"><i>Er zijn geen berichten om weer te geven</i></td></tr>';
        }
        ?>
    </tbody>
</table>