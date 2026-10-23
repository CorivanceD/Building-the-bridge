<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article>
        <div class="column-66 float-left">              
            <h1 class="sectionTitle"><?= $oPage->title ?></h1>
            <hr>
            <?= $oPage->content ?>
            <br/>
            <form method="post" class="validateForm">
                <input type="hidden" name="action" value="sendPetition">
                <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                <table class="withForm contactForm column-100">
                    <tr>
                        <td style="width:120px"><label for="author">Alias *</label></td>
                    </tr>
                    <tr>
                        <td><input data-rule-required="true" data-msg-required="You must enter your alias" id="author" name="author" type="text" value="<?= _e(http_post('author')) ?>" /></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="organization">Name of Organization *</label></td>
                    </tr>
                    
                    <tr>
                        <td><input data-rule-required="true" data-msg-required="You must enter your organization name" id="organization" name="organization" type="text" value="<?= _e(http_post('organization')) ?>" /></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="phone">Telephone Number, Including Country Code *</label></td>
                    </tr>
                    <tr>
                        <td><input data-rule-required="true" data-msg-required="You must enter your phone number" id="phone" name="phone" type="text" value="<?= _e(http_post('phone')) ?>" /></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="countryId">Country</label></td>
                    </tr>
                    <tr>
                        <td><select data-rule-required="true" data-msg-required="You must choose a country" id="countryId" name="countryId">
                                <option value="">Select a country</option>
                                <?
                                $aCountries = CountryManager::getCountries();
                                foreach ($aCountries as $oCountry) {
                                    ?>
                                    <option value="<?= $oCountry->countryId ?>" <?= (http_post('countryId') == $oCountry->countryId) ? 'selected' : '' ?> ><?= $oCountry->name ?></option>    
                                <? } ?>
                            </select>
                        </td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="email">E-mail *</label></td>
                    </tr>
                    <tr>
                        <td><input data-rule-required="true" data-msg-required="You must enter your e-mail address" data-rule-email="true" data-msg-email="You must enter a valid e-mail address" id="email" name="email" type="text" value="<?= _e(http_post('email')) ?>" /></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                    <td><label for="title">The Title of Your Petition *</label></td>
                    </tr>
                    <tr>
                        <td><input data-rule-required="true" data-msg-required="You must enter the title of your petition" id="title" name="title" type="text" value="<?= _e(http_post('title')) ?>" /></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="intro">Short Intro Text of Your Petition *</label></td>
                    </tr>
                    <tr>
                        <td><textarea style="height:200px;resize: none" data-rule-required="true" data-msg-required="You must enter an intro" id="intro" name="intro"><?= _e(http_post('intro')) ?></textarea></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><label for="description">Your Petition *</label></td>
                    </tr>
                    <tr>
                        <td><textarea style="height:400px;resize: none" data-rule-required="true" data-msg-required="You must enter a description" id="description" name="description"><?= _e(http_post('description')) ?></textarea></td>
                    </tr>
                                        <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: right">
                            <div class="g-recaptcha float-right" data-sitekey="6LcS7gwTAAAAAO4Yrym57vvhoJX3z2zZaMVlSRWG"></div>                            
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>                    
                    <tr>
                        
                        <td>
                            <input type="hidden" name="action" value="sendPetition" />
                            <input type="submit" name="done" value="Send" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
</section>