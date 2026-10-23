<div class="hide">
    <div id="editLinkForm">
        <h2><?= sysTranslations::get('global_change_link_title') ?></h2>
        <form onsubmit="saveLink(this); return false;" method="POST" action="#">
            <input type="hidden" name="mediaId" value="" />
            <label for="linkTitle"><?= sysTranslations::get('global_title') ?></label><br />
            <input type="text" class="default" id="linkTitle" name="title" value="" /><br />
            <label for="linkLink">URL</label><br />
            <input type="text" class="default" id="linkLink" name="link" value="" /><br />            
            <input type="submit" name="" value="<?= sysTranslations::get('global_save_link') ?>" />
        </form>
    </div>
    <a id="editLinkFormLink" class="fancyBoxLink" href="#editLinkForm"></a>
</div>