<div class="hide">
    <div id="editFileForm">
        <h2><?= sysTranslations::get('global_change_file_title') ?></h2>
        <form onsubmit="saveFile(this); return false;" method="POST" action="#">
            <input type="hidden" name="mediaId" value="" />
            <label for="fileTitle"><?= sysTranslations::get('global_title') ?></label><br />
            <input type="text" class="default" id="fileTitle" name="title" value="" /><br />
            <input type="submit" name="" value="<?= sysTranslations::get('global_save_title') ?>" />
        </form>
    </div>
    <a id="editFileFormLink" class="fancyBoxLink" href="#editFileForm"></a>
</div>