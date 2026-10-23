<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?=  sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <fieldset>
                <legend><?= sysTranslations::get('news_source_logo') ?></legend>
                <table class="withForm">
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_name') ?> *</label></td>
                        <td><input size="50" id="name" class="{validate:{required:true}}" title="News source name" type="text" name="sourceName" value="<?= _e($oNewsSourceLogo->sourceName) ?>" /></td>
                        <td><span class="error"><?= $oNewsSourceLogo->isPropValid("sourceName") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
        <div class="contentColumn">
            <fieldset>
                <legend><?= sysTranslations::get('global_images') ?></legend>
                <?
                if ($oNewsSourceLogo->newsSourceLogoId !== null) {
                    $oImageManagerHTML->includeTemplate();
                } else {
                    echo '<p><i>' . sysTranslations::get('pages_images_warning') . '</i></p>';
                }
                ?>
            </fieldset>
        </div>    
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?=  sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>