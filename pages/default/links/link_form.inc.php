<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article>
        <div class="column-66 float-left">              
            <h1 class="sectionTitle"><?= $oPage->title ?></h1>
            <hr>
            <?= $oPage->content ?>
            <br/>
            <form method="post" class="validateForm">
                <input type="hidden" name="action" value="sendForm">
                <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                <table class="withForm contactForm column-100">
                    <tr>
                        <td style="width:120px"><label for="sender">Name or alias *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your name" id="sender" name="sender" type="text" value="<?= _e(http_post('sender')) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="email">E-mail address*</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your e-mail address" data-rule-email="true" data-msg-email="You must enter a valid e-mail address" id="email" name="email" type="text" value="<?= _e(http_post('email')) ?>" /></td>
                    </tr>  
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>                    
                    <tr>
                        <td colspan="2"><b>SHARE LINK</b></td>
                    </tr>
                    <tr>
                        <td><label for="name">Title webpage *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter a title" id="name" name="name" type="text" value="<?= _e(http_post('name')) ?>" /></td>
                    </tr> 
                    <tr>
                        <td><label for="url">URL *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter an url" data-rule-url="true" data-msg-url="You must enter a valid url" id="url" name="url" type="text" value="<?= _e(http_post('url')) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="returnUrl">Return URL *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter a return url" data-rule-url="true" data-msg-url="You must enter a valid return url" id="returnUrl" name="returnUrl" type="text" value="<?= _e(http_post('returnUrl')) ?>" /></td>
                    </tr>  
                    <tr>                        
                        <td class="withLabel">Category</td>
                        <td>
                            <select id="linkCategoryId" name="linkCategoryId" data-rule-required="true" data-msg-required="You must choose a category">
                                <option value="">-- choose a category --</option>
                                <?
                                foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('onlyParent' => 1)) AS $oCategory) {
                                    echo '<option ' . ( http_post('linkCategoryId') == $oCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oCategory->customerLinkCategoryId . '">' . _e($oCategory->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>                      
                    </tr>
                    <tr>
                        <td class="withLabel">Sub-Category</td>
                        <td>
                            <select <?= empty($oParentCategory) ? 'disabled' : '' ?> id="linkSubCategoryId" name="linkSubCategoryId" data-rule-required="true" data-msg-required="You must choose a subcategory">
                                <?
                                if (!empty($oParentCategory)) { ?>                                
                                <option value="">-- choose a subcategory --</option>
                                <?
                                    foreach (CustomerLinkCategoryManager::getCustomerLinkCategoriesByFilter(array('parentId' => $oParentCategory->customerLinkCategoryId)) AS $oSubCategory) {
                                        echo '<option ' . ( http_post('linkSubCategoryId') == $oSubCategory->customerLinkCategoryId ? 'SELECTED' : '') . ' value="' . $oSubCategory->customerLinkCategoryId . '">' . _e($oSubCategory->name) . '</option>';
                                    }
                                }
                                else { ?>
                                    <option value="">-- inapplicable --</option>
                                <?
                                }
                                ?>
                            </select>
                        </td>                     
                    </tr>
                    <tr>
                        <td><label for="message">Short description *</label></td>
                        <td><textarea style="height:200px;resize: none" data-rule-required="true" data-msg-required="You must enter a message" id="message" name="message"><?= _e(http_post('message')) ?></textarea></td>
                    </tr>  
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="g-recaptcha float-right" data-sitekey="6LcS7gwTAAAAAO4Yrym57vvhoJX3z2zZaMVlSRWG"></div>                            
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>                    
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="hidden" name="action" value="sendLink" />
                            <input type="submit" name="done" value="Send" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
</section>
<?
# create necessary javascript
$sURL = '/' . http_get('controller');
$sBottomJavascript = <<<EOT
<script>        
    $('#linkCategoryId').change(function (e) {
        $('#linkSubCategoryId').prop('disabled', true);
        $('#linkSubCategoryId').html('');
        var options = '<option value="">-- inapplicable --</option>';
        
        if ($('#linkCategoryId').val().length) {
            $.ajax(
                {
                    type: "POST",
                    url: "$sURL",
                    data: 'action=ajax-getSubCategories&linkCategoryId=' + $('#linkCategoryId').val(),
                    async: true,
                    success: function (data) {
                        var dataObj = eval( '(' + data + ')');
                        
                        if (dataObj.subCategories.length) {
                            $('#linkSubCategoryId').removeProp('disabled');
                            options = '<option value="">-- choose a subcategory --</option>';
                            $.each(dataObj.subCategories, function (index, sub) {
                                options += '<option value="' + sub.customerLinkCategoryId + '">' + sub.name + '</option>';
                            });
                            $('#linkSubCategoryId').data('rule-required', true);
                            $('.validateForm').validate();
                        }
                        else {
                            $('#linkSubCategoryId').data('rule-required', false);
                            $('.validateForm').validate();
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