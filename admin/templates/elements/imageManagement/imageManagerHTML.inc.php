<div id="im_<?= $this->iContainerIDAddition ?>" class="im_container" data-addition="<?= $this->iContainerIDAddition ?>">
    <p class="maxImagesError error <?= $this->iMaxImages > 0 && (count($this->aImages) >= $this->iMaxImages) ? '' : 'hide' ?>">
        <i><?= sysTranslations::get('global_file_limit') ?> <?= $this->iMaxImages ?> <?= sysTranslations::get('global_image_limit') ?></i>
    </p>
    <form action="<?= $this->sUploadUrl ?>" onsubmit="if ($(this).valid())
                return hideSubmitShowAjaxLoader('saveImageBtn_<?= $this->iContainerIDAddition ?>', 'ajax-loader');
            else
                return false;" id="form_<?= $this->iContainerIDAddition ?>" class="validateForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $this->sHiddenAction ?>" />
        <div class="clearfix">
            <div style="float: left;">
                <table class="uploadForm <?= $this->iMaxImages > 0 && (count($this->aImages) >= $this->iMaxImages) ? 'hide' : '' ?> withForm">
                    <?
                    if (!$this->bMultipleFileUpload) {
                        ?>
                        <tr>
                            <td>
                                <?
                                if (!empty($this->sExtraUploadLine)) {
                                    echo '<p>' . $this->sExtraUploadLine . '</p>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="imageTitle_<?= $this->iContainerIDAddition ?>"><?= sysTranslations::get('global_image_description') ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input id="imageTitle_<?= $this->iContainerIDAddition ?>" maxlength="255" class="default" name="title" type="text" value="" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="file" title="<?= $this->sValidateFile ? sysTranslations::get('global_select_extensions') .': '. $this->sValidateFile : '' ?>" class="{validate:{required:true<?= $this->sValidateFile ? ', accept:\'' . $this->sValidateFile . '\'' : '' ?>}}" name="image" />
                            </td>
                        </tr>

                        <?
                    }

                    # optional extra options for after upload
                    if (is_array($this->getExtraOptions())) {
                        echo '<tr><td><select name="extra-option">';
                        foreach ($this->getExtraOptions() AS $aExtraOption) {
                            list($sOptionLabel, $sOptionValue) = $aExtraOption;
                            echo '<option value="' . $sOptionValue . '">' . $sOptionLabel . '</option>';
                        }
                        echo '</select></td></tr>';
                    }

                    if ($this->bShowCropAfterUploadOption) {
                        ?>
                        <tr>
                            <td>
                                <input class="alignCheckbox" type="checkbox" <?= $this->bCropAfterUploadChecked ? 'CHECKED' : '' ?> name="cropAfterUpload" id="cropAfterUpload" value="1" /> <label for="cropAfterUpload"><?= sysTranslations::get('global_image_direct_cut') ?></label>
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                    <?
                    if (!$this->bMultipleFileUpload) {
                        ?>
                        <tr>
                            <td>
                                <img src="<?= ADMIN_FOLDER ?>/images/layout/ajax-loader.gif" class="ajax-loader imageManagement" />
                                <input <?= $this->iMaxImages > 0 && (count($this->aImages) >= $this->iMaxImages) ? 'DISABLED' : '' ?> id="saveImageBtn_<?= $this->iContainerIDAddition ?>" type="submit" name="saveImage" value="<?= sysTranslations::get('global_upload_image') ?>" />
                            </td>
                        </tr>
                        <?
                    } else {
                        ?>
                        <tr>
                            <td style="padding-top: 5px;">
                                <span id="upload-images-btn_<?= $this->iContainerIDAddition ?>"></span>
                            </td>
                        </tr>
                        <tr>
                            <td><div class="progressInfo"></div></td>
                        </tr>
                        <?
                    }
                    ?>
                </table>
            </div>
            <? if ($this->bCoverImageShow) { ?>
                <div class="coverImageContainer<?= $this->oCoverImageImageFile ? '' : ' hide' ?>" style="float: right;">
                    <div class="placeholder">
                        <div class="placeholderTitle"><b><?= $this->sCoverImageTitle ?></b> <div class="hasTooltip tooltip" title="<?= sysTranslations::get('global_replace_image') ?>">&nbsp;</div></div>
                        <div class="imagePlaceholder">
                            <? if ($this->oCoverImageImageFile) { ?>
                                <img data-imageid="<?= $this->oCoverImageImageFile->imageId ?>" src="<?= $this->oCoverImageImageFile->link ?>" />
                            <? } ?>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
    </form>
    <hr>
    <?
    $sTooltipTitle = '';
    $sTooltipTitle .= ($sTooltipTitle != '' ? '<br />' : '') . sysTranslations::get('global_drag_images');
    if ($this->iMaxImages > 0)
        $sTooltipTitle .= ($sTooltipTitle != '' ? '<br />' : '') . sysTranslations::get('global_max_files') . $this->iMaxImages . sysTranslations::get('global_max_images');
    ?>
    <div class="imagesText<?= count($this->aImages) ? '' : ' hide' ?>">
        <h3><?= sysTranslations::get('global_images_already_uploaded') ?> <?= $this->iMaxImages > 0 ? ' (<span class="imagesCountLeft">' . count($this->aImages) . '/' . $this->iMaxImages . '</span>) ' : '' ?><div class="hasTooltip tooltip" title="<?= $sTooltipTitle ?>">&nbsp;</div></h3>
        <?
        if ($this->sExtraUploadedLine) {
            echo '<p>' . $this->sExtraUploadedLine . '</p>';
        }
        ?>
    </div>
    <div class="noImagesText<?= count($this->aImages) ? ' hide' : '' ?>">
        <i><?= sysTranslations::get('global_no_images_uploaded') ?></i>
    </div>
    <div class="images <?= $this->sortable ? 'sortable' : '' ?>">
        <?
        foreach ($this->aImages AS $oImage) {
            ?>
            <div id="placeholder-<?= $oImage->imageId ?>" imageId="<?= $oImage->imageId ?>" class="placeholder">
                <?
                if (!$oImage->hasImageFiles($this->aNeededImageFileReferences)) {
                    echo '<img class="notAllCrops" src="' . ADMIN_FOLDER . '/images/layout/icons/exclamation_icon.png" alt="'.sysTranslations::get('global_no_cutouts').'" title="'.sysTranslations::get('global_no_cutouts').'" />';
                }
                ?>
                <div class="imagePlaceholder">
                    <div class="centered">
                        <img src="<?= $oImage->getImageFileByReference('cms_thumb')->link . '?' . time() ?>" alt="<?= $oImage->getImageFileByReference('cms_thumb')->title ?>" <?= $oImage->getImageFileByReference('cms_thumb')->imageSizeAttr ?> title="<?= $oImage->getImageFileByReference('cms_thumb')->title ?>" />
                    </div>
                </div>
                <div class="actionsPlaceholder">
                    <?
                    if ($this->onlineChangeable) {
                        if ($oImage->isOnlineChangeable()) {
                            echo '<a class="action_icon ' . ($oImage->online ? 'online' : 'offline') . '_icon onlineOfflineBtn" onclick="setOnlineImage(this); return false;" online="' . ($oImage->getImageFileByReference('original')->online ? 0 : 1) . '" href="' . $this->changeOnlineLink . '"></a>';
                        } else {
                            echo '<a onclick="return false;" class="action_icon grey ' . ($oImage->online ? 'online' : 'offline') . '_icon onlineOfflineBtn"  href="#"></a>';
                        }
                    }
                    if ($this->cropable == true) {
                        if ($oImage->isCropable()) {
                            echo '<a class="action_icon crop_icon" href="' . $this->cropLink . (strpos($this->cropLink, '?') ? '&' : '?') . 'imageId=' . $oImage->imageId . '"></a>';
                        } else {
                            echo '<a onclick="return false;" class="action_icon grey crop_icon" href="#"></a>';
                        }
                    }
                    if ($this->editable) {
                        if ($oImage->isEditable()) {
                            echo '<a class="action_icon edit_icon" onclick="showEditImage(this); return false;" href="' . $this->editLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    if ($this->deletable) {
                        if ($oImage->isDeletable()) {
                            echo '<a class="action_icon delete_icon" onclick="deleteImage(this); return false;" href="' . $this->deleteLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey delete_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?
        }
        ?>
    </div>
</div>
<?
# include editImageForm once
include_once $this->sEditImageFormLocation;

# add sortable javascript initiation code
$sImageManagerJavascript = <<<EOT
var updateSortableOnDrop = true;
   
$( "div#im_{$this->iContainerIDAddition} div.images.sortable").sortable({
    items: '> div',
    placeholder: 'ui-state-highlight placeholder',
    forcePlaceholderSize: true,
    tolerance: 'pointer',
    update: function(event, ui) {
        if(updateSortableOnDrop){
        var imageIds = new Array();
        ui.item.closest('div.images').find('> div').each(function(index, value){
            imageIds[index] = $(value).attr('imageId');
        });
        updateImageOrder(imageIds, '{$this->saveOrderLink}', 'im_{$this->iContainerIDAddition}');
        }else{
            $( "div#im_{$this->iContainerIDAddition} div.images.sortable").sortable("cancel"); // cancel sortable update
            updateSortableOnDrop = true;
    }
    }
});

EOT;
$oPageLayout->addJavascriptBottom('<script>' . $sImageManagerJavascript . '</script>');

if ($this->bCoverImageShow) {
    $sCoverImageJavascript = <<<EOT
$("div#im_{$this->iContainerIDAddition} .coverImageContainer .placeholder").droppable({
    drop: function(e, ui) {
        var jElementDroppable = $(this);
        var jElementDraggable = $(ui.draggable);
        $.ajax({
            type: 'POST',
            url: '{$this->sCoverImageUpdateLink}',
            data: 'imageId=' + jElementDraggable.attr('imageid'),
            success: function(data){
                var dataObj = eval("("+data+")");
                if(dataObj.success){
                    showStatusUpdate('{$this->sCoverImageSuccessText}');
                    jElementDroppable.find('img').attr('src', dataObj.imageFile.link).data('imageid', dataObj.imageFile.imageId);
                    jElementDroppable.effect('highlight', 1500);
                }else{
                    showStatusUpdate('{$this->sCoverImageErrorText}');
                }
            }
        });
        updateSortableOnDrop = false;
    },
    hoverClass: 'ui-state-highlight',
    accept: 'div#im_{$this->iContainerIDAddition} .images .placeholder',
    tolerance: 'pointer'
});

/**
* @param imageId id of image that modified
*/
globalFunctions.updateCoverImageAfterDelete{$this->iContainerIDAddition} = function (imageId){
    var jElementDroppable = $('div#im_{$this->iContainerIDAddition} .coverImageContainer .placeholder');

    if($('div#im_{$this->iContainerIDAddition} .images .placeholder').size() == 0){
        $('div#im_{$this->iContainerIDAddition} .coverImageContainer').hide();
        return;
    }

    if(jElementDroppable.find('img').data('imageid') == imageId){
        $.ajax({
            url: '{$this->sCoverImageGetLink}',
            async: false,
            success: function(data){
                var dataObj = eval("("+data+")");
                if(dataObj.success){
                    jElementDroppable.find('img').attr('src', dataObj.imageFile.link).data('imageid', dataObj.imageFile.imageId);
                    jElementDroppable.effect('highlight', 1500);
                }
            }
        });
    }
}

EOT;
    $oPageLayout->addJavascriptBottom('<script>' . $sCoverImageJavascript . '</script>');
}

# get javascript for mulitple file upload
if ($this->bMultipleFileUpload) {

    # include js files once
    include_once ADMIN_TEMPLATES_FOLDER . '/elements/jsFiles_ManagerHTML.inc.php';

    $sAdminFolder = ADMIN_FOLDER;
    $sAdminPluginsFolder = ADMIN_PLUGINS_FOLDER;
    $sSessionId = session_id();

    $sAllowedExtensions = '';
    $iT = 1;
    foreach ($this->aMultipleFileUploadAllowedExtensions AS $sExtension) {
        $sAllowedExtensions .= '*.' . $sExtension;
        if ($iT < count($this->aMultipleFileUploadAllowedExtensions))
            $sAllowedExtensions .= ';';
        $iT++;
    }
    if ($sAllowedExtensions != '') {
        $sAllowedExtensions = 'file_types : "' . $sAllowedExtensions . '",';
    }

    $sFileSizeLimit = '';
    if ($this->sMultipleFileUploadFileSizeLimit !== null) {
        $sFileSizeLimit = 'file_size_limit : "' . $this->sMultipleFileUploadFileSizeLimit . '",';
    }

    $bOnlineChangable = $this->onlineChangeable ? 'true' : 'false';
    $bEditable = $this->editable ? 'true' : 'false';
    $bDeletable = $this->deletable ? 'true' : 'false';
    $bCropable = $this->cropable ? 'true' : 'false';

    $sMultipleFileUploadJavascript = <<<EOT

    var swfu_im_{$this->iContainerIDAddition};
    swfu_im_{$this->iContainerIDAddition} = new SWFUpload({
        upload_url : "{$this->sUploadUrl}",
        file_post_name : "image",
        post_params : {
            "action" : "saveImage",
            "SWFUpload" : true,
            "PHPSESSID" : "$sSessionId"
        },
        flash_url : "$sAdminPluginsFolder/SWFUpload/Flash/swfupload.swf",
        button_placeholder_id : "upload-images-btn_{$this->iContainerIDAddition}",
        button_image_url : '$sAdminFolder/images/layout/select-images.jpg',
        button_width : 244,
        button_height : 28,
        $sAllowedExtensions
        $sFileSizeLimit
        button_cursor : SWFUpload.CURSOR.HAND,
        button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE,
        
        swfupload_loaded_handler : swfupload_loaded_handler,
        //file_dialog_start_handler : file_dialog_start_function,
        //file_queued_handler : file_queued_handler,
        file_queue_error_handler : file_queue_error_handler,
        file_dialog_complete_handler : file_dialog_complete_handler,
        
        upload_start_handler : upload_start_handler,
        upload_progress_handler : upload_progress_handler,
        upload_error_handler : upload_error_handler,
        
        upload_success_handler : upload_success_handler_images,
        upload_complete_handler : upload_complete_handler,
        custom_settings : {
            progressTarget : "progressInfo",
            formID : "form_{$this->iContainerIDAddition}",
            onlineChangeable : $bOnlineChangable,
            cropable : $bCropable,
            editable : $bEditable,
            deletable : $bDeletable,
            changeOnlineLink : '{$this->changeOnlineLink}',
            cropLink : '{$this->cropLink}',
            editLink : '{$this->editLink}',
            deleteLink : '{$this->deleteLink}',
            containerID : 'im_{$this->iContainerIDAddition}',
            containerIDAddition : '{$this->iContainerIDAddition}',
            coverImageShow : '{$this->bCoverImageShow}'
        },
        //debug : false,
        //debug_handler : debug_handler
    });
    
    // count current amount of images and check if upload should be disabled
    globalFunctions.checkMaxImages{$this->iContainerIDAddition} = function (){
        var imageManagerID = '#im_{$this->iContainerIDAddition}';
        var imageCount = $(imageManagerID).find('.images .placeholder').size();
        var maxImages = '{$this->iMaxImages}';
        if(maxImages>0){    
            if((maxImages-imageCount) <= 0){
                if(swfu_im_{$this->iContainerIDAddition}.settings.button_disabled != true){
                    swfu_im_{$this->iContainerIDAddition}.setButtonDisabled(true);
                }
                $(imageManagerID).find('.maxImagesError').show();
                $(imageManagerID).find('.imagesCountLeft').html(imageCount+'/'+maxImages);
                $(imageManagerID).find('.uploadForm').hide();
            }else{
                $(imageManagerID).find('.uploadForm').show();
                if(swfu_im_{$this->iContainerIDAddition}.settings.button_disabled != false){
                    swfu_im_{$this->iContainerIDAddition}.setButtonDisabled(false);
                    swfu_im_{$this->iContainerIDAddition}.setFileUploadLimit(maxImages-imageCount);
                }
                $(imageManagerID).find('.imagesCountLeft').html(imageCount+'/'+maxImages);
                $(imageManagerID).find('.maxImagesError').hide();
            }
        }
        if(imageCount > 0){
            $(imageManagerID).find('.extraUploadedLine').show();
        }else{
            $(imageManagerID).find('.extraUploadedLine').hide();
        }
    }
    
    // handle swfupload loaded
    function swfupload_loaded_handler(){
        globalFunctions.checkMaxImages{$this->iContainerIDAddition}();
    }
EOT;
    $oPageLayout->addJavascriptBottom('<script>' . $sMultipleFileUploadJavascript . '</script>');
} else {
    $sSingleFileUploadJavascript = <<<EOT
    // count current amount of images and check if upload should be disabled
    globalFunctions.checkMaxImages{$this->iContainerIDAddition} = function (){
        var imageManagerID = '#im_{$this->iContainerIDAddition}';
        var imageCount = $(imageManagerID).find('.images .placeholder').size();
        var maxImages = '{$this->iMaxImages}';
        if(maxImages>0){    
            if((maxImages-imageCount) <= 0){
                $('#saveImageBtn_{$this->iContainerIDAddition}').attr('disabled', true);
                $(imageManagerID).find('.imagesCountLeft').html(imageCount+'/'+maxImages);
                $(imageManagerID).find('.maxImagesError').show();
                $(imageManagerID).find('.uploadForm').hide();
            }else{
                $('#saveImageBtn_{$this->iContainerIDAddition}').attr('disabled', false);
                $(imageManagerID).find('.imagesCountLeft').html(imageCount+'/'+maxImages);
                $(imageManagerID).find('.maxImagesError').hide();
                $(imageManagerID).find('.uploadForm').show();
            }
        }
        if(imageCount > 0){
            $(imageManagerID).find('.extraUploadedLine').show();
        }else{
            $(imageManagerID).find('.extraUploadedLine').hide();
        }
    }
    
EOT;
    $oPageLayout->addJavascriptBottom('<script>' . $sSingleFileUploadJavascript . '</script>');
}
?>