<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= (http_get('param3', null) === null) ? http_get('controller') : http_get('param3') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" id="articleForm" action="" class="validateForm">
            <input type="hidden" value="" name="action" id="action"/>
            <input type="hidden" name="articleId" value="<?= $oArticle->articleId ?>"/>
            <input type="hidden" name="worldLeaderLetterId" value="<?= $oWorldLeaderLetter->worldLeaderLetterId ?>"/>
            <fieldset>
                <legend>Letter</legend>

                <table class="withForm">
                    <tr>
                        <td class="withLabel" style="width: 120px;"><label for="title"><?= sysTranslations::get('global_title') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_title_tooltip') ?>">&nbsp;</div></td>
                        <td><input id="title" class="{validate:{required:true}} autofocus default" title="<?= sysTranslations::get('global_title_tooltip') ?>" type="text" autocomplete="off" name="title" value="<?= _e($oArticle->title) ?>" /></td>
                        <td><span class="error"><?= $oArticle->isPropValid("title") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>  
                    <tr>
                        <td class="withLabel"><label for="countryId"><?= ucfirst(sysTranslations::get('letter_addresee_country')) ?></label> *</td>
                        <td>
                            <select id="countryId" class="{validate:{required:true}}" name="countryId"  title="<?= sysTranslations::get('letter_choose_addressee') ?>">
                                <option value="">-- <?= sysTranslations::get('customer_choose_country') ?> --</option>
                                <?php foreach ($aCountries AS $oCountry) { ?>
                                    <option value="<?= $oCountry->countryId ?>" <?= $oCountry->countryId === $iCountryId ? 'selected' : '' ?> ><?= $oCountry->name ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="worldLeaderId"><?= ucfirst(sysTranslations::get('global_addressee')) ?></label> *</td>
                        <td>
                            <select id="worldLeaderId" class="{validate:{required:true}}" name="worldLeaderId"  title="<?= sysTranslations::get('letter_choose_addressee') ?>">
                                <option value="">-- <?= sysTranslations::get('letter_choose_addressee') ?> --</option>
                                <?php foreach ($aWorldLeaders AS $oWorldLeader) { ?>
                                    <option value="<?= $oWorldLeader->worldLeaderId ?>" <?= $oWorldLeader->worldLeaderId === $iWorldLeaderId ? 'selected' : '' ?> ><?= $oWorldLeader->name ?></option>
                                <?php } ?>
                            </select>
                        <td><span class="error"><?= $oWorldLeaderLetter->isPropValid("worldLeaderId") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>                            
                    </tr>
                    
                    <tr>
                        <td class="withLabel"><label for="worldLeaderAddressId"><?= ucfirst(sysTranslations::get('global_address')) ?></label></td>
                        <td>
                            <select id="worldLeaderAddressId" name="worldLeaderAddressId"  title="<?= sysTranslations::get('letter_choose_address') ?>">
                                <option value="">-- <?= sysTranslations::get('letter_choose_address') ?> --</option>
                                <?php foreach ($aAddresses AS $oAddress) { ?>
                                    <option value="<?= $oAddress->worldLeaderAddressId ?>" <?= $oAddress->worldLeaderAddressId === $iWorldLeaderAddressId ? 'selected' : '' ?> ><?= $oAddress->name ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>    
                    <tr>
                        <td class="withLabel" style="padding-top: 10px;"><label for="isLuminary">Voice of Wisdom</label></td>
                        <td colspan="2" style="padding-top: 10px;">
                            <input class="alignRadio" type="radio" name="isLuminary" <?= $oArticle->isLuminary ? 'checked' : '' ?> value="1" > &nbsp;<?= sysTranslations::get('global_yes') ?>

                            <input class="alignRadio" type="radio" name="isLuminary" <?= $oArticle->isLuminary ? '' : 'checked' ?> value="0" >&nbsp;<?= sysTranslations::get('global_no') ?>
                        </td>                        
                    </tr>                    
                    <tr>
                        <td colspan="3" style="padding-top: 20px;"><label for="intro"><?= sysTranslations::get('articles_intro') ?></label> *<div class="hasTooltip tooltip" title="<?= sysTranslations::get('articles_intro_tooltip') ?>">&nbsp;</div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><textarea name="intro" id="intro" class="{validate:{required:true}} tiny_MCE_default tiny_MCE intro" ><?= $oArticle->intro ?></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding-top: 20px;"><label for="content"><?= sysTranslations::get('articles_articles_item_2') ?></label> *<div class="hasTooltip tooltip" title="<?= sysTranslations::get('articles_articles_item_2_tooltip') ?>">&nbsp;</div></td>
                    </tr>
                    <tr>
                        <td colspan="3"><textarea name="content" id="content" class="{validate:{required:true}} tiny_MCE_default tiny_MCE"><?= $oArticle->content ?></textarea></td>
                    </tr>

                    <tr>
                        <td colspan="3"><h2><?= sysTranslations::get('user_seo') ?></h2></td>
                    </tr>
                    <?php if (!empty($oArticle->articleCategoryId)) { ?>
                        <tr>
                            <td><?= sysTranslations::get('global_current_url') ?></td>
                            <td><?= $oArticle->getUrlPath() ?></td>
                        </tr>
                    <?php } ?>                        
                    <tr>
                        <td class="withLabel"><label for="windowTitle"><?= sysTranslations::get('global_window_title') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_title_seo') ?>">&nbsp;</div></td>
                        <td colspan="2">
                            <div class="inline-block">
                                <input class="default charCounterWindowTitle" id="windowTitle" type="text" name="windowTitle" value="<?= _e($oArticle->windowTitle) ?>" />
                                <div id="windowTitleCounter" style="text-align: right;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="metaDescription"><?= sysTranslations::get('global_description') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_description_seo') ?>">&nbsp;</div></td>
                        <td colspan="2">
                            <div class="inline-block">
                                <textarea class="default charCounterMetaDescription" id="metaDescription" name="metaDescription"><?= _e($oArticle->metaDescription) ?></textarea>
                                <div id="metaDescriptionCounter" style="text-align: right;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="metaKeywords"><?= sysTranslations::get('global_keywords') ?></label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_keywords_seo') ?>">&nbsp;</div></td>
                        <td colspan="2"><input  class="default" id="metaKeywords" type="text" name="metaKeywords" value="<?= _e($oArticle->metaKeywords) ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input type="submit" id="saveButton" value="<?= sysTranslations::get('global_save') ?>" name="saveButton" />
                            <?php if (!empty($oArticle->articleId)) { ?>
                                &nbsp;<input type="submit" id="sendToReview" value="<?= sysTranslations::get('articles_send_to_review') ?>" name="sendToReview" />
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
    <?php ?>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_images') ?></legend>
            <?php
            if ($oArticle->articleId !== null) {

                $oImageManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('articles_images_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_files') ?></legend>
            <?php
            if ($oArticle->articleId !== null) {
                $oFileManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('articles_files_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_links') ?></legend>
            <?php
            if ($oArticle->articleId !== null) {
                $oLinkManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('articles_links_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_youtube') ?></legend>
            <?php
            if ($oArticle->articleId !== null) {
                $oYoutubeLinkManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('articles_youtube_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
$jsLang = empty($oCurrentUser) ? 'nl' : $oCurrentUser->getLanguage()->abbr;
$sCurrentUrl = getCurrentUrl();
$sChooseCountry = sysTranslations::get('customer_choose_country');
$sChooseAddressee = sysTranslations::get('letter_choose_addressee');
$sChooseAddress =  sysTranslations::get('letter_choose_address');
$sJavascript = <<<EOT
<script>
    initTinyMCE(".tiny_MCE_default.intro", "/admin/paginas/link-list" , undefined, undefined, undefined, undefined, "$jsLang");
    initTinyMCE(".tiny_MCE_default", "/admin/paginas/link-list", "/admin/articles/image-list/$oArticle->articleId", undefined, undefined, undefined, "$jsLang");
    
    $("#saveButton").click(
        function(e) {
            e.preventDefault();
            $("#action").val("save");
            $("#articleForm").submit();
        }
    );
    
    $("#sendToReview").click(
        function(e) {
            e.preventDefault();
            $("#action").val("sendToReview");
            $("#articleForm").submit();
        }
    ); 
        

    $('#countryId').change(
        function(e) {
            $('#worldLeaderId').html('<option value="">-- $sChooseAddressee --</option>');
            $('#worldLeaderAddressId').html('<option value="">-- $sChooseAddress --</option>');
            
            if ($(this).val() != '') {
                $.ajax(
                    {
                        url: '$sCurrentUrl',
                        type:'POST',
                        data: 'action=ajax-getLeaders&countryId=' + $(this).val(),
                        async: true,
                        success: function(data){
                            var dataObj = eval('(' + data + ')');

                            var optLeader = '<option value="">-- $sChooseAddressee --</option>';

                            /* On success */
                            if (dataObj.success == true){
                                $.each(
                                    dataObj.leaders, 
                                    function(index, leader) {
                                        optLeader = optLeader + '<option value="' + leader.worldLeaderId + '">' + leader.name + '</option>';
                                    }
                                );                    
                            } 

                            $('#worldLeaderId').html(optLeader);
                        }        
                        
                    }
                );
            }
        }
   );
        
    $('#worldLeaderId').change(
        function(e) {
            $('#worldLeaderAddressId').html('<option value="">-- $sChooseAddress --</option>');
            
            if ($(this).val() != '') {
                $.ajax(
                    {
                        url: '$sCurrentUrl',
                        type:'POST',
                        data: 'action=ajax-getAddresses&worldLeaderId=' + $(this).val(),
                        async: true,
                        success: function(data){
                            var dataObj = eval('(' + data + ')');

                            var optAddress = '<option value="">-- $sChooseAddress --</option>';

                            /* On success */
                            if (dataObj.success == true){
                                $.each(
                                    dataObj.addresses, 
                                    function(index, address) {
                                        optAddress = optAddress + '<option value="' + address.worldLeaderAddressId + '">' + address.name + '</option>';
                                    }
                                );                    
                            } 

                            $('#worldLeaderAddressId').html(optAddress);
                        }        
                        
                    }
                );
            }
        }
   );        
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>