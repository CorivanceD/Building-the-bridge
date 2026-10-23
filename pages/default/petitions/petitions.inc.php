<section style="margin-top: 32px;">
    <!-- MAIN CONTENT -->
    <article >
        <div class="column-66 float-left rssFeeds">
                <h1 class="sectionTitle"><?= $oPage->title ?></h1>
                <hr>
                <?= $oPage->content ?>

                <p><b>Running petitions</b></p><br>
                <form id="petitionForm" method="POST" action="<?=  getCurrentUrl()?>">
                    <select id="petitionId" name="petitionId">
                    <option>--- Choose a petition ---</option>
                    <?
                    foreach ($aPetitions as $oPetition) { ?>
                    <option value="<?= $oPetition->petitionId ?>"><?= $oPetition->title ?></option>
                    <?    
                    }
                    ?>
                    </select>
                </form>
        </div>
        <? include TEMPLATES_FOLDER . '/elements/rightColumn.inc.php'; ?>
    </article>
    <!-- /MAIN CONTENT -->
</section>
<?
$sJavascript = <<<EOT
<script>
$('#petitionId').change(
    function() {
        if ($(this).val().length) {
            $(this).parent().submit();
        }
    }
);
</script>
EOT;
$oPageLayout->addJavascriptBottom($sJavascript);
?>