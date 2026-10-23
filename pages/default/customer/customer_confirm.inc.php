<section style="margin-top: 32px">
    <div class="column-100 float-left">
        <h3><?= _e($oPage->title) ?></h3>
        <hr>
        <?= $oPage->content ?>
        <form method="POST" class="validateForm">
            <input type="hidden" value="confirm" name="action" />
            <?php
            $aErrors = array();
            if (!empty($aErrorsActivate)) {
                $aErrors = $aErrorsActivate;
            }
            include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
            ?>
            <table class="withForm">
                <tr>
                    <td style="width: 150px;"><label for="email">E-mail *</label></td>
                    <td><input data-rule-required="true" data-msg-required="You must enter your e-mail address" data-rule-email="true" data-msg-email="enter a valid e-mail address" id="email" name="email" type="email" value="<?= _e(http_post('email', http_session('accountConfirmEmail'))) ?>" /></td>
                </tr>
                <tr>
                    <td><label for="code">Confirmation code *</label></td>
                    <td><input data-rule-required="true" data-msg-required="You must enter the code provided in the confirmation email. If you don't find it, take a lookm into your SPAM folder" id="code"  name="code" type="text" value="<?= _e(http_post('code')) ?>" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="submit" value="Confirm" name="confirm" />
                </tr>
            </table>
        </form>
    </div>
</section>