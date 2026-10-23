<div id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('user_user') ?></legend>
            <form method="POST" action="" class="validateForm">
                <input type="hidden" value="save" name="action" />
                <table class="withForm">
                    <tr>
                        <td class="withLabel" style="width: 120px;"><label for="name"><?= sysTranslations::get('global_name') ?> *</label></td>
                        <td><input id="name" class="{validate:{required:true}} autofocus" title="<?= sysTranslations::get('user_enter_username_tooltip') ?>" type="text" autocomplete="off" name="name" value="<?= $oUser->name ?>" /></td>
                        <td><span class="error"><?= $oUser->isPropValid("name") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="username"><?= sysTranslations::get('user_username') ?> *</label></td>
                        <td><input id="username" class="{validate:{required:true,remote:'<?= ADMIN_FOLDER ?>/gebruikers/ajax-checkUsername?userId=<?= $oUser->userId ?>'}}" title="<?= sysTranslations::get('user_unique_username_tooltip') ?>" type="text" autocomplete="off" name="username" value="<?= $oUser->username ?>" /></td>
                        <td><span class="error"><?= $oUser->isPropValid("username") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="email"><?= sysTranslations::get('user_email') ?> *</label></td>
                        <td><input id="email" class="{validate:{required:true,email:true,remote:'<?= ADMIN_FOLDER ?>/gebruikers/ajax-checkEmail?userId=<?= $oUser->userId ?>'}}" title="<?= sysTranslations::get('user_email_tooltip') ?>" type="email" autocomplete="off" name="email" value="<?= $oUser->email ?>" /></td>
                        <td><span class="error"><?= $oUser->isPropValid("email") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>                    
                    <tr>
                        <td class="withLabel"><label for="password"><?= sysTranslations::get('user_new_password') ?> *</label></td>
                        <td><input id="password" class="<?= $oUser->userId ? '{validate:{password:true}}' : '{validate:{required:true,password:true}}' ?>" title="<?= sysTranslations::get('user_secure_password_tooltip') ?>" autocomplete="off" type="text" name="password" value="" /><em>&nbsp;(** <?= sysTranslations::get('user_password_empty') ?>)</em></td>
                        <td><span class="error"><?= $oUser->isPropValid("password") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                    </tr>
                    <tr>
                        <td class="withLabel"><label for="description"><?= sysTranslations::get('global_description') ?> *</label></td>
                        <td colspan="2"><textarea maxlength="255" rows="10" cols="26" id="description" title="" name="description"><?= $oUser->description ?></textarea></td>
                    </tr>  
                    <?php
                    if ((intval($oCurrentUser->userProfile) == User::USER_CLIENT_ADMIN || $oCurrentUser->username === 'admin') && $oUser->username !== 'admin') { ?>
                        <tr>
                            <td class="withLabel"><label for="userProfile"><?= sysTranslations::get('user_profile') ?> *</label></td>
                            <td colspan="2">
                                <select id="userProfile" name="userProfile" class="{validate:{required:true}}" >
                                        <option value="">-- <?= sysTranslations::get('user_select_profile') ?> --</option>                                
                                        <option value="<?= User::USER_CLIENT_ADMIN ?>" <?= intval($oUser->userProfile) == User::USER_CLIENT_ADMIN ? 'selected' : '' ?> >Super user</option>
                                        <option value="<?= User::USER_EDITOR ?>" <?= intval($oUser->userProfile) == User::USER_EDITOR ? 'selected' : '' ?> >Editor</option>
                                        <option value="<?= User::USER_WRITER ?>" <?= intval($oUser->userProfile) == User::USER_WRITER ? 'selected' : '' ?> >Writer</option>                                   
                                </select>
                            </td>
                        </tr>
                    <?php } ?>                    
                    <?php if ($oCurrentUser->isAdmin() || intval($oCurrentUser->userProfile) == User::USER_CLIENT_ADMIN ) { ?>
                        <tr id="editorRow" style="<?= (intval($oUser->userProfile) == User::USER_WRITER) ? '' : 'display:none' ?>">
                            <td class="withLabel"><label for="editorId"><?= sysTranslations::get('user_editor') ?></label> *</td>
                            <td colspan="2">                           
                                <select name="editorId" class="{validate:{required:true}}">
                              
                                        <option value="">-- <?= sysTranslations::get('user_select_editor') ?> --</option>
                                        <?php foreach ($aEditors AS $oEditor) { ?>
                                            <option value="<?= $oEditor->userId ?>" <?= $oUser->editorId == $oEditor->userId ? 'selected' : '' ?> ><?= $oEditor->name ?></option>
                                        <?php } ?>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($oUser->userId == $oCurrentUser->userId && !$oCurrentUser->isAsideAdmin() && empty($oCurrentUser->userProfile)) { ?>
                        <tr>
                            <td colspan="3"><b class="errorColor"><i><?= sysTranslations::get('user_put_out_module_warning') ?></i></b></td>
                        </tr>
                    <?php } ?>
                    <?php if ($oCurrentUser->isAdmin()) { ?>
                        <tr>
                            <td><?= sysTranslations::get('global_modules') ?></td>
                            <td>
                                <?php
                                foreach ($aModules AS $oModule) {
                                    echo '<input class="alignCheckbox" type="checkbox" id="module_' . $oModule->moduleId . '" name="moduleIds[' . $oModule->name . ']" ' . ($oUser->hasRightsFor($oModule->name) ? 'CHECKED' : '') . ' value="' . $oModule->moduleId . '" /> <label for="module_' . $oModule->moduleId . '">' . sysTranslations::get($oModule->linkName) . '</label><br />';
                                }
                                ?>
                            </td>
                        </tr>                    
                        <tr>
                            <td><?= sysTranslations::get('user_admin_rights') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('user_all_admin_rights_tooltip') ?>" type="radio" <?= $oUser->getAdministrator() ? 'CHECKED' : '' ?> id="administrator_1" name="administrator" value="1" /> <label for="administrator_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('user_all_admin_rights_tooltip') ?>" type="radio" <?= !$oUser->getAdministrator() ? 'CHECKED' : '' ?> id="administrator_0" name="administrator" value="0" /> <label for="administrator_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oUser->isPropValid("administrator") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>   

<!--                        <tr>
                            <td><?= sysTranslations::get('user_client_admin_rights') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('user_client_admin_rights_tooltip') ?>" type="radio" <?= intval($oUser->userProfile) == User::USER_CLIENT_ADMIN ? 'CHECKED' : '' ?> id="userProfile_1" name="userProfile" value="<?= User::USER_CLIENT_ADMIN ?>" /> <label for="userProfile_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('user_client_admin_rights_tooltip') ?>" type="radio" <?= intval($oUser->userProfile) != User::USER_CLIENT_ADMIN ? 'CHECKED' : '' ?> id="userProfile_0" name="userProfile" value="" /> <label for="userProfile_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oUser->isPropValid("userProfile") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>                         -->
                    <?php } ?>                        
                    <?php if ($oCurrentUser->isAdmin() || $oCurrentUser->isSEO()) { ?>
                        <tr>
                            <td><?= sysTranslations::get('user_seo_rights') ?></td>
                            <td>
                                <input class="alignRadio" title="<?= sysTranslations::get('user_all_seo_rights') ?>" type="radio" <?= $oUser->getSeo() ? 'CHECKED' : '' ?> id="seo_1" name="seo" value="1" /> <label for="seo_1"><?= sysTranslations::get('global_yes') ?></label>
                                <input style="margin-left: 5px;" class="alignRadio" title="<?= sysTranslations::get('user_all_seo_rights') ?>" type="radio" <?= !$oUser->getSeo() ? 'CHECKED' : '' ?> id="seo_0" name="seo" value="0" /> <label for="seo_0"><?= sysTranslations::get('global_no') ?></label>
                            </td>
                            <td><span class="error"><?= $oUser->isPropValid("global_seo") ? '' : sysTranslations::get('global_field_not_completed') ?></span></td>
                        </tr>
                    <?php } ?>
                    <?php if ($oCurrentUser->isAdmin() || $oCurrentUser->userProfile == User::USER_CLIENT_ADMIN || $oCurrentUser->userProfile == User::USER_EDITOR) { ?>
                        <tr>
                            <td><label for="systemLanguageId"><?= sysTranslations::get('user_language') ?></label> * </td>
                            <td><select name="systemLanguageId" class="{validate:{required:true}}">
                                    <option value=""><?= sysTranslations::get('user_choose_language') ?></option>
                                    <?php foreach (SystemLanguageManager::getLanguagesByFilter() AS $oSystemLanguage) { ?>
                                        <option value="<?= $oSystemLanguage->systemLanguageId ?>" <?= ($oUser->systemLanguageId === $oSystemLanguage->systemLanguageId) ? 'selected' : '' ?> ><?= $oSystemLanguage->name ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="2">
                            <input type="submit" value="<?= sysTranslations::get('global_save') ?>" name="save" />
                        </td>
                    </tr>
                </table>
            </form>
        </fieldset>
    </div>
    <div class="contentColumn">
        <fieldset>
            <legend><?= sysTranslations::get('global_image') ?></legend>
            <?php
            if ($oUser->userId !== null) {
                $oImageManagerHTML->includeTemplate();
            } else {
                echo '<p><i>' . sysTranslations::get('user_image_warning') . '</i></p>';
            }
            ?>
        </fieldset>
    </div>    
</div>
<div id="bottomOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>"><?= sysTranslations::get('global_back_to_overview') ?></a><span class="backBtnInfo"> (<?= sysTranslations::get('global_without_saving') ?>)</span>
</div>
<?php
# add ajax code for online/offline handling
$sRandomPasswordJavascript = <<<EOT
<script>
    // randompassword to password field
    $("#password").randomPass();
    $("#userProfile").change(
        function() {
            var userProfile = $(this).val();
            
            if (userProfile == 30) {
                $('#editorRow').show();
            }
            else {
                $('#editorRow').hide();
            }
        }
    );
</script>
EOT;
$oPageLayout->addJavascriptBottom($sRandomPasswordJavascript);
?>