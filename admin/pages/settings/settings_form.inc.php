<h1><?= sysTranslations::get('settings_settings') ?></h1>
<form method="POST" action="" class="validateForm" enctype="multipart/form-data">
    <input type="hidden" value="save" name="action" />
    <table class="withForm">
        <tr>
            <td style="padding-top: 10px;" colspan="2"><b><?= sysTranslations::get('settings_contact') ?></b></td>
        </tr>
        <tr>
            <td class="withLabel" style="width: 220px;"><label for="clientName"><?= sysTranslations::get('global_name') ?></label> *</td>
            <td>
                <input class="{validate:{required:true}} default" title="<?= sysTranslations::get('settings_name_tooltip') ?>" type="text" id="clientName" name="settings[clientName]" value="<?= _e(Settings::get('clientName')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="clientStreet"><?= sysTranslations::get('global_street') ?></label> *</td>
            <td>
                <input class="{validate:{required:true}}" title="<?= sysTranslations::get('settings_street_tooltip') ?>" type="text" id="clientStreet" name="settings[clientStreet]" size="20" value="<?= _e(Settings::get('clientStreet')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="clientPostalCode"><?= sysTranslations::get('global_postal_code') ?></label> *</td>
            <td>
                <input class="{validate:{required:true}}" title="<?= sysTranslations::get('settings_postal_code_tooltip') ?>" type="text" id="clientPostalCode" name="settings[clientPostalCode]" size="6" value="<?= _e(Settings::get('clientPostalCode')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="clientCity"><?= sysTranslations::get('global_city') ?></label> *</td>
            <td>
                <input class="{validate:{required:true}}" title="<?= sysTranslations::get('settings_city_tooltip') ?>" type="text" id="clientCity" name="settings[clientCity]" size="20" value="<?= _e(Settings::get('clientCity')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="contactLatitude"><?= sysTranslations::get('settings_latitude') ?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('settings_latitude_tooltip') ?>">&nbsp;</div></label></td>
            <td>
                <input title="<?= sysTranslations::get('settings_latitude_tooltip') ?>" type="text" id="contactLatitude" name="settings[contactLatitude]" size="20" value="<?= _e(Settings::get('contactLatitude')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="contactLongitude"><?= sysTranslations::get('settings_longitude') ?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('settings_longitude_tooltip') ?>">&nbsp;</div></label></td>
            <td>
                <input title="<?= sysTranslations::get('settings_longitude_tooltip') ?>" type="text" id="contactLongitude" name="settings[contactLongitude]" size="20" value="<?= _e(Settings::get('contactLongitude')) ?>" />
            </td>
        </tr>
        <tr>
            <td class="withLabel"><label for="socialMediaFacebook"><?= sysTranslations::get('settings_facebook_link') ?></label></td>
            <td>
                <input title="<?= sysTranslations::get('settings_facebook_link_tooltip') ?>" type="text" id="socialMediaFacebook" name="settings[socialMediaFacebook]" size="20" value="<?= _e(Settings::get('socialMediaFacebook')) ?>" />
            </td>
        </tr>        
        <? if ($oCurrentUser->isAdmin()) { ?>
            <tr>
                <td style="padding-top: 10px;" colspan="2"><b><?= sysTranslations::get('settings_admin') ?></b></td>
            </tr>
            <tr>
                <td><?= sysTranslations::get('settings_page_levels') ?></td>
                <td>
                    <input class="{validate:{required:true,digits:true}} default" id="pagesMaxLevels" name="settings[pagesMaxLevels]" style="width: 30px;" type="text" value="<?= _e(Settings::get('pagesMaxLevels')) ?>" />
                </td>
            </tr>   
        <? } ?>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
            </td>
        </tr>
    </table>
</form>