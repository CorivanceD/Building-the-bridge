<section style="margin-top: 32px">
    <h3><?= _e($oPage->title) ?></h3>
    <hr>
    <?= $oPage->content ?>
    <form method="POST" enctype="multipart/form-data" class="validateForm">
        <div>
            <div class="accountStatusUpdate statusUpdate column-100"></div>
            <div class="column-20 float-left">&nbsp;</div>
            <div class="column-50 float-left"> 

                <input type="hidden" value="save" name="action" />
                <?
                $aErrors = array();
                if (!empty($aCustomerErrors)) {
                    $aErrors = $aCustomerErrors;
                }
                include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
                ?>
                <table class="withForm">
                    <tr>
                        <td style="width: 120px;"><label>E-mail</label></td>
                        <td><input disabled type="email" value="<?= _e($oCustomer->email) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label>Password</label></td>
                        <td><input autocomplete="off"  data-rule-minlength="8" data-msg-minlength="You must enter a valid password (min. 8 characters long)" id="password" type="password" name="password" value="" /></td>
                    </tr>
                    <tr>
                        <td><label for="confirmPassword">Confirm password</label></td>
                        <td><input autocomplete="off"  data-rule-equalTo="#password" data-msg-equalTo="Passwords don't match" id="confirmPassword" name="confirmPassword" type="password" value="" /></td>
                    </tr>                
                    <tr>
                        <td><label for="name">Name</label></td>
                        <td><input disabled type="text" value="<?= _e($oCustomer->name) ?>" /></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><label>Gender</label></td>
                        <td>
                            <input <?= _e($oCustomer->gender) == 'M' ? 'CHECKED' : '' ?> type="radio" name="gender" value="M" disabled /> <label>Male</label><br />
                            <input <?= _e($oCustomer->gender) == 'F' ? 'CHECKED' : '' ?> type="radio"  name="gender" value="F" disabled /> <label>Female</label>
                        </td>
                    </tr>                    
                    <tr>
                        <td><label>Age</label>                          
                        </td>
                        <td>
                            <select disabled>
                                <option value="">-- choose an age --</option>
                                <option value="<?= Customer::AGE_UNDER_12 ?>" <?= intval($oCustomer->age) === Customer::AGE_UNDER_12 ? 'selected' : '' ?>>< 12</option>
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
                        <td><label>Country</label></td>
                        <td><select disabled>
                                <option value="">Select a country</option>
                                <?
                                $aCountries = CountryManager::getCountries();
                                foreach ($aCountries as $oCountry) {
                                    ?>
                                    <option value="<?= $oCountry->countryId ?>" <?= ($oCountry->countryId == $oCustomer->countryId) ? 'selected' : '' ?> ><?= $oCountry->name ?></option>    
<? } ?>
                            </select>
                        </td>
                    </tr>                      
                    <tr>
                        <td><label>City</label></td>
                        <td><input type="text" value="<?= _e($oCustomer->city) ?>" disabled /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" value="Save" name="save" />
                        </td>
                    </tr>
                </table>
            </div>
            <div class="column-20 float-left">       
                <img style="display:block;cursor:pointer;" onclick="$('#imageFile').click()" src="<?= $_SESSION['oCurrentCustomer']->getImage() !== null ? $_SESSION['oCurrentCustomer']->getImage()->getImageFileByReference('crop_detail')->link : '/images/layout/no-user-photo.png' ?>" width="200" height="200" alt="<?= $oCustomer->name ?>">                
                <input type="file" accept="image/*" name="imageFile" id="imageFile" style="display:none;margin-top:6px" onchange="$('#fileName').val($(this).val());">
                <input type="text" style="margin-top:6px;width:128px; padding: 5px 4px" id="fileName" name="fileName" disabled="true">
                <img id="explore" src="/images/layout/search-icon-mobile.png" width="21" height="21" style="background-color: #444444; padding:4px; vertical-align:bottom; cursor:pointer">

            </div>
            <div class="column-20 float-left">&nbsp;</div>        
        </div>
    </form>
</section>
<?
$sAccountStatusUpdate = http_session('accountStatusUpdate', '');
$sJavascript = <<<EOT
<script>
    var statusUpdate = '$sAccountStatusUpdate';
    if(statusUpdate){
        showStatusUpdate(statusUpdate, $('.accountStatusUpdate'));
    }
        
    $('#explore').click(function (e) {
        e.preventDefault();
        $('#imageFile').click()        
    });
        
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