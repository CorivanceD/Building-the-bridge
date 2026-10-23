<div id="ym_<?= $this->iContainerIDAddition ?>">
    <form method="POST" action="<?= $this->sUploadUrl ?>" class="validateForm">
        <input type="hidden" name="action" value="<?= $this->sHiddenAction ?>" />
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 45px;"><label for="youtubeLinkTitle_<?= $this->iContainerIDAddition ?>"><?= sysTranslations::get('global_title') ?> *</label></td>
                <td><input title="<?= sysTranslations::get('global_youtube_title_tooltip') ?>" class="{validate:{required:true}} default" id="youtubeLinkTitle_<?= $this->iContainerIDAddition ?>" maxlength="255" type="text" name="title" value="" /></td>
            </tr>
            <tr>
                <td class="withLabel"><label for="youtubeLinkLink_<?= $this->iContainerIDAddition ?>">URL *</label></td>
                <td><input id="youtubeLinkLinkTitle_<?= $this->iContainerIDAddition ?>" title="<?= sysTranslations::get('global_youtube_url_tooltip') ?>" class="default {validate:{required:true}}" type="text" name="link" value="" /></td>
            </tr>                 
            <tr>
                <td colspan="2"><input type="submit" name="send" value="<?= sysTranslations::get('global_youtube_save') ?>" /></td>
            </tr>
        </table>
    </form>
    <hr>
    <h3><?= sysTranslations::get('global_youtube_links_already_added') ?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_youtube_drag') ?>">&nbsp;</div></h3>     
    <ul class="youtubeLinks <?= $this->sortable ? 'sortable' : '' ?>">
        <?php
        foreach ($this->aYoutubeLinks AS $oYoutubeLink) {
            ?>
            <li id="placeholder-<?= $oYoutubeLink->mediaId ?>" data-mediaid="<?= $oYoutubeLink->mediaId ?>" data-title="<?= _e($oYoutubeLink->title) ?>" data-link="<?= _e($oYoutubeLink->link) ?>" class="placeholder">
                <div class="youtubeLinkPlaceholder" title="<?= $oYoutubeLink->link ?>">
                    <span class="mediaType youtube"></span><a class="fancyBoxLink fancybox.iframe title" href="<?= $oYoutubeLink->getEmbedLink() ?>"><?= $oYoutubeLink->title ? $oYoutubeLink->title : $oYoutubeLink->link ?></a>
                </div>
                <div class="youtubeThumbsPlaceholder">
                    <img class="thumb1" src="<?= $oYoutubeLink->getThumbLink(1) ?>" />
                    <img class="thumb2" src="<?= $oYoutubeLink->getThumbLink(2) ?>" />
                    <img class="thumb3" src="<?= $oYoutubeLink->getThumbLink(3) ?>" />
                </div>
                <div class="actionsPlaceholder">
                    <?php
                    if ($this->onlineChangeable) {
                        if ($oYoutubeLink->isOnlineChangeable()) {
                            echo '<a class="action_icon ' . ($oYoutubeLink->online ? 'online' : 'offline') . '_icon onlineOfflineBtn" onclick="setOnlineYoutubeLink(this); return false;" data-online="' . ($oYoutubeLink->online ? 0 : 1) . '" href="' . $this->changeOnlineLink . '"></a>';
                        } else {
                            echo '<a onclick="return false;" class="action_icon grey ' . ($oYoutubeLink->online ? 'online' : 'offline') . '_icon onlineOfflineBtn"  href="#"></a>';
                        }
                    }
                    if ($this->editable) {
                        if ($oYoutubeLink->isEditable()) {
                            echo '<a class="action_icon edit_icon" onclick="showEditYoutubeLink(this); return false;" href="' . $this->editLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    if ($this->deletable) {
                        if ($oYoutubeLink->isDeletable()) {
                            echo '<a class="action_icon delete_icon" onclick="deleteYoutubeLink(this); return false;" href="' . $this->deleteLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey delete_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    ?>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>
</div>
<?php
# include editLinkForm once
include_once $this->sEditYoutubeLinkFormLocation;

# add sortable javascript initiation code
$sLinkManagerJavascript = <<<EOT
$( "div#ym_{$this->iContainerIDAddition} ul.youtubeLinks.sortable").sortable({
    items: '> li',
    placeholder: 'ui-state-highlight placeholder',
    forcePlaceholderSize: true,
    tolerance: 'pointer',
    update: function(event, ui) {
        var mediaIds = new Array();
        ui.item.closest('ul.youtubeLinks').find('> li').each(function(index, value){
            mediaIds[index] = $(value).data('mediaid');
        });
        updateYoutubeLinkOrder(mediaIds, '{$this->saveOrderLink}', 'ym_{$this->iContainerIDAddition}');
    }
});
EOT;
$oPageLayout->addJavascriptBottom('<script>' . $sLinkManagerJavascript . '</script>');
?>