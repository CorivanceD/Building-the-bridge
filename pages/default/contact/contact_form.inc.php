<section style="margin-top: 32px;">
    <div class="column-100 float-left">   
        <h3><?= _e($oPage->title) ?></h3>
        <hr>
        <article class="column-66 page-content float-left contactForm">           
            <?= $oPage->content ?>  
        </article>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>       
    </div>
</section> 
<?
$sHtml = '';
ob_start();
include TEMPLATES_FOLDER . '/elements/errorBox.inc.php';
$sHtml = ob_get_contents();
$sHtml = str_replace('"', '\\"', $sHtml);
$sHtml = trim(preg_replace('/\s\s+/', ' ', $sHtml));
ob_end_clean();

$sJavascript = '<script>
    $("#contact-form").html(
        "<span id=\"errors\"></span>" +
        "' . $sHtml . '" +
        "<form method=\"post\" class=\"validateForm\">" +
            "<input type=\"hidden\" name=\"action\" value=\"sendForm\">" +
            "<div class=\"column-100 float-left withForm contactForm\">" +
                "<div class=\"column-100 float-left\"><label for=\"name\">Name *</label></div>" +
                "<div class=\"column-100 float-left\"><input id=\"name\" name=\"name\" type=\"text\" value=\"\" /></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\"><label for=\"organisation\">Organisation</label></div>" +
                "<div class=\"column-100 float-left\"><input id=\"organisation\" name=\"organisation\" type=\"text\" value=\"\" /></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\"><label for=\"phone\">Phone</label></div>" +
                "<div class=\"column-100 float-left\"><input id=\"phone\" name=\"phone\" type=\"text\" value=\"\" /></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\"><label for=\"email\">E-mail *</label></div>" +
                "<div class=\"column-100 float-left\"><input id=\"email\" name=\"email\" type=\"text\" value=\"\" /></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\"><label for=\"subject\">Subject</label></div>" +
                "<div class=\"column-100 float-left\"><input id=\"subject\" name=\"subject\" type=\"text\" value=\"\" /></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\"><label for=\"message\">Message *</label></div>" +
                "<div class=\"column-100 float-left\"><textarea rows=\"6\" id=\"message\" name=\"message\"></textarea></div>" +
                "<div class=\"column-100 float-left\">&nbsp;</div>" +
                "<div class=\"column-100 float-left\">" +
                    "<input type=\"hidden\" name=\"action\" value=\"sendForm\" />" +
                    "<input type=\"submit\" name=\"done\" value=\"Send\" />" +
                    "<label style=\"display:none\" for=\"rumpelstiltskin-empty\">Not change this field (SPAM check)</label>" +
                    "<input style=\"display:none\" autocomplete=\"off\" type=\"text\" name=\"rumpelstiltskin-empty\" value=\"\" />" +
                    "<label style=\"display:none\" for=\"rumpelstiltskin-filled\">Not change this field (SPAM check)</label>" +
                    "<input style=\"display:none\" autocomplete=\"off\" type=\"text\" name=\"rumpelstiltskin-filled\" value=\"repelsteeltje\" />" +
                "</div>" +
            "</div>" +
        "</form>");
    $("#name").val("' . _e(http_post('name')) . '");
    $("#organisation").val("' . _e(http_post('organisation')) . '");
    $("#phone").val("' . _e(http_post('phone')) . '");
    $("#email").val("' . _e(http_post('email')) . '");
    $("#subject").val("' . _e(http_post('subject')) . '");
    $("#message").val("' . _e(http_post('message')) . '");     
</script>';
if (!empty($aErrors)) {
    $sJavascript .= '
    <script>
        location.hash = "#errors"        
    </script>';
}
$oPageLayout->addJavascriptBottom($sJavascript);
?>

