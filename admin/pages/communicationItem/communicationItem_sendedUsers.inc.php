<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend>Berichteigenschappen</legend>
            <table>
                <tr>
                    <td style="width: 120px">Titel:</td><td><?= _e($oCommunicationItem->title) ?></td>
                </tr>
                <tr>
                    <td>Type:</td><td><?= $oCommunicationItem->type ?></td>
                </tr>
                <? if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
                <tr>
                    <td>Onderwerp:</td><td><?= _e($oCommunicationItem->subject) ?></td>
                </tr>
                <? } ?>
                <? if(!empty($oCommunicationItem->sendDate)) { ?>
                <tr>
                    <td>Verzonden:</td><td><?= Date::strToDate($oCommunicationItem->sendDate)->format('%d-%m-%Y') ?></td>
                </tr>
                <? } ?>
                <tr>
                    <td>Naam afzender:</td><td><?= _e($oCommunicationItem->senderName) ?></td>
                </tr>
                <? if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
                <tr>
                    <td>E-mail afzender:</td><td><?= _e($oCommunicationItem->senderEmail) ?></td>
                </tr>
                <? } ?>
                <tr>
                    <td>Voorbeeld bericht <?= $oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL ? '(kan layout technisch afwijken van werkelijkheid)' : '' ?>:</td><td><a class="pointer fancyBoxLinkIframe fancybox.iframe" href="<?= ADMIN_FOLDER . '/' . http_get('controller') . '/ajax-getMessage?communicationItemId=' . $oCommunicationItem->communicationItemId ?>">Toon bericht</a></td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Statistieken</legend>
            <table>
                <tr>
                    <td style="width: 300px">Verzonden naar aantal klanten</td><td><?= count($aCustomers) ?></td>
                </tr>
                <? if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
                <tr>
                    <td>Aantal unieke gemeten openingen via meetpixel:</td><td><?= count($oCommunicationItem->getConversionStats()) ?></td>
                </tr>
                <tr>
                    <td>Aantal afmelders:</td><td><?= count($oCommunicationItem->getBlacklistItems()); ?></td>
                </tr>
                <tr>
                    <td>Aantal bounces:</td><td><?= count($oCommunicationItem->getBouncedItems()); ?></td>
                </tr>
                <? } ?>
            </table>
            <hr>
            <? if ($oCommunicationItem->type == CommunicationItem::COMMUNICATION_TYPE_EMAIL) { ?>
            <div style="float: left; margin-right: 50px;">
                Openingspercentage: <?= $iPercentageOpened ?>%
                <br>
                <span class="chart" data-percent="<?= $iPercentageOpened ?>"></span>
            </div>
            <div style="float: left; margin-right: 50px;">
                Afmeld percentage: <?= $iPercentageBlacklisted ?>%
                <br>
                <span class="chart" data-percent="<?= $iPercentageBlacklisted ?>"></span>
            </div>
            <div style="float: left">
                Bounce percentage: <?= $iPercentageBounced ?>%
                <br>
                <span class="chart" data-percent="<?= $iPercentageBounced ?>"></span>
            </div>
            <? } ?>

            <?
            # check if mail has links
            if(!empty($aRedirectLinks)) {
                ?>
                <br style="clear: both">
                <hr>
                <table>
                    <tr>
                        <td>
                            <table>
                                <?
                                # write out redirect links and amount of clicked links
                                foreach($aRedirectLinks as $oRedirectLink) {
                                    echo '<tr><td style="padding-right: 20px">' . $oRedirectLink->originalUrl . '</td><td> ' . CommunicationItemLinkRedirectStatManager::getUniqueAmountOfClicksOfRedirectStat($oCommunicationItem->communicationItemId, $oRedirectLink->communicationItemLinkRedirectId)->amount . ' </td></tr>';
                                }
                                ?>
                            </table>
                            <br>
                        </td>
                    </tr>
                </table>
                <?
            }
            ?>
            
        </fieldset>
        <fieldset>
            <legend>Exporteren</legend>
            <div style="padding: 12px 0">
                <form action="" method="post">
                    <input type="hidden" name="action" value="export_xls">
                    <input type="submit" value="Exporteren naar Excel">
                </form>
            </div>
        </fieldset>

    </div>

    <div class="contentColumn">
        <fieldset>
            <legend>Dit bericht is verzonden naar de volgende klant<?= !empty($aCustomers) && count($aCustomers) > 1 ? 'en' : '' ?>:</legend>
            <ul style="margin: 12px">
                <?
                # write out receivers of this message
                foreach ($aCustomers as $oCustomer) {
                    if (!empty($oCustomer->created)) {
                        switch ($oCommunicationItem->type) {
                            case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
                                echo '<li><a href="' . ADMIN_FOLDER . '/klanten/bewerken/' . $oCustomer->customerId . '">' . $oCustomer->getFullName() . '</a> - <a href="mailto:' . $oCustomer->email . '">' . $oCustomer->email . '</a> ' . ($oCustomer->didSignOffForEmail == 1 ? '<span class="importantMessage">(Klant heeft zich handmatig afgemeld)</span>' : '') . (is_numeric($oCustomer->bounceRuleId) ? '<span class="importantMessage">(E-mailadres staat op de bouncelijst)</span>' : '') . '</li>';
                                break;
                            case CommunicationItem::COMMUNICATION_TYPE_SMS:
                                echo '<li><a href="' . ADMIN_FOLDER . '/klanten/bewerken/' . $oCustomer->customerId . '">' . $oCustomer->getFullName() . '</a> - ' . $oCustomer->mobilePhone . '</li>';
                                break;
                        }
                    } else {
                        switch ($oCommunicationItem->type) {
                            case CommunicationItem::COMMUNICATION_TYPE_EMAIL:
                                echo '<li>' . _e($oCustomer->name) . ' - <a href="mailto:' . $oCustomer->receivedDestination . '">' . $oCustomer->receivedDestination . '</a></li>';
                                break;
                            case CommunicationItem::COMMUNICATION_TYPE_SMS:
                                echo '<li>' . _e($oCustomer->name) . ' - ' . $oCustomer->receivedDestination . '</li>';
                                break;
                        }
                    }
                }
                ?>
            </ul>
        </fieldset>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>