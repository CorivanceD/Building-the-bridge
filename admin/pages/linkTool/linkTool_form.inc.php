<div class="cf" id="topOptions">
    <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar het linken overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
</div>
<div class="cf">
    <div class="contentColumn">
        <form method="POST" action="" class="validateForm">
            <input type="hidden" value="save" name="action" />
            <fieldset>
                <legend>Link</legend>
                <table class="withForm">
                    <tr>
                        <td>Online *</td>
                        <td>
                            <input class="alignRadio {validate:{required:true}}" title="" type="radio" <?= $oCustomerLink->online ? 'CHECKED' : '' ?> id="online_1" name="online" value="1" /> <label for="online_1">Ja</label>
                            <input class="alignRadio {validate:{required:true}}" title="" type="radio" <?= !$oCustomerLink->online ? 'CHECKED' : '' ?> id="online_0" name="online" value="0" /> <label for="online_0">Nee</label>
                        </td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("online") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>
                    <!-- Name -->
                    <tr>
                        <td class="withLabel"><label for="name">Naam *</label></td>
                        <td><input id="name" class="{validate:{required:true}} default" title="Vul de naam in" type="text" name="name" value="<?= _e($oCustomerLink->name) ?>" /></td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("name") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>

                    <!-- Sender -->
                    <tr>
                        <td class="withLabel"><label for="sender">Verzender *</label></td>
                        <td><input id="sender" class="{validate:{required:true}} default" title="Vul de verzender in" type="text" name="sender" value="<?= _e($oCustomerLink->sender) ?>" /></td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("sender") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>                    

                    <!-- Url -->
                    <tr>
                        <td class="withLabel"><label for="url">Url *</label></td>
                        <td><input id="url" class="{validate:{required:true,url:true}} default" title="Vul de url in" type="text" name="url" value="<?= _e($oCustomerLink->url) ?>" /></td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("url") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>    

                    <!-- Return Url -->
                    <tr>
                        <td class="withLabel"><label for="returnUrl">Return Url *</label></td>
                        <td><input id="url" class="{validate:{required:true,url:true}} default" title="Vul de return url in" type="text" name="returnUrl" value="<?= _e($oCustomerLink->returnUrl) ?>" /></td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("returnUrl") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                    </tr>                    

                    <!-- Category -->
                    <tr>
                        <td class="withLabel">Categorie</td>
                        <td>
                            <select id="linkCategoryId" name="linkCategoryId" class="{validate:{required:true,url:true}} default">
                                <option value="">-- alle categorieën --</option>
                                <?php
                                foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('onlyParent' => 1)) AS $oCategory) {
                                    echo '<option ' . ( $iMainCategoryId == $oCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oCategory->customerLinkCategoryId . '">' . _e($oCategory->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("aCategories") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>                        
                    </tr>
                    <tr>
                        <td class="withLabel">Subcategorie</td>
                        <td>
                            <select  id="linkSubCategoryId" name="linkSubCategoryId"
                            <?= (!empty($oParentCategory) && CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('parentId' => $oParentCategory->customerLinkCategoryId)) != null) ? 'class="{validate:{required:true}} default"' : ''
                            ?>>
                                <option value="">-- alle subcategorieën --</option>
                                <?php
                                if (!empty($oParentCategory)) {
                                    foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('parentId' => $oParentCategory->customerLinkCategoryId)) AS $oSubCategory) {
                                        echo '<option ' . ( $iSubCategoryId == $oSubCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oSubCategory->customerLinkCategoryId . '">' . _e($oSubCategory->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><span class="error"><?= $oCustomerLink->isPropValid("aCategories") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>                        
                    </tr>
                    <!-- Message  -->
                    <tr>
                        <td class="withLabel"><label for="message">Bericht *</label></td>
                        <td><textarea style="width:316px; height:100px; resize:none" id="message" name="message" class="{validate:{required:true}"><?= $oCustomerLink->message ?></textarea></td>
                                                                                                    <td><span class="error"><?= $oCustomerLink->isPropValid("message") ? '' : 'Veld niet (juist) ingevuld' ?></span></td>
                        </tr>                     
                        <tr>
                            <td colspan="3">
                                <input id="saveLinkButton" type="submit" value="Opslaan" name="save" />
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </div>
        <div id="bottomOptions">
            <a class="backBtn" href="<?= ADMIN_FOLDER ?>/<?= http_get('controller') ?>">Terug naar het linken overzicht</a><span class="backBtnInfo"> (zonder opslaan)</span>
        </div>
    </div>

    <?php
# create necessary javascript
    $sURL = '/admin/'.http_get('controller');
    $sBottomJavascript = <<<EOT
<script>        
    $('#linkCategoryId').change(function (e) {
        $('#linkSubCategoryId').html('');
        var options = '<option value="">-- alle subcategorieën --</option>';
        
        if ($('#linkCategoryId').val().length) {
            $.ajax(
                {
                    type: "POST",
                    url: "$sURL",
                    data: 'action=getSubCategories&linkCategoryId=' + $('#linkCategoryId').val() + '&ajax=1',
                    async: true,
                    success: function (data) {
                        var dataObj = eval( '(' + data + ')');
                        
                        if (dataObj.subCategories.length) {
                            $.each(dataObj.subCategories, function (index, sub) {
                                options += '<option value="' + sub.customerLinkCategoryId + '">' + sub.name + '</option>';
                            });
                            $('#linkSubCategoryId').addClass('{validate:{required:true}}');
                            $('.validateForm').validate();
                        }
                        else {
                            $('#linkSubCategoryId').removeClass('{validate:{required:true}}');
                        }
            
                        $('#linkSubCategoryId').html(options);
                    }
                }
            );
        }
        else {
            $('#linkSubCategoryId').html(options);
        }
    });
</script>
EOT;
    $oPageLayout->addJavascriptBottom($sBottomJavascript);
    ?>