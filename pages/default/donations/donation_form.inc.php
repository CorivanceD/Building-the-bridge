<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article >
        <div class="column-66 float-left rssFeeds">
            <h3><?= $oPage->title ?></h3>
            <hr>
            <?= $oPage->content ?>
            <form method="post" class="validateForm">
                <input type="hidden" name="action" value="sendForm">
                <?php include TEMPLATES_FOLDER . '/elements/errorBox.inc.php'; ?>
                <table class="withForm">
                    <tr>
                        <td style="width: 120px;"><label for="name">Name *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your name" id="name" name="name" type="text" value="<?= _e(http_post('name')) ?>" /></td>
                    </tr>
                    <tr>
                        <td><label for="email">E-mail *</label></td>
                        <td><input data-rule-required="true" data-msg-required="You must enter your e-mail address" data-rule-email="true" data-msg-email="You must enter a valid e-mail address" id="email" name="email" type="text" value="<?= _e(http_post('email')) ?>" /></td>
                    </tr>               
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="hidden" name="action" value="sendForm" />
                            <input type="submit" name="done" value="Send" />
                        </td>
                    </tr>
                </table>
            </form>                

        </div>
        <?php include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
    <!-- /MAIN CONTENT -->
</section>