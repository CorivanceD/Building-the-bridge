<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" id="articleForm" action="" class="validateForm">
            <input type="hidden" value="save" name="action" id="action"/>
            <fieldset>
                <legend><?= sysTranslations::get('petitions_petition') ?></legend>
                <table class="withForm">
                    <tr>
                        <td style="width: 150px;"><?= sysTranslations::get('petitions_active') ?> *</td>
                        <td colspan="2">
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('petitions_active_tooltip') ?>" type="radio" <?= $oPetition->online ? 'CHECKED' : '' ?> id="online_1" name="online" value="1" /> <label for="online_1"><?= sysTranslations::get('global_yes') ?></label>
                            <input class="alignRadio {validate:{required:true}}" title="<?= sysTranslations::get('petitions_active_tooltip') ?>" type="radio" <?= !$oPetition->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0"><?= sysTranslations::get('global_no') ?></label>
                        </td>
                    </tr>                    
                    <tr>
                        <td class="withLabel" ><label for="author"><?= sysTranslations::get('global_author') ?></label> </td>
                        <td colspan="2"><input id="author"  type="text" autocomplete="off" disabled value="<?= $oPetition->author ?>" /></td>                        
                    </tr>   
                    <tr>
                        <td class="withLabel" ><label for="email"><?= sysTranslations::get('global_email') ?></label> </td>
                        <td colspan="2"><input id="email"  type="text" autocomplete="off" disabled value="<?= $oPetition->email ?>" /></td>                        
                    </tr>                    
                    <tr>
                        <td class="withLabel" ><label for="phone"><?= sysTranslations::get('global_phone') ?></label> </td>
                        <td colspan="2"><input id="phone"  type="text" autocomplete="off" disabled value="<?= $oPetition->phone ?>" /></td>                        
                    </tr>                    
                    <tr>
                        <td class="withLabel" ><label for="countryId"><?= sysTranslations::get('global_country') ?></label> </td>
                        <td colspan="2"><input id="countryId"  type="text" autocomplete="off" disabled value="
                            <?php 
                        if (!empty($oPetition->countryId)) { 
                            $oCountry = CountryManager::getCountry($oPetition->countryId); 
                            echo $oCountry->name;                                     
                        }
                        else {
                            echo '';
                        }
                        ?>" /></td>                        
                    </tr>                                        
                    <tr>
                        <td class="withLabel" ><label for="title"><?= sysTranslations::get('global_title') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_title_tooltip') ?>">&nbsp;</div></td>
                        <td><input id="title" class="{validate:{required:true}} autofocus default" title="<?= sysTranslations::get('global_title_tooltip') ?>" type="text" autocomplete="off" name="title" value="<?= _e($oPetition->title) ?>" /></td>
                        <td><span class="error"><?= $oPetition->isPropValid("title") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px;"><label for="intro"><?= sysTranslations::get('articles_intro') ?></label> *<div class="hasTooltip tooltip" title="<?= sysTranslations::get('articles_intro_tooltip') ?>">&nbsp;</div></td>
                        <td style="padding-top: 20px;" colspan="2"><span class="error"><?= $oPetition->isPropValid("intro") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3"><textarea  name="intro" id="intro" class="{validate:{required:true}} tiny_MCE_default tiny_MCE intro"><?= $oPetition->intro ?></textarea></td>
                    </tr>                    
                    <tr>
                        <td style="padding-top: 20px;"><label for="description"><?= sysTranslations::get('global_description') ?></label> *<div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_description_tooltip') ?>">&nbsp;</div></td>
                        <td style="padding-top: 20px;" colspan="2"><span class="error"><?= $oPetition->isPropValid("description") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>                        
                    </tr>
                    <tr>
                        <td colspan="3"><textarea  name="description" id="description" class="{validate:{required:true}} tiny_MCE_default tiny_MCE intro"><?= $oPetition->description ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="requiredSignatures"><?= sysTranslations::get('petitions_required_signatures') ?> *</label> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('petitions_required_signatures_tooltip') ?>">&nbsp;</div></td>
                        <td><input id="requiredSignatures" class="{validate:{required:true,min:1,number:true}} autofocus default" title="<?= sysTranslations::get('petitions_required_signatures_tooltip') ?>" type="text" autocomplete="off" name="requiredSignatures" value="<?= _e($oPetition->requiredSignatures) ?>" /></td>
                        <td><span class="error"><?= $oPetition->isPropValid("requiredSignatures") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input type="submit" id="saveButton" value="<?= sysTranslations::get('global_save') ?>" name="saveButton" />
                            <?php if (http_get('param1') === 'bewerken') { ?>
                            <input type="button" id="createReport" value="Generate report" name="createReport" />
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>
    </div>   
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_images') ?></legend>
            <?php
            if ($oPetition->petitionId !== null) {
                $oImageManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('petitions_images_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
$sReportUrl = CLIENT_HTTPS_URL.'/admin/'.http_get('controller').'/export/'.http_get('param2');
$jsLang = empty($oCurrentUser) ? 'nl' : $oCurrentUser->getLanguage()->abbr;
$oPageLayout->addJavascriptBottom('
<script>
    initTinyMCE(".tiny_MCE_default", undefined, undefined, undefined, undefined, "' . $jsLang . '");
        
    $("#createReport").click(
        function (e) {
            window.location = "'.$sReportUrl.'";
            e.preventDefault();
        }
    );
</script>
');
?>