<table>
    <tr>
        <td colspan="2" style="white-space: nowrap;"><h1><?= sysTranslations::get('formbackups_details') ?>: `<?= $oFormBackup->name ?>`</h1></td>
    </tr>
    <tr>
        <td><b><?= sysTranslations::get('global_field_name') ?></b></td>
        <td><b><?= sysTranslations::get('global_value') ?></b></td>
    </tr>
    <?php
    foreach ($oFormBackup->getFormData() AS $sInputName => $sValue) {
        echo '<tr><td>' . $sInputName . '</td><td>' . $sValue . '</td></tr>';
    }
    ?>
</table>