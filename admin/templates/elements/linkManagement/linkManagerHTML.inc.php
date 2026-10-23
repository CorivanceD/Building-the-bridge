<div id="lm_<?= $this->iContainerIDAddition ?>">
    <form method="POST" action="<?= $this->sUploadUrl ?>" class="validateForm">
        <input type="hidden" name="action" value="<?= $this->sHiddenAction ?>" />
        <table class="withForm">
            <tr>
                <td class="withLabel" style="width: 45px;"><label for="linkTitle_<?= $this->iContainerIDAddition ?>"><?= sysTranslations::get('global_title') ?> <?= $this->bTitleRequired ? '*' : '' ?></label></td>
                <td><input id="linkTitle_<?= $this->iContainerIDAddition ?>" maxlength="255" type="text" class="default <?= $this->bTitleRequired ? '{validate:{required:true}}' : '' ?>" <?= $this->sTitleTitle ? 'title="' . $this->sTitleTitle . '"' : '' ?> name="title" value="" /></td>
            </tr>
            <tr>
                <td class="withLabel"><label for="linkLink_<?= $this->iContainerIDAddition ?>">URL *</label></td>
                <td><input id="linkLink_<?= $this->iContainerIDAddition ?>" title="<?= sysTranslations::get('global_url_tooltip') ?>" class="default {validate:{required:true}}" type="text" name="link" value="" /></td>
            </tr>                           
            <tr>
                <td colspan="2"><input type="submit" name="send" value="<?= sysTranslations::get('global_link_added') ?>" /></td>
            </tr>
        </table>
    </form>
    <hr>
    <h3><?= sysTranslations::get('global_links_already_added') ?> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_drag_links') ?>">&nbsp;</div></h3>    
    <ul class="links <?= $this->sortable ? 'sortable' : '' ?>">
        <?
        foreach ($this->aLinks AS $oLink) {
            ?>
            <li id="placeholder-<?= $oLink->mediaId ?>" data-mediaid="<?= $oLink->mediaId ?>" data-title="<?= _e($oLink->title) ?>" data-link="<?= _e($oLink->link) ?>" class="placeholder">
                <div class="linkPlaceholder" title="<?= $oLink->link ?>">
                    <span class="mediaType link"></span><a class="title" target="_blank" href="<?= $oLink->link ?>"><?= ($oLink->title ? $oLink->title : $oLink->link) ?></a>
                </div>
                <div class="actionsPlaceholder">
                    <?
                    if ($this->onlineChangeable) {
                        if ($oLink->isOnlineChangeable()) {
                            echo '<a class="action_icon ' . ($oLink->online ? 'online' : 'offline') . '_icon onlineOfflineBtn" onclick="setOnlineLink(this); return false;" data-online="' . ($oLink->online ? 0 : 1) . '" href="' . $this->changeOnlineLink . '"></a>';
                        } else {
                            echo '<a onclick="return false;" class="action_icon grey ' . ($oLink->online ? 'online' : 'offline') . '_icon onlineOfflineBtn"  href="#"></a>';
                        }
                    }
                    if ($this->editable) {
                        if ($oLink->isEditable()) {
                            echo '<a class="action_icon edit_icon" onclick="showEditLink(this); return false;" href="' . $this->editLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    if ($this->deletable) {
                        if ($oLink->isDeletable()) {
                            echo '<a class="action_icon delete_icon" onclick="deleteLink(this); return false;" href="' . $this->deleteLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey delete_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    ?>
                </div>
            </li>
            <?
        }
        ?>
    </ul>
</div>
<?
# include editLinkForm once
include_once $this->sEditLinkFormLocation;

# add sortable javascript initiation code
$sLinkManagerJavascript = <<<EOT
$( "div#lm_{$this->iContainerIDAddition} ul.links.sortable").sortable({
    items: '> li',
    placeholder: 'ui-state-highlight placeholder',
    forcePlaceholderSize: true,
    tolerance: 'pointer',
    update: function(event, ui) {
        var mediaIds = new Array();
        ui.item.closest('ul.links').find('> li').each(function(index, value){
            mediaIds[index] = $(value).data('mediaid');
        });
        updateLinkOrder(mediaIds, '{$this->saveOrderLink}', 'lm_{$this->iContainerIDAddition}');
    }
});
EOT;
$oPageLayout->addJavascriptBottom('<script>' . $sLinkManagerJavascript . '</script>');
?>