<section style="margin-top: 32px;">
    <article >
        <div class="column-66 float-left">
            <h3>SIGN THE PETITION</h3>
            <hr>
            <div class="statusUpdate signatureStatusUpdate"></div>  
            <h2 class="articleTitle"><?= _e($oPetition->title) ?></h2>   
            <?
            $aSignatures = $oPetition->getSignatures();
            $oPetitionImage = $oPetition->getImage();

            if (!empty($oPetitionImage)) {
                ?>
                <img class="articleHeadImage" src="<?= $oPetitionImage->getImageFileByReference('detail')->link ?>" alt="alt="<?= empty($oPetitionImage->getImageFileByReference('detail')->title) ? $oPetition->title : $oPetitionImage->getImageFileByReference('detail')->title ?>"" <?= $oPetitionImage->getImageFileByReference('detail')->imageSizeAttr ?> >
            <? } ?>              
            <p>We're almost there, help us get <b><?= $oPetition->requiredSignatures ?></b></p>
            <div class="signatures" style="vertical-align: top">                
                <div id="progressBar" class="jquery-ui-like"><div style="background-color: #ffbc00"></div></div>
            </div>
            <form method="POST">
                <div id="signPetition" style="text-align:left; margin-top: 20px;" >                    
                    <input type="hidden" name="action" value="sign" >
                    <input type="hidden" name="petitionId" value="<?= $oPetition->petitionId ?>">
                    <input type="submit" name="signIt" id="signIt" value="Click here to SIGN the Petition">
                    <div style="display: none;">
                        <input type="text" name="rumpelstiltskin-empty" value="" />
                        <input type="text" name="rumpelstiltskin-filled" value="repelsteeltje" />
                    </div>
                </div>                    
            </form>
            <br>
            <div class="articleContent">
                <?= $oPetition->description ?>
            </div>           
            <form method="POST">
                <div id="signPetition" style="text-align:left" >                    
                    <input type="hidden" name="action" value="sign" >
                    <input type="hidden" name="petitionId" value="<?= $oPetition->petitionId ?>">
                    <textarea style="width:90%" id="comment1" name="comment" rows="9" maxlength="255" placeholder="Leave a comment, please"><?= _e(http_post('comment')) ?></textarea><br><br>
                    <input type="submit" name="signIt" id="signIt" value="Click here to SIGN the Petition">
                    <div style="display: none;">
                        <input type="text" name="rumpelstiltskin-empty" value="" />
                        <input type="text" name="rumpelstiltskin-filled" value="repelsteeltje" />
                </div>                    
                </div>                    
            </form>
            <? if (!empty($aSignatures)) { ?>
                <br>
                <div class="comments">
                    <h3>LATEST COMMENTS</h3>
                    <hr>                
                    <?
                    $iComments = 0;

                    foreach ($aSignatures AS $oSignature) {
                        if ($iComments === 4) {
                            break;
                        }
                        //if (!empty($oSignature->comment)) {
                            $iComments++;
                            ?>
                            <div class="articleComment">
                                <img alt="No Photo" src="<?= empty($oSignature->getCustomer()->imageId) ? '/images/layout/no_user_photo.jpg' : $oSignature->getCustomer()->getImage()->getImageFileByReference('cms_thumb')->link ?>" width="100" height="100">
                                <div class="commentContent">
                                    <h3><?= $oSignature->getCustomer()->name ?></h3>
                                    <div class="postedOn">Sign on <?= Date::strToDate($oSignature->date)->format('%A, %B %d, %Y') ?></div>
                                    <p><?= $oSignature->comment ?></p>
                                    <br>
                                </div>
                            </div>
                        <? //} ?>
                        <? } ?>
                </div>  
            <? } ?>
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
</section>
<? if (empty($oCurrentCustomer)) { ?>
    <div id="signerForm">
        <div class="withForm contactForm signerForm">
            <p>Please, enter the required info or <a href="/account" style="text-decoration: underline">login</a> to sign the petition.</p>
            <form method="POST" class="validateForm">
                <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                <input type="hidden" name="action" value="sign">
                <input type="hidden" name="petitionId" value="<?= $oPetition->petitionId ?>">
                <input type="hidden" id="comment2" name="comment" value="<?= _e(http_post('comment')) ?>">
                <p><label for="alias"><b>Your name or alias (no need for real name) *</b></label></p>
                <input type="text" id="alias" name="alias" value="" data-rule-required="true" data-msg-required="You must enter your name or alias" value="<?= _e(http_post('alias')) ?>"> 
                <p><label for="alias"><b>Email *</b></label></p>
                <input type="text" id="email" name="email" value="" data-rule-required="true" data-msg-required="You must enter your email address" data-rule-email="true" data-msg-email="You must enter a valid e-mail address" data-rule-remote="/account?ajax=checkEmail" data-msg-remote="There already was a registered user with that e-mail address. Please log in or use another e-mail address" value="<?= _e(http_post('email')) ?>">
                <p><label for="gender"><b>Gender *</b></label><p>
                    <label><input id="gender_M" type="radio" name="gender" value="M" <?= http_post('gender') === 'M' ? 'checked' : '' ?> data-rule-required="true"  data-rule-required="true" data-msg-required="You must enter your gender"/> Male</label>
                    <label><input id="gender_F" type="radio"  name="gender" value="F" <?= http_post('gender') === 'F' ? 'checked' : '' ?>   data-rule-required="true"  data-rule-required="true" data-msg-required="You must enter your gender"/> Female</label>
                <p><label for="age"><b>Age *</b></label></p>
                <select id="age" name="age" class="cf" style="float:none"  data-rule-required="true" data-msg-required="You must enter your age">
                    <option value="">-- choose an age --</option>
                    <option value="<?= Customer::AGE_UNDER_12 ?>" <?= http_post('age') == Customer::AGE_UNDER_12 ? 'selected' : '' ?>> 12</option>
                    <option value="<?= Customer::AGE_12_17 ?>" <?= http_post('age') == Customer::AGE_12_17 ? 'selected' : '' ?>>12-17</option>
                    <option value="<?= Customer::AGE_18_24 ?>" <?= http_post('age') == Customer::AGE_18_24 ? 'selected' : '' ?>>18-24</option>
                    <option value="<?= Customer::AGE_25_34 ?>" <?= http_post('age') == Customer::AGE_25_34 ? 'selected' : '' ?>>25-34</option>
                    <option value="<?= Customer::AGE_35_44 ?>" <?= http_post('age') == Customer::AGE_35_44 ? 'selected' : '' ?>>35-44</option>
                    <option value="<?= Customer::AGE_45_54 ?>" <?= http_post('age') == Customer::AGE_45_54 ? 'selected' : '' ?>>45-54</option>
                    <option value="<?= Customer::AGE_55_64 ?>" <?= http_post('age') == Customer::AGE_55_64 ? 'selected' : '' ?>>55-64</option>
                    <option value="<?= Customer::AGE_65_74 ?>" <?= http_post('age') == Customer::AGE_65_74 ? 'selected' : '' ?>>65-74</option>
                    <option value="<?= Customer::AGE_75_OLDER ?>" <?= http_post('age') == Customer::AGE_75_OLDER ? 'selected' : '' ?>> 75</option>
                </select>

                <p><label for="countryId"><b>Country *</b></label></p>
                <select id="countryId" name="countryId" style="float:none"  data-rule-required="true" data-msg-required="You must enter your country">
                    <option value="">Select a country</option>
<?
                    $aCountries = CountryManager::getCountries();
                    foreach ($aCountries as $oCountry) {
                        if ($oCountry->countryId != Country::UNKNOWN_COUNTRY_ID) {
                        ?>
                        <option value="<?= $oCountry->countryId ?>" <?= http_post('countryId') == $oCountry->countryId ? 'selected' : '' ?> ><?= $oCountry->name ?></option>    
                    <? 
                        }
                    }
                    ?>
                </select>

                <p><label for="city"><b>City (optional)</b></label></p>
                <input type="text" id="cityId" name="city" value="<?= _e(http_post('city')) ?>" />  
                <label style="display:none" for="rumpelstiltskin-empty">Not change this field (SPAM check)</label>
                <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-empty" value="" />
                <label style="display:none" for="rumpelstiltskin-filled">Not change this field (SPAM check)</label>
                <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-filled" value="repelsteeltje" />                
                <p><input id="signA" type="submit" name="submit" value="Sign"></p>
            </form>
        </div>
    </div>
<? } ?>
<?
$iPerc = round(count($aSignatures) * 100 / $oPetition->requiredSignatures);
$sSignatureStatusUpdate = http_session('signatureStatusUpdate', '');
$sJavascript = <<<EOT
        
    var statusUpdate = '$sSignatureStatusUpdate';
        
    if(statusUpdate){
        showStatusUpdate(statusUpdate, $('.signatureStatusUpdate'));
    }
    progressBar($iPerc, $('#progressBar'));         
        
EOT;

if (empty($oCurrentCustomer)) {
    if (!empty($aErrors)) {
        $sJavascript .= "
        $.fancybox({
            content: $('#signerForm').html(),
            width: 400,
            height: 200,
            afterShow:
                function () {
                    
                    $('form.validateForm').each(function (index) {
                        var jForm = $(this);
                        jForm.validate({
                            focusInvalid: true,
                            errorContainer: jForm.find('.errorBox'),
                            errorLabelContainer: jForm.find('.errorBox ul'),
                            wrapper: 'li'
                        });
                    });             
                }
        });            
        ";               
    }
    
    $sJavascript .= <<<EOT
         
$('#signIt').click(
    function(e) {
        /*
        if ($('#comment1').val().trim().length == 0) {
            $.fancybox({
                content: '<p style="color: red;margin: 20% auto 0px;">You must enter a comment first</p>', 
                afterClose: function() { $('#comment1').focus(); $('html,body').animate({scrollTop: $('#comment1').offset().top},'slow')}
            });
            e.preventDefault();
            return;
        }
            */
        $('#comment2').val($('#comment1').val().trim());    
        $.fancybox({
            content: $('#signerForm').html(),
            width: 400,
            height: 200,
            afterShow:
                function () {
                    
                    $('form.validateForm').each(function (index) {
                        var jForm = $(this);
                        jForm.validate({
                            focusInvalid: true,
                            errorContainer: jForm.find('.errorBox'),
                            errorLabelContainer: jForm.find('.errorBox ul'),
                            wrapper: "li"
                        });
                    });                      
                }
        });
            
        e.preventDefault();
    }
); 
                    
EOT;
}
$oPageLayout->addJavascriptBottom('<script>' . $sJavascript . '</script>');
?>
