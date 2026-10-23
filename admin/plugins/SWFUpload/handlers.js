/**
 * dump debug info to screen
 * @param e string
 */
function debug_handler(e) {
    _d(e);
}

/**
 * start uploading after closing dialog
 * @param numFilesSelected int
 * @param numFilesQueued int
 */
function file_dialog_complete_handler(numFilesSelected, numFilesQueued) {

    numFilesQueued = numFilesQueued;

    try {
        if (numFilesQueued > 0) {
            this.startUpload();
        }
    } catch (ex) {
        this.debug(ex);
    }
}
/**
 * handle upload before start
 */
function upload_start_handler(file) {
    // add post param extra-option
    this.addPostParam('extra-option', $('#' + this.customSettings.containerID + ' select[name="extra-option"]').val());
}

function upload_progress_handler(file, bytesLoaded) {
    try {
        // calulate percentage
        var percent = Math.ceil((bytesLoaded / file.size) * 100);

        // weird more than 100% showing fix
        if (percent > 100)
            percent = 100;

        // set progress container with status, name and percentage
        var progress = new FileProgressHandler(file, this.customSettings.containerID, this.customSettings.progressTarget);
        progress.setProgressName(file.name + ' (' + percent + '%)');
        progress.setProgressBar(percent);

        // calulate files2Go from queue
        var queued = parseInt(this.getStats().files_queued);
        var successfullUploads = parseInt(this.getStats().successful_uploads);
        var totaal = queued + successfullUploads;
        progress.setProgressFiles2Go('(' + (successfullUploads + 1) + '/' + totaal + ')');

        // set progress status
        if (percent === 100) {
            progress.setProgressStatus(jsTranslations['js_swfupload_storing']);
        } else {
            progress.setProgressStatus(jsTranslations['js_swfupload_uploading']);
        }
    } catch (ex) {
        this.debug(ex);
    }
}

/**
 * handle upload success for images
 * @param file object
 * @param serverData string (json encoded)
 */
function upload_success_handler_images(file, serverData) {
    try {
        var progress = new FileProgressHandler(file, this.customSettings.containerID, this.customSettings.progressTarget);

        // add image to list
        var dataObj = eval('(' + serverData + ')');
        if (dataObj.success === true) {
            addImage(dataObj.imageFile, this);
            // if checkMaxImages exists, call it
            if (typeof globalFunctions['checkMaxImages' + this.customSettings.containerIDAddition] == 'function') {
                globalFunctions['checkMaxImages' + this.customSettings.containerIDAddition]();
            }
        } else {
            alert(dataObj.errorMsg);
        }
    } catch (ex) {
        this.debug(ex);
    }
}
/**
 * handle upload success for files
 * @param file object
 * @param serverData string (json encoded)
 */
function upload_success_handler_files(file, serverData) {
    try {
        var progress = new FileProgressHandler(file, this.customSettings.containerID, this.customSettings.progressTarget);

        // add image to list
        var dataObj = eval('(' + serverData + ')');
        if (dataObj.success === true) {
            addFile(dataObj.file, this);
            // if checkMaxFiles exists, call it
            if (typeof checkMaxFiles == 'function') {
                checkMaxFiles();
            }
        } else {
            alert(dataObj.errorMsg);
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function upload_complete_handler(file) {
    try {
        // start next upload
        if (this.getStats().files_queued > 0) {
            this.startUpload();
        } else {
            // reset stats
            var stats = {
                successful_uploads: 0, // The number of files that have uploaded successfully (caused uploadSuccess to be fired)
                upload_errors: 0, // The number of files that have had errors (excluding cancelled files)
                upload_cancelled: 0, // The number of files that have been cancelled
                queue_errors: 0 // The number of files that caused fileQueueError to be fired }
            };
            this.setStats(stats);

            // show end message
            var progress = new FileProgressHandler(file, this.customSettings.containerID, this.customSettings.progressTarget);
            progress.setProgressName(jsTranslations['js_swfupload_uploaded']);
            progress.setProgressStatus(jsTranslations['js_swfupload_finished']);
            progress.hide();
        }
    } catch (ex) {
        this.debug(ex);
    }
}

/**
 * handle upload error
 * @param file object
 * @param errorCode int
 * @param message string
 */
function upload_error_handler(file, errorCode, message) {
    try {
        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                try {
                    alert(jsTranslations['js_swfupload_canceled'] + " " + file.name);
                }
                catch (ex1) {
                    this.debug(ex1);
                }
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                try {
                    alert(jsTranslations['js_swfupload_stopped'] + " " + file.name);
                }
                catch (ex2) {
                    this.debug(ex2);
                }
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                alert(jsTranslations['js_swfupload_limit_exceeded'] + " " + file.name);
                break;
            default:
                alert(message);
                break;
        }
    } catch (ex3) {
        this.debug(ex3);
    }
}

/**
 * handle file queue error
 * @param file object
 * @param errorCode int
 * @param message string
 */
function file_queue_error_handler(file, errorCode, message) {
    try {
        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                alert(jsTranslations['js_swfupload_zero_byte'] + " " + file.name);
                break;
            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                alert(jsTranslations['js_swfupload_max_files_1'] + " " + message + " " + jsTranslations['js_swfupload_max_files_2']);
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                alert(jsTranslations['js_swfupload_too_big'] + " " + file.name);
                break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                alert(jsTranslations['js_swfupload_wrong_type'] + " " + file.name);
            default:
                alert(message);
                break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}

/**
 * add image to list
 * @param imageFile object
 * @param swfObject object
 */
function addImage(imageFile, swfObject) {

    var html = '';
    html += '<div id="placeholder-' + imageFile.imageId + '" style="display:none;" imageId="' + imageFile.imageId + '" class="placeholder">';

    if (!imageFile.hasNeededImageFiles)
        html += '<img class="notAllCrops" src="/admin/images/layout/icons/exclamation_icon.png" alt="' + jsTranslations['js_swfupload_no_cutout'] + '" title="' + jsTranslations['js_swfupload_no_cutout'] + '" />';

    html += '<div class="imagePlaceholder">';
    html += '<div class="centered">';
    html += '<img src="' + imageFile.link + '" alt="' + imageFile.title + '" title="' + imageFile.title + '" />';
    html += '</div>';
    html += '</div>';
    html += '<div class="actionsPlaceholder">';
    if (swfObject.customSettings.onlineChangeable) {
        if (imageFile.isOnlineChangeable) {
            html += '<a class="action_icon ' + (imageFile.online ? 'online' : 'offline') + '_icon onlineOfflineBtn" onclick="setOnlineImage(this); return false;" online="' + (imageFile.online ? 0 : 1) + '" href="' + swfObject.customSettings.changeOnlineLink + '"></a>';
        } else {
            html += '<a onclick="return false;" class="action_icon grey ' + (imageFile.online ? 'online' : 'offline') + '_icon onlineOfflineBtn"  href="#"></a>';
        }
    }
    if (swfObject.customSettings.cropable) {
        if (imageFile.isCropable) {
            html += '<a class="action_icon crop_icon" href="' + swfObject.customSettings.cropLink + '?imageId=' + imageFile.imageId + '"></a>';
        } else {
            html += '<a onclick="return false;" class="action_icon grey crop_icon" href="#"></a>';
        }
    }
    if (swfObject.customSettings.editable) {
        if (imageFile.isEditable) {
            html += '<a class="action_icon edit_icon" onclick="showEditImage(this); return false;" href="' + swfObject.customSettings.editLink + '"></a>';
        } else {
            html += '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
        }
    }
    if (swfObject.customSettings.deletable) {
        if (imageFile.isDeletable) {
            html += '<a class="action_icon delete_icon" onclick="deleteImage(this); return false;" href="' + swfObject.customSettings.deleteLink + '"></a>';
        } else {
            html += '<a class="action_icon grey delete_icon" onclick="return false;" href="#"></a>';
        }
    }
    html += '</div>';
    html += '</div>';

    // insert html to DOM
    $('#' + swfObject.customSettings.containerID + ' .images').append(html);

    var imageCount = $('#' + swfObject.customSettings.containerID + ' .images .placeholder').size();

    //show other text after uploading first image (no images uploaded text will change)
    if (imageCount == 1) {
        $('#' + swfObject.customSettings.containerID + ' .imagesText').show();
        $('#' + swfObject.customSettings.containerID + ' .noImagesText').hide();
    }

    $('#placeholder-' + imageFile.imageId).show(500);

    if (imageCount == 1 && swfObject.customSettings.coverImageShow) {
        $('#' + swfObject.customSettings.containerID + ' .coverImageContainer').find('.imagePlaceholder').html('<img src="' + imageFile.link + '" data-imageid="' + imageFile.imageId + '" />');
        $('#' + swfObject.customSettings.containerID + ' .coverImageContainer').show();
        $('#' + swfObject.customSettings.containerID + ' .coverImageContainer .placeholder').effect('highlight', 1500);
    }
}

function addFile(file, swfObject) {
    var html = '';
    html += '<li id="placeholder-' + file.mediaId + '" style="display:none;" mediaId="' + file.mediaId + '" class="placeholder">';
    html += '<div class="filePlaceholder" title="' + file.title + '">';
    html += '<span class="fileType ' + file.extension + '"></span><a target="_blank" href="' + file.link + '">' + file.name + '</a>';
    html += '</div>';
    html += '<div class="actionsPlaceholder">';
    if (swfObject.customSettings.onlineChangeable) {
        if (file.isOnlineChangeable) {
            html += '<a class="action_icon ' + (file.online ? 'online' : 'offline') + '_icon onlineOfflineBtn" onclick="setOnlineFile(this); return false;" online="' + (file.online ? 0 : 1) + '" href="' + swfObject.customSettings.changeOnlineLink + '"></a>';
        } else {
            html += '<a onclick="return false;" class="action_icon grey ' + (file.online ? 'online' : 'offline') + '_icon onlineOfflineBtn"  href="#"></a>';
        }
    }
    if (swfObject.customSettings.editable) {
        if (file.isEditable) {
            html += '<a class="action_icon edit_icon" onclick="showEditFile(this); return false;" href="' + swfObject.customSettings.editLink + '"></a>';
        } else {
            html += '<a class="action_icon grey edit_icon" onclick="return false;" href="#"></a>';
        }
    }
    if (swfObject.customSettings.deletable) {
        if (file.isDeletable) {
            html += '<a class="action_icon delete_icon" onclick="deleteFile(this); return false;" href="' + swfObject.customSettings.deleteLink + '"></a>';
        } else {
            html += '<a class="action_icon grey delete_icon" onclick="return false;" href="#"></a>';
        }
    }
    html += '</div>';
    html += '</li>';

    // insert html to DOM
    $('#' + swfObject.customSettings.containerID + ' .files').append(html);

    //show other text after uploading first file (no files uploaded text will change)
    if ($('#' + swfObject.customSettings.containerID + ' .files .placeholder').size() == 1) {
        $('#' + swfObject.customSettings.containerID + ' .filesText').show();
        $('#' + swfObject.customSettings.containerID + ' .noFilesText').hide();
    }

    $('#placeholder-' + file.mediaId).show(500);
}