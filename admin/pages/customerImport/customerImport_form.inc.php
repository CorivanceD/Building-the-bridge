<h1>Klanten import</h1>
<div class="errorBox" <?= !empty($aErrors) ? 'style="display:block;"' : '' ?>>
    <div class="title">De volgende fouten zijn gevonden</div>
    <ul>
        <?
        if (!empty($aErrors)) {
            foreach ($aErrors AS $sField => $sError) {
                echo '<li><label for="' . $sField . '" style="display: block;">' . $sError . '</label></li>';
            }
        }
        ?>
    </ul>
</div>
<? if (!empty($aLogs)) { ?>
    <div class="import-messages">
        <h2>Resultaat <?= $iSaved . '/' . $iTotal ?> opgeslagen</h2>
        <ul>
            <li>Totaal rijen: <?= $iTotal ?></li>
            <li>Totaal errors: <?= $iErrors ?></li>
            <li>Totaal waarschuwingen: <?= $iWarnings ?></li>
            <li>Totaal opgeslagen: <?= $iSaved ?></li>
        </ul>
        <?
        foreach ($aLogs AS $iRow => $aErrors) {
            echo '<div class="row ' . ($iRow == 2 ? 'first' : '') . '">';
            foreach ($aErrors AS $sType => $aMsgs) {
                echo '<div class="type-' . $sType . '">';
                foreach ($aMsgs AS $sMsg) {
                    echo '<div class="msg">' . $sMsg . '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
<? } ?>
<form method="POST" enctype="multipart/form-data">
    <input name="action" type="hidden" value="import" />
    <table class="withForm">
        <tr>
            <td class="withLabel" style="width: 180px;"><label for="file">Bestand (.xls of .xlsx)</label></td>
            <td>
                <input name="file" type="file" />
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        <tr>
            <td>Kies te koppelen groep</td>
            <td>
                <?
                $aCustomerGroups = CustomerGroupManager::getAllCustomerGroups();
                foreach ($aCustomerGroups as $oCustomerGroup) {
                    ?>
                    <input class="alignCheckbox" id="customerGroup-<?= $oCustomerGroup->customerGroupId ?>" name="customerGroupIds[]" type="checkbox" value="<?= $oCustomerGroup->customerGroupId ?>">
                    <label for="customerGroup-<?= $oCustomerGroup->customerGroupId ?>"><?= _e($oCustomerGroup->title) ?></label>
                    <br>
                <? } ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><hr></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <input type="submit" value="Importeer" name="save" />
            </td>
        </tr>
    </table>
</form>
<hr />
<div>
    <h2>Ondersteunde kolommen</h2>|
    <?
    foreach ($aImportFields AS $aField) {
        $bRequiredColumn = in_array($aField['column'], $aRequiredColumns);


        if ($bRequiredColumn) {
            echo '<b><u>' . $aField['column'] . '</u></b> | ';
        } else {
            echo $aField['column'] . ' | ';
        }
    }
    ?>
</div>