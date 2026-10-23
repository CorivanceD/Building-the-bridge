<div class="errorBox" <?= !empty($aErrors) ? 'style="display:block;"' : '' ?>>
    <div class="title">Some information is missing or incorrect</div>
    <p>
        The form cannot be sent because it is incorrect.
    </p>
    <ul>
        <?php
        if (!empty($aErrors)) {
            foreach ($aErrors AS $sField => $sError) {
                echo '<li><label for="' . $sField . '" class="error" style="display: block;">' . $sError . '</label></li>';
            }
        }
        ?>
    </ul></div>