<section style="margin-top: 32px;">
    <div class="column-100 float-left">
        <h3><?= _e($oPage->title) ?></h3>
        <hr>
        <article class="column-66 page-content float-left contactForm">
            <?= $oPage->content ?>
            <!-- <? if (empty($oCurrentCustomer)) { ?>

                <div class="withForm contactForm">
                    <form method="POST" class="validateForm">
                        <? include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                        <input type="hidden" name="action" value="subscribeNewsletter">
                        <p><label for="email"><b>Email address*</b></label></p>
                        <p><input type="text" id="email" name="email" value="" data-rule-email="true" data-msg-email="You must enter a valid e-mail address" data-rule-remote="/account?ajax=checkEmail" data-msg-remote="There already was a registered user with that e-mail address. Please log in or use another e-mail address" value="<?= _e(http_post('email')) ?>"></p>
                        <p><label for="alias"><b>Name</b></label></p>
                        <p><input type="text" id="name" name="name" value="<?= _e(http_post('name')) ?>"></p>
                        <p><label for="companyName"><b>Name of organization</b></label></p>
                        <p><input type="text" id="companyName" name="companyName" value="" value="<?= _e(http_post('companyName')) ?>"></p>
                        <p><label for="countryId"><b>Country</b></label></p>
                        <p><select id="countryId" name="countryId" style="float:none">
                                <option value="">Select a country</option>
                                <?
                                $aCountries = CountryManager::getCountries();
                                foreach ($aCountries as $oCountry) {
                                    if ($oCountry->countryId != Country::UNKNOWN_COUNTRY_ID) {
                                ?>
                                        <option value="<?= $oCountry->countryId ?>" <?= http_post('countryId') == $oCountry->countryId ? 'selected' : '' ?>><?= $oCountry->name ?></option>
                                <?
                                    }
                                }
                                ?>
                            </select></p>
                        <label style="display:none" for="rumpelstiltskin-empty">Not change this field (SPAM check)</label>
                        <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-empty" value="" />
                        <label style="display:none" for="rumpelstiltskin-filled">Not change this field (SPAM check)</label>
                        <input style="display:none" autocomplete="off" type="text" name="rumpelstiltskin-filled" value="repelsteeltje" />

                        <input type="submit" name="submit" id="subscribe" value="SUBSCRIBE">
                    </form>
                </div>

            <?
                    } else {
            ?>
                <form method="POST">
                    <input type="hidden" name="action" value="subscribeNewsletter">
                    <input type="submit" name="submit" id="subscribe" value="CLICK HERE">
                </form>
            <?
                    }
            ?> -->


            <!-- Begin Mailchimp Signup Form -->
            <div id="mc_embed_signup">
                <form action="https://abrahamavankempen.us20.list-manage.com/subscribe/post?u=1873d0531334953a7b8b6c32a&amp;id=eb83ae447c&amp;f_id=00267be0f0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div id="mc_embed_signup_scroll">
                        <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                        <div class="mc-field-group">
                            <label for="mce-EMAIL">Email Address <span class="asterisk">*</span>
                            </label>
                            <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" required>
                            <span id="mce-EMAIL-HELPERTEXT" class="helper_text"></span>
                        </div>
                        <div class="mc-field-group">
                            <label for="mce-FNAME">First Name </label>
                            <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                            <span id="mce-FNAME-HELPERTEXT" class="helper_text"></span>
                        </div>
                        <div class="mc-field-group">
                            <label for="mce-LNAME">Last Name </label>
                            <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                            <span id="mce-LNAME-HELPERTEXT" class="helper_text"></span>
                        </div>
                        <div id="mce-responses" class="clear">
                            <div class="response" id="mce-error-response" style="display:none"></div>
                            <div class="response" id="mce-success-response" style="display:none"></div>
                        </div> <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                        <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_1873d0531334953a7b8b6c32a_eb83ae447c" tabindex="-1" value=""></div>
                        <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
            </div>
            <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>
            <script type='text/javascript'>
                (function($) {
                    window.fnames = new Array();
                    window.ftypes = new Array();
                    fnames[0] = 'EMAIL';
                    ftypes[0] = 'email';
                    fnames[1] = 'FNAME';
                    ftypes[1] = 'text';
                    fnames[2] = 'LNAME';
                    ftypes[2] = 'text';
                }(jQuery));
                var $mcj = jQuery.noConflict(true);
            </script>
            <!--End mc_embed_signup-->

        </article>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </div>
</section>