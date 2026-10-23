<div id="fm_<?= $this->iContainerIDAddition ?>">
    <p class="maxFilesError error <?= $this->iMaxFiles > 0 && (count($this->aFiles) >= $this->iMaxFiles) ? '' : 'hide' ?>">
        <i><?= sysTranslations::get('global_file_limit') ?> <?= $this->iMaxFiles ?> <?= sysTranslations::get('global_file_limit_2') ?></i>
    </p>
    <form action="<?= $this->sUploadUrl ?>" onsubmit="if ($(this).valid())
                return hideSubmitShowAjaxLoader('saveFileBtn_<?= $this->iContainerIDAddition ?>', 'ajax-loader');
            else
                return false;" id="_form" class="validateForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $this->sHiddenAction ?>" />
        <table class="withForm">
            <?
            if (!$this->bMultipleFileUpload) {
                ?>
                <tr>
                    <td>
                        <label for="fileTitle_<?= $this->iContainerIDAddition ?>"><?= sysTranslations::get('global_file_description') ?> <?= $this->bTitleRequired ? '*' : '' ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="fileTitle_<?= $this->iContainerIDAddition ?>" maxlength="255" name="title" class="default <?= $this->bTitleRequired ? '{validate:{required:true}}' : '' ?>" <?= $this->sTitleTitle ? 'title="' . $this->sTitleTitle . '"' : '' ?> type="text" value="" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="file" title="<?= $this->sValidateFile ? sysTranslations::get('global_select_extensions') .': '  . $this->sValidateFile : sysTranslations::get('global_select_file') ?>" class="{validate:{required:true<?= $this->sValidateFile ? ', accept:\'' . $this->sValidateFile . '\'' : '' ?>}}" name="file" />
                    </td>
                </tr>
                <?
            }
            if (!$this->bMultipleFileUpload) {
                ?>
                <tr>
                    <td>
                        <img src="<?= ADMIN_FOLDER ?>/images/layout/ajax-loader.gif" class="ajax-loader fileManagement" />
                        <input <?= $this->iMaxFiles > 0 && (count($this->aFiles) >= $this->iMaxFiles) ? 'DISABLED' : '' ?> id="saveFileBtn_<?= $this->iContainerIDAddition ?>" type="submit" name="saveFile" value="<?= sysTranslations::get('global_upload_file') ?>" />
                    </td>
                </tr>
                <?
            } else {
                ?>
                <tr>
                    <td>
                        <span id="upload-files-btn_<?= $this->iContainerIDAddition ?>"></span>
                    </td>
                </tr>
                <tr>
                    <td><div class="progressInfo"></div></td>
                </tr>
                <?
            }
            ?>
        </table>
    </form>
    <hr>
    <?
    $sTooltipTitle = '';
    $sTooltipTitle .= ($sTooltipTitle != '' ? '<br />' : '') . sysTranslations::get('global_drag_lines');
    if ($this->iMaxFiles > 0)
        $sTooltipTitle .= ($sTooltipTitle != '' ? '<br />' : '') . sysTranslations::get('global_max_files') . $this->iMaxFiles . sysTranslations::get('global_max_files_2');
    ?>
    <h3><?= sysTranslations::get('global_already_uploaded') ?><?= $this->iMaxFiles > 0 ? ' (<span class="filesCountLeft">' . count($this->aFiles) . '/' . $this->iMaxFiles . '</span>) ' : '' ?> <div class="hasTooltip tooltip" title="<?= $sTooltipTitle ?>">&nbsp;</div></h3>    
    <ul class="files <?= $this->sortable ? 'sortable' : '' ?>">
        <?
        foreach ($this->aFiles AS $oFile) {
            ?>
            <li id="placeholder-<?= $oFile->mediaId ?>" data-mediaid="<?= $oFile->mediaId ?>" data-title="<?= _e($oFile->title) ?>" class="placeholder">
                <div class="filePlaceholder" title="<?= $oFile->name ?>">
                    <span class="fileType <?= $oFile->getExtension() ?>"></span><a class="title" target="_blank" href="<?= $oFile->link ?>"><?= $oFile->title ? $oFile->title : $oFile->name ?></a>
                </div>
                <div class="actionsPlaceholder">
                    <?
                    if ($this->onlineChangeable) {
                        if ($oFile->isOnlineChangeable()) {
                            echo '<a class="action_icon ' . ($oFile->online ? 'online' : 'offline') . '_icon onlineOfflineBtn" onclick="setOnlineFile(this); return false;" data-online="' . ($oFile->online ? 0 : 1) . '" href="' . $this->changeOnlineLink . '"></a>';
                        } else {
                            echo '<a onclick="return false;" class="action_icon grey ' . ($oFile->online ? 'online' : 'offline') . '_icon onlineOfflineBtn"  href="#"></a>';
                        }
                    }
                    if ($this->editable) {
                        if ($oFile->isEditable()) {
                            echo '<a class="action_icon edit_icon" onclick="showEditFile(this); return false;" href="' . $this->editLink . '"></a>';
                        } else {
                            echo '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
                        }
                    }
                    if ($this->deletable) {
                        if ($oFile->isDeletable()) {
                            echo '<a class="action_icon delete_icon" onclick="deleteFile(this); return false;" href="' . $this->deleteLink . '"></a>';
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
# include editImageForm once
include_once $this->sEditFileFormLocation;

# add sortable javascript initiation code
$sFileManagerJavascript = <<<EOT
$( "div#fm_{$this->iContainerIDAddition} ul.files.sortable").sortable({
    items: '> li',
    placeholder: 'ui-state-highlight placeholder',
    forcePlaceholderSize: true,
    tolerance: 'pointer',
    update: function(event, ui) {
        var mediaIds = new Array();
        ui.item.closest('ul.files').find('> li').each(function(index, value){
            mediaIds[index] = $(value).data('mediaid');
        });
        updateFileOrder(mediaIds, '{$this->saveOrderLink}', 'fm_{$this->iContainerIDAddition}');
    }
});
EOT;
$oPageLayout->addJavascriptBottom('<script>' . $sFileManagerJavascript . '</script>');

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

    $sMultipleFileUploadJavascript = <<<EOT

        var swfu_fm_{$this->iContainerIDAddition} = new SWFUpload({
        upload_url : "{$this->sUploadUrl}",
        file_post_name : "file",
        post_params : {
            "action" : "saveFile",
            "SWFUpload" : true,
            "PHPSESSID" : "$sSessionId"
        },
        flash_url : "$sAdminPluginsFolder/SWFUpload/Flash/swfupload.swf",
        button_placeholder_id : "upload-files-btn_{$this->iContainerIDAddition}",
        button_image_url : '$sAdminFolder/images/layout/select-files.jpg',
        button_width : 229,
        button_height : 28,
        $sAllowedExtensions
        $sFileSizeLimit
        button_cursor : SWFUpload.CURSOR.HAND,
        button_window_mode : SWFUpload.WINDOW_MODE.OPAQUE,
        
        swfupload_loaded_handler : swfupload_loaded_handler,
        file_queue_error_handler : file_queue_error_handler,
        file_dialog_complete_handler : file_dialog_complete_handler,
        
        upload_start_handler : upload_start_handler,
        upload_progress_handler : upload_progress_handler,
        upload_error_handler : upload_error_handler,
        
        upload_success_handler : upload_success_handler_files,
        upload_complete_handler : upload_complete_handler,
        custom_settings : {
            progressTarget : "progressInfo",
            formID : "_form",
            onlineChangeable : $bOnlineChangable,
            editable : $bEditable,
            deletable : $bDeletable,
            changeOnlineLink : '{$this->changeOnlineLink}',
            editLink : '{$this->editLink}',
            deleteLink : '{$this->deleteLink}',
            containerID : 'fm_{$this->iContainerIDAddition}'
        },
        //debug : false,
        //debug_handler : debug_handler
    });
    
    // count current amount of files and check if upload should be disabled
    function checkMaxFiles(){
        var fileManagerID = '#fm_{$this->iContainerIDAddition}';
        var fileCount = $(fileManagerID).find('.placeholder').size();
        var maxFiles = '{$this->iMaxFiles}';
        if(maxFiles>0){    
            if((maxFiles-fileCount) <= 0){
                if(swfu_fm_{$this->iContainerIDAddition}.settings.button_disabled != true){
                    swfu_fm_{$this->iContainerIDAddition}.setButtonDisabled(true);
                }
                $(fileManagerID).find('.maxFilesError').show();
                $(fileManagerID).find('.filesCountLeft').html(fileCount+'/'+maxFiles);
            }else{
                if(swfu_fm_{$this->iContainerIDAddition}.settings.button_disabled != false){
                    swfu_fm_{$this->iContainerIDAddition}.setButtonDisabled(false);
                    swfu_fm_{$this->iContainerIDAddition}.setFileUploadLimit(maxFiles-fileCount);
                }
                $(fileManagerID).find('.filesCountLeft').html(fileCount+'/'+maxFiles);
                $(fileManagerID).find('.maxFilesError').hide();
            }
        }
    }
    
    // handle swfupload loaded
    function swfupload_loaded_handler(){
        checkMaxFiles();
    }
EOT;
    $oPageLayout->addJavascriptBottom('<script>' . $sMultipleFileUploadJavascript . '</script>');
} else {
    $sSingleFileUploadJavascript = <<<EOT
    // count current amount of files and check if upload should be disabled
    function checkMaxFiles(){
        var fileManagerID = '#fm_{$this->iContainerIDAddition}';
        var fileCount = $(fileManagerID).find('.placeholder').size();
        var maxFiles = '{$this->iMaxFiles}';
        if(maxFiles>0){    
            if((maxFiles-fileCount) <= 0){
                $('#saveFileBtn_{$this->iContainerIDAddition}').attr('disabled', true);
                $(fileManagerID).find('.filesCountLeft').html(fileCount+'/'+maxFiles);
                $(fileManagerID).find('.maxFilesError').show();
            }else{
                $('#saveFileBtn_{$this->iContainerIDAddition}').attr('disabled', false);
                $(fileManagerID).find('.filesCountLeft').html(fileCount+'/'+maxFiles);
                $(fileManagerID).find('.maxFilesError').hide();
            }
        }
    }
EOT;
    $oPageLayout->addJavascriptBottom('<script>' . $sSingleFileUploadJavascript . '</script>');
}
?>