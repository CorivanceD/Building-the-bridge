<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />            
            <fieldset>
                <legend><?= sysTranslations::get('articles_category') ?></legend>
                <table class="withForm">
                    <tr>
                        <td>Online *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('articles_category_set_online') ?>" type="radio" <?= $oArticleCategory->online ? 'CHECKED' : '' ?> id="articles_categoryonline_1" name="online" value="1" /> <label for="online_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('articles_category_set_online') ?>" type="radio" <?= !$oArticleCategory->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                        <td><span class="error"><?= $oArticleCategory->isPropValid("online") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel" style="width: 116px;"><label for="name"><?= sysTranslations::get('pages_page') ?></label> *</td>
                        <td colspan="2">                            
                            <select id="parentPageId" name="parentPageId" class="{validate:{required:true}}">
                                <option value="">-- None --</option>
                            <?php  foreach ($aPages AS $oPage) { ?>
                                   <option value="<?= $oPage->pageId ?>" <?= $oPage->pageId == $oArticleCategory->parentPageId ? 'selected' : '' ?>><?= $oPage->title ?></option>
       
                            <?php  } ?>
                            </select>
                        </td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="name"><?= sysTranslations::get('global_parent') ?></label></td>
                        <td colspan="2">
                            <select id="parentCategoryId" name="parentCategoryId">
                                <option value="">-- None --</option>
                                <?php
                                foreach ($aParentCategories AS $oParentCategory) {
                                    if ($oParentCategory->articleCategoryId != $oArticleCategory->articleCategoryId) {
                                        ?>
                                        <option value="<?= $oParentCategory->articleCategoryId ?>" <?= $oParentCategory->articleCategoryId == $oArticleCategory->parentCategoryId ? 'selected' : '' ?>><?= $oParentCategory->name ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="withLabel" style="width: 116px;"><label for="name"><?= sysTranslations::get('global_name') ?> *</label></td>
                        <td><input id="name" class="{validate:{required:true}} autofocus default" name="name" title="<?= sysTranslations::get('global_set_name') ?>" type="text" value="<?= _e($oArticleCategory->name) ?>" /></td>
                        <td><span class="error"><?= $oArticleCategory->isPropValid("name") ? '' : sysTranslations::get('global_field_incorrect') ?></span></td>
                    </tr>
                    <?php if ($oCurrentUser->isSEO()) { ?>
                        <tr>
                            <td colspan="3" style="padding-top: 10px;"><h2><?= sysTranslations::get('user_seo') ?></h2></td>
                        </tr>
                        <?php if ($oArticleCategory->articleCategoryId) { ?>
                            <tr>
                                <td><?= sysTranslations::get('global_current_url') ?></td>
                                <td><?= $oArticleCategory->getUrlPath() ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class="withLabel"><label for="windowTitle"><?= sysTranslations::get('global_window_title') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_title_seo') ?>">&nbsp;</div></td>
                            <td colspan="2">
                                <div class="inline-block">
                                    <input class="default charCounterWindowTitle" id="windowTitle" type="text" name="windowTitle" value="<?= _e($oArticleCategory->windowTitle) ?>" />
                                    <div id="windowTitleCounter" style="text-align: right;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="metaDescription"><?= sysTranslations::get('global_description') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_description_seo') ?>">&nbsp;</div></td>
                            <td colspan="2">
                                <div class="inline-block">
                                    <textarea cols="34" rows="5" class="charCounterMetaDescription default" id="metaDescription" name="metaDescription"><?= _e($oArticleCategory->metaDescription) ?></textarea>
                                    <div id="metaDescriptionCounter" style="text-align: right;"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="withLabel"><label for="metaKeywords"><?= sysTranslations::get('global_keywords') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_keywords_seo') ?>">&nbsp;</div></td>
                            <td colspan="2"><input class="default" id="metaKeywords" type="text" name="metaKeywords" value="<?= _e($oArticleCategory->metaKeywords) ?>" /></td>
                        </tr>
                        <tr>
                            <td class="withLabel"><label for="urlPartText"><?= sysTranslations::get('global_seo_url') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_seo_url_tooltip') ?> <?= CLIENT_HTTPS_URL ?>/nieuws/<b>[url tekst]</b>">&nbsp;</div></td>
                            <td><input class="default" id="urlPart" type="text" name="urlPartText" value="<?= _e($oArticleCategory->getUrlPartText()) ?>" /></td>
                            <td></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
$sCurrentUrl = getCurrentUrl();
$sJavascript = <<<EOT
<script>
        $('#parentPageId').change(function() {
            $.ajax({
                type: 'POST',
                url: '$sCurrentUrl',
                data: 'action=ajaxChangePage&page=' + $(this).val(),
                async: true,
                success: function(data){
                    var dataObj = eval('(' + data + ')');
                    var optCat = '<option value="">-- None --</option>';

                    /* On success */
                    if (dataObj.success == true){
                        $.each(
                            dataObj.categories, 
                            function(index, cat) {
                                optCat = optCat + '<option value="' + cat.articleCategoryId + '">' + cat.name + '</option>';
                            }
                        );                    
                    } 

                    $('#parentCategoryId').html(optCat);
                }
            });
        });
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>