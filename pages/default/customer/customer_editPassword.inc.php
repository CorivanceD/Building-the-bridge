<section style="margin-top: 32px">
    <div class="column-100 float-left">
        <h3><?= _e($oPage->title) ?></h3>
        <hr>
        <?= $oPage->content ?>
        <form method="POST" class="validateForm">
            <input type="hidden" value="updatePassword" name="action" />
            <?php
            // hack for multiple errorBoxes on one page
            $aErrors = array();
            if (!empty($aErrorsChangePass))
                $aErrors = $aErrorsChangePass;
            include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
            ?>
            <table class="withForm">
                <tr>
                    <td style="width: 230px;"><label for="email">E-mail *</label></td>
                    <td><input autocomplete="off" data-rule-required="true" data-msg-required="You must enter your e-mail address" data-rule-email="true" data-msg-email="You entered an invalid email address"  id="email" name="email" type="email" value="<?= http_post('email', http_get('email')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="code">Confirmation code *</label></td>
                    <td><input autocomplete="off" data-rule-required="true" data-msg-required="You have not filled in your code. Code not received, check to be sure your SPAM folder" id="code" name="code" style="width: 350px;" type="text" value="<?= http_post('code', http_get('code')) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="password">New password *</label></td>
                    <td><input autocomplete="off" data-rule-required="true" data-msg-required="You have not entered a password" data-rule-minlength="8" data-msg-minlength="You must enter a secure password (minimum 8 characters)" id="password" class="required" data-rule-minlength="8" title="Enter a secure password (at least 8 characters)" name="password" type="password" value="" /></td>
                </tr>
                <tr>
                    <td><label for="confirmPassword">Confirm new password *</label></td>
                    <td><input autocomplete="off" data-rule-required="true" data-msg-required="You have not confirmed your password" data-rule-equalTo="#password" data-msg-equalTo="Passwords do not match"  id="confirmPassword" name="confirmPassword" type="password" value="" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" value="Wachtwoord wijzigen" name="updatePassword" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</section>