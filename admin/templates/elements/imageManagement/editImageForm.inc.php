<div class="hide">
    <div id="editImageForm">
        <h2><?= sysTranslations::get('global_change_image_title') ?></h2>
        <form onsubmit="saveImage(this); return false;" method="POST" action="#">
            <input type="hidden" name="imageId" value="" />
            <img src="" class="thumb" /><br />
            <label for="imageTitle"><?= sysTranslations::get('global_title') ?></label><br />
            <input type="text" class="default" id="imageTitle" name="title" value="" /><br />
            <input type="submit" name="" value="Titel opslaan" />
        </form>
    </div>
    <a id="editImageFormLink" class="fancyBoxLink" href="#editImageForm"></a>
</div>