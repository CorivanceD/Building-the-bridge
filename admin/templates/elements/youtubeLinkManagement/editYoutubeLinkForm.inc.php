<div class="hide">
    <div id="editYoutubeLinkForm">
        <h2><?= sysTranslations::get('global_change_youtube_link') ?></h2>
        <form onsubmit="saveYoutubeLink(this); return false;" method="POST" action="#">
            <input type="hidden" name="mediaId" value="" />
            <label for="youtubeLinkTitle"><?= sysTranslations::get('global_title') ?></label><br />
            <input type="text" class="default" id="youtubeLinkTitle" name="title" value="" /><br />
            <label for="youtubeLinkLink">URL</label><br />
            <input type="text" class="default" id="youtubeLinkLink" name="link" value="" /><br />            
            <input type="submit" name="" value="<?= sysTranslations::get('global_save_youtube_link') ?>" />
        </form>
    </div>
    <a id="editYoutubeLinkFormLink" class="fancyBoxLink" href="#editYoutubeLinkForm"></a>
</div>