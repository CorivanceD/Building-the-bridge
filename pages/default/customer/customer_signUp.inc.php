<section style="margin-top: 32px">
    <h1><?= _e($oPage->title) ?></h1>
    <hr>
    <?= $oPage->content ?>
    <div class="cf">
        <div class="column-50 float-left">
            <div class="accountStatusUpdate statusUpdate"></div>
            <h3>Are you already registered?</h3>
            <br>
            <form method="POST" class="validateForm">
                <input type="hidden" name="action" value="login">
                <?php
                // hack for multiple errorBoxes on one page
                $aErrors = array();
                if (!empty($aErrorsLogin))
                    $aErrors = $aErrorsLogin;
                include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
                ?>
                <table class="withForm">
                    <tr>
                        <td style="width: 120px;"><label for="signup-login-email">E-mail *</label></td>
                        <td><input autocomplete="off" data-rule-required="true" data-msg-required="Enter your email address" data-rule-email="true" data-msg-email="Invalid email address" id="signup-login-email" name="email" type="email" value="<?= _e(http_post('email')) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="signup-login-password">Password *</label></td>
                        <td><input autocomplete="off" data-rule-required="true" data-msg-required="Enter your password" id="signup-login-password" name="password" type="password" value="<?= _e(http_post('password')) ?>" /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" value="ENTER" name="login" />
                        </td>
                    </tr>
                </table>
            </form>
            <p><a class="pageLink" href="/<?= http_get('controller') ?>/password-forgotten">Have you forgotten your password?</a></p>
        </div>
        <div class="column-width float-left">&nbsp;</div>
        <div class="column-50 float-left">
            <h3>Let your vote count </h3>
            <br>
            <form method="POST" action="<?= getCurrentUrlPath() ?>" class="validateForm">
                <input type="hidden" value="register" name="action" />
                <?php
                // hack for multiple errorBoxes on one page
                $aErrors = array();
                if (!empty($aErrorsSignup))
                    $aErrors = $aErrorsSignup;
                include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
                ?>
                <table class="withForm">
                    <tr>
                        <td style="width: 160px;"><label for="signup-email">E-mail *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your email address" data-rule-email="true" data-msg-email="You must enter a valid email address" data-rule-remote="/<?= http_get('controller') ?>?ajax=checkEmail" data-msg-remote="That email address is already in use" id="signup-email" name="email" type="email" value="<?= _e($oCustomer->email) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="signup-password">Password *</label></td>
                        <td><input autocomplete="off" data-rule-required="true" data-msg-required="Enter your password" data-rule-minlength="8" data-msg-minlength="You must enter a valid password (min. 8 characters long)" id="signup-password" type="password" name="password" value="" /></td>
                    </tr>
                    <tr>
                        <td><label for="signup-confirmPassword">Confirm password *</label></td>
                        <td><input autocomplete="off" data-rule-required="true" data-msg-required="You must confirm your password" data-rule-equalTo="#signup-password" data-msg-equalTo="Passwords don't match" id="signup-confirmPassword" name="confirmPassword" type="password" value="" /></td>
                    </tr>
                    <tr>
                        <td><label for="signup-name">Name *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your name" id="signup-name" type="text" name="name" value="<?= _e($oCustomer->name) ?>" /></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label>Gender *</label></td>
                        <td>
                            <input <?= _e($oCustomer->gender) == 'M' ? 'CHECKED' : '' ?> data-rule-required="true" data-msg-required="You must choose a gender" id="signup-gender_M" type="radio" name="gender" value="M" /> <label for="signup-gender_M">Male</label><br />
                            <input <?= _e($oCustomer->gender) == 'F' ? 'CHECKED' : '' ?> data-rule-required="true" data-msg-required="You must choose a gender" id="signup-gender_F" type="radio"  name="gender" value="F" /> <label for="signup-gender_F">Female</label>
                        </td>
                    </tr>                    
                    <tr>
                        <td><label for="signup-Age">Age *</label>                          
                        </td>
                        <td>
                            <select id="signup-Age" name="age" data-rule-required="true" data-msg-required="You must select an age" >
                                <option value="">-- choose an age --</option>
                                <option value="<?= Customer::AGE_UNDER_12 ?>" <?= intval($oCustomer->age) === Customer::AGE_UNDER_12 ? 'selected' : '' ?>> 12</option>
                                <option value="<?= Customer::AGE_12_17 ?>" <?= intval($oCustomer->age) === Customer::AGE_12_17 ? 'selected' : '' ?>>12-17</option>
                                <option value="<?= Customer::AGE_18_24 ?>" <?= intval($oCustomer->age) === Customer::AGE_18_24 ? 'selected' : '' ?>>18-24</option>
                                <option value="<?= Customer::AGE_25_34 ?>" <?= intval($oCustomer->age) === Customer::AGE_25_34 ? 'selected' : '' ?>>25-34</option>
                                <option value="<?= Customer::AGE_35_44 ?>" <?= intval($oCustomer->age) === Customer::AGE_35_44 ? 'selected' : '' ?>>35-44</option>
                                <option value="<?= Customer::AGE_45_54 ?>" <?= intval($oCustomer->age) === Customer::AGE_45_54 ? 'selected' : '' ?>>45-54</option>
                                <option value="<?= Customer::AGE_55_64 ?>" <?= intval($oCustomer->age) === Customer::AGE_55_64 ? 'selected' : '' ?>>55-64</option>
                                <option value="<?= Customer::AGE_65_74 ?>" <?= intval($oCustomer->age) === Customer::AGE_65_74 ? 'selected' : '' ?>>65-74</option>
                                <option value="<?= Customer::AGE_75_OLDER ?>" <?= intval($oCustomer->age) === Customer::AGE_75_OLDER ? 'selected' : '' ?>>> 75</option>
                            </select>
                        </td>
                    </tr>                        
                    <tr>
                        <td><label for="signup-country">Country *</label></td>
                        <td><select data-rule-required="true" data-msg-required="You must select a country" id="signup-country" name="countryId"
                                    style="width: 190px;">
                                <option value="">Select a country</option>
                                <?php
                                $aCountries = CountryManager::getCountries();
                                foreach ($aCountries as $oCountry) {
                                    ?>
                                    <option value="<?= $oCountry->countryId ?>" <?= ($oCountry->countryId == $oCustomer->countryId) ? 'selected' : '' ?> ><?= $oCountry->name ?></option>    
                                <?php } ?>
                            </select>
                        </td>
                    </tr>                          
                    <tr>
                        <td><label for="signup-city">City *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your city" id="signup-city" type="text" name="city" value="<?= _e($oCustomer->city) ?>" /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input style="float:left" type="submit" value="Register" name="register" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</section>
<?php
$sJavascript = <<<EOT
<script>
    var statusUpdate = '$sAccountStatusUpdate';
    if(statusUpdate){
        showStatusUpdate(statusUpdate, $('.accountStatusUpdate'));
    }  
        
    $('#birthYear').change(function(e) {
        if($(this).val()) {
            $('#birthMonth').prop('disabled', false);
        }
        else {
            $('#birthMonth').val('');
            $('#birthDay').val('');
            $('#birthMonth').prop('disabled', true);
            $('#birthDay').prop('disabled', true);
        }
    });

    $('#birthMonth').change(function(e) {
        if ($(this).val()) {
            var daysInMonth =  new Date($('#birthYear').val(), $('#birthMonth').val(), 0).getDate();
            var options = '<option value="">Day</option>';
            for (i = 1; i <= daysInMonth; i++) {
                options += '<option value="' + i + '">' + i + '</option>';
            }    
            $('#birthDay').html(options); 
            $('#birthDay').prop('disabled', false);
        }
        else {
            $('#birthDay').val('');
            $('#birthDay').prop('disabled', true);
        }

    });        
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>