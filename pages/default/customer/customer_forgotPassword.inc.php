<section style="margin-top: 32px">
    <div class="column-100 float-left">
        <h3><?= _e($oPage->title) ?></h3>
        <hr>
        <?= $oPage->content ?>
        <form method="POST" class="validateForm">
            <input type="hidden" value="requestPassword" name="action" />
            <?
            $aErrors = array();
            if (!empty($aErrorsForgotPass)) {
                $aErrors = $aErrorsForgotPass;
            }
            include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
            ?>
            <table class="withForm">
                <tr>
                    <td style="width: 120px;"><label for="email">E-mail *</label></td>
                    <td><input data-rule-required="true" data-msg-required="You must enter your email address" data-rule-email="true" data-msg-email="You've entered an invalid email address" id="email" name="email" type="email"  value="<? http_post('email') ?>" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" value="Reset password" name="requestPassword" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</section>