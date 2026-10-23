/*
 * basic functionality used on many pages
 */

/**
 * log value to console
 * @param sValue mixed
 */
function _d(mValue) {
    if (console) {
        if (console.log) {
            console.log(mValue);
        }
    } else {
        return false;
    }
    return true;
}

/**
 * confirm a onclick
 * @param subject string
 * @param addToSentence string
 * @return mixed true/false/redirect
 */
function confirmChoice(subject, addToSentence) {
    var message = jsTranslations['js_sure_delete_1'] + ' "' + subject + '" ' + jsTranslations['js_sure_delete_2'];
    if(typeof(addToSentence)!=='undefined') {
        message = message + " " + addToSentence;
    }
    return confirm(message);
}

/**
 * show the status update
 * @param message string
 */
function showStatusUpdate(message) {

    // message box
    var mb = $('#statusUpdate')
            .html(message)
            .stop(true, true).show('highlight', {
        color: "#d7ed44"
    }, 1500);

    //if delay is set, clear delay
    if (mb.data('delay'))
        clearTimeout(mb.data('delay'));

    // set delay again
    mb.data('delay', setTimeout(function() {
        mb.fadeOut(1000);
    }, 3500));
}


/**
 * initialize tinymce default
 */
function initTinyMCE(selector, link_list, image_list, image, width, content_css, lang) {

    if (lang === undefined) {
        lang = 'nl';
    }
    
    if (image_list) {
        image = true;
    }
    
    if (width === undefined || width === 450) {
        width = 450;
    }
    
    if (content_css === undefined) {
        content_css = '/admin/css/FE_style.css?cache=' + new Date().getTime(); //website stylesheet frontend
    }
    

    //tiny_MCE initiation
    tinymce.init({
        // General options
        selector: selector,
        theme: 'modern',
        plugins: 'paste tabfocus table autolink code link image visualchars visualblocks searchreplace charmap autoresize contextmenu',
        content_css: content_css, 
        resize: 'both',
        convert_urls: false,
        relative_urls: false,
        paste_as_text: true,
        language: lang,
        image_advtab: true,
        menu: {
            edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall'},
            insert: {title: 'Insert', items: (image ? ' image' : '') + ' link charmap'},
            table: {title: 'Table', items: 'inserttable | cell row column deletetable'},
            tools: {title: 'Tools', items: 'visualchars visualblocks searchreplace code'}
        },
        toolbar1: 'bold italic underline strikethrough superscript subscript removeformat | alignleft  aligncenter  alignright | pastetext',
        toolbar2: 'undo redo | formatselect | bullist numlist | charmap | link unlink ' + (image ? '| image' : ''),
        block_formats: 'Paragraph=p;Header 2=h2;Header 3=h3',
        width: width,
        autoresize_min_height: 50,
        autoresize_max_height: 500,
        contextmenu: 'link image inserttable | cell row column deletetable',
        extended_valid_elements: 'img[!src|border:0|alt|title|width|height|style]a[name|href|target|title|onclick]',
        image_list: image_list,
        link_list: link_list,
        statusbar : false,
        theme_advanced_resizing: true,
        theme_advanced_resizing_use_cookie : false
    });
    
}

///**
// * initialize tinymce default
// */
//function initTinyMCE(selector, link_list, image_list, image, width, content_css, lang) {
//
//    if (lang === undefined) {
//        lang = 'nl';
//    }
//    
//    if (image_list) {
//        image = true;
//    }
//    
//    if (width === undefined || width === 450) {
//        width = 450;
//    }
//    
//    if (content_css === undefined) {
//        content_css = '/admin/css/FE_style.css?cache=' + new Date().getTime(); //website stylesheet frontend
//    }
//    
//
//    //tiny_MCE initiation
//    tinymce.init({
//        // General options
//        selector: selector,
//        theme: 'modern',
//        plugins: 'paste tabfocus table autolink code link image visualchars visualblocks searchreplace charmap autoresize contextmenu',
//        content_css: content_css, 
//        resize: 'both',
//        convert_urls: false,
//        relative_urls: false,
//        paste_as_text: true,
//        language: lang,
//        image_advtab: true,
//        menu: {
//            edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall'},
//            insert: {title: 'Insert', items: (image ? ' image' : '') + ' link charmap'},
//            table: {title: 'Table', items: 'inserttable | cell row column deletetable'},
//            tools: {title: 'Tools', items: 'visualchars visualblocks searchreplace code'}
//        },
//        toolbar1: 'bold italic underline strikethrough superscript subscript removeformat | alignleft  aligncenter  alignright | pastetext',
//        toolbar2: 'undo redo | formatselect | bullist numlist | charmap | link unlink ' + (image ? '| image' : ''),
//        block_formats: 'Paragraph=p;Header 2=h2;Header 3=h3',
//        width: width,
//        autoresize_min_height: 50,
//        autoresize_max_height: 500,
//        contextmenu: 'link image inserttable | cell row column deletetable',
//        extended_valid_elements: 'img[!src|border:0|alt|title|width|height|style]a[name|href|target|title|onclick]',
//        image_list: image_list,
//        link_list: link_list,
//        statusbar : false,
//        theme_advanced_resizing: true,
//        theme_advanced_resizing_use_cookie : false
//    });
//    
//}

/**
 * set table sorter functionality
 */
function setTableSorterStuff() {
    // table sorter set widgte to zebra
    $.tablesorter.defaults.widgets = ['zebra'];

    // extra tablesorter parser for Dutch date format: dd/mm/yyyy nothing in front of the values!!
    $.tablesorter.addParser({
        id: 'dateNL',
        is: function(s) {
            return false;
        },
        format: function(s) {
            var date = s.match(/(\d{1,2})[\/\-]?(\d{1,2})[\/\-]?(\d{4})( (\d{1,2}):(\d{1,2})(:(\d{1,2}))?)?$/);

            if (date == null)
                return '' + 0;

            var d = String(date[1]);
            var m = String(date[2]);
            var y = String(date[3]);
            var H = (date[5] ? String(date[5]) : "00");
            var M = (date[6] ? String(date[6]) : "00");
            var S = (date[8] ? String(date[8]) : "00");

            if (d.length == 1) {
                d = "0" + d;
            }
            if (m.length == 1) {
                m = "0" + m;
            }
            if (y.length == 2) {
                y = "20" + y;
            }

            if (H.length == 1) {
                H = "0" + H;
            }
            if (M.length == 1) {
                M = "0" + M;
            }
            if (S.length == 1) {
                S = "0" + S;
            }

            return '' + y + m + d + H + M + S;
        },
        type: 'numeric'
    });

    //add tablesorter to tables with sorted class
    $("table.sorted").tablesorter();
}

/**
 * set default validation trough jqeury
 * also add errors div construction for fancybox showing errors
 */
function setDefaultValidationStuff() {
    /* validate form with fancybox */
    $("form.validateForm").each(function() {
        $(this).validate({
            focusInvalid: true,
            errorLabelContainer: "#validationErrors ul#errors",
            wrapper: "li",
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    setTimeout("$(\"#validationErrorsLink\").click();", 200);
                }
            },
            meta: 'validate'
        });
    });
}

/**
 * remove submit button and show ajax-loader
 * @param submitID string if of the submit element to replace
 * @param loaderClass string class of the loader to display
 */
function hideSubmitShowAjaxLoader(submitID, loaderClass) {
    $('#' + submitID).closest('form').find('.' + loaderClass).css('display', 'block');
    $('#' + submitID).remove();
    return true;
}

/**
 * delete an image and on success its placeholder
 * @param element clicked <a> element
 */
function deleteImage(element) {

    // confirm deleting image
    if (confirmChoice(jsTranslations['js_this_image']) !== true) {
        return false;
    }

    jElement = $(element);
    var addition = jElement.closest('.im_container').data('addition');
    var imageId = jElement.closest('.placeholder').attr("imageId");
    var jContainerElement = jElement.closest('.im_container');

    $.ajax({
        type: 'POST',
        url: jElement.attr("href"),
        data: 'imageId=' + imageId,
        success: function(data) {
            var dataObj = eval("(" + data + ")");

            if (dataObj.success === true) {
                $("div.images #placeholder-" + dataObj.imageId).hide(750, function() {
                    $(this).remove();
                    if (jContainerElement.find('.images .placeholder').size() == 0) {
                        jContainerElement.find('.imagesText').hide();
                        jContainerElement.find('.noImagesText').show();
                    }

                    // if checkMaxImages exists, call it
                    if (typeof globalFunctions['checkMaxImages' + addition] == 'function') {
                        globalFunctions['checkMaxImages' + addition]();
                    }

                    // if updateCoverImageAfterDelete exists, call it
                    if (typeof globalFunctions['updateCoverImageAfterDelete' + addition] == 'function') {
                        globalFunctions['updateCoverImageAfterDelete' + addition](imageId);
                    }
                });
                showStatusUpdate(jsTranslations['js_image_deleted']);
            } else {
                showStatusUpdate(jsTranslations['js_image_not_deleted']);
            }
        }
    });
    return true;
}

/**
 * set an image to online/offline
 * @param element (clicked <a> element)
 */
function setOnlineImage(element) {

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr("href"),
        data: 'imageId=' + jElement.closest('.placeholder').attr("imageId") + '&online=' + jElement.attr("online"),
        success: function(data) {
            var dataObj = eval("(" + data + ")");

            if (dataObj.success === true) {
                // set online/offline btn to right value value will be online?
                if (dataObj.online === 1) {
                    $("div.images #placeholder-" + dataObj.imageId + " a.onlineOfflineBtn").attr("online", 1).removeClass('online_icon').addClass('offline_icon');
                    $("div.images #placeholder-" + dataObj.imageId + "").effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_image_offline']);
                } else {
                    $("div.images #placeholder-" + dataObj.imageId + " a.onlineOfflineBtn").attr("online", 0).removeClass('offline_icon').addClass('online_icon');
                    $("div.images #placeholder-" + dataObj.imageId + "").effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_image_offline']);
                }
            } else {
                $("div.images #placeholder-" + dataObj.imageId + "").effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_image_not_changed']);
            }
        }
    });
}

/**
 * edit an image
 * @param element element to get data from
 */
function showEditImage(element) {

    jElement = $(element);
    jPlaceholder = $(element).closest(".placeholder");

    // set src for editing image title
    $('#editImageForm form').attr('action', jElement.attr("href"));
    $('#editImageForm img').attr('src', jPlaceholder.find('img').attr('src'));
    $('#editImageForm input[name="title"]').val(jPlaceholder.find('img').attr('title'));
    $('#editImageForm input[name="imageId"]').val(jPlaceholder.attr("imageId"));
    $('#editImageFormLink').click();
}

/**
 * save image from form
 * @param element (form element)
 */
function saveImage(element) {
    var jForm = $(element);

    $.ajax({
        type: 'POST',
        url: jForm.attr("action"),
        data: jForm.serialize(),
        success: function(data) {
            var dataObj = eval("(" + data + ")");
            if (dataObj.success === true) {
                //change title from image in list
                $("div.images #placeholder-" + dataObj.imageId + " img").attr("title", dataObj.title).attr("alt", dataObj.title);
                $.fancybox.close(); //close fancybox after saving
                setTimeout(function() {
                    $("div.images #placeholder-" + dataObj.imageId + "").effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_image_title_changed']);
                }, 800);// timeout for closing fancybox first
            } else {
                showStatusUpdate(jsTranslations['js_image_not_changed']);
            }
        }
    });
}

/**
 * save image order
 * @param imageIds int
 * @param url string url to save order
 * @param containerID string id of the overall container
 */
function updateImageOrder(imageIds, url, containerID) {
    $.ajax({
        type: 'POST',
        url: url,
        data: 'imageIds=' + imageIds.join(','),
        success: function(data) {
            var dataObj = eval("(" + data + ")");
            if (dataObj.success === true) {
                $('#' + containerID + " div.images > div.placeholder").effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_image_order_changed']);
            } else {
                showStatusUpdate(jsTranslations['js_image_order_not_changed']);
            }
        }
    });
}

/**
 * save file order
 * @param mediaIds int
 * @param url string url to save order
 * @param containerID string id of the overall container
 */
function updateFileOrder(mediaIds, url, containerID) {
    $.ajax({
        type: 'POST',
        url: url,
        data: 'mediaIds=' + mediaIds.join(','),
        success: function(data) {
            var dataObj = eval("(" + data + ")");
            if (dataObj.success === true) {
                $("#" + containerID + " ul.files > li.placeholder").effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_files_order_changed']);
            } else {
                showStatusUpdate(jsTranslations['js_file_not_changed']);
            }
        }
    });
}

/**
 * set a file to online/offline
 * @param element (clicked <a> element)
 */
function setOnlineFile(element) {
    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr('href'),
        data: 'mediaId=' + jElement.closest('.placeholder').data('mediaid') + '&online=' + jElement.data('online'),
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success === true) {
                // set online/offline btn to right value value will be online?
                if (dataObj.online === 1) {
                    $('ul.files #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 1).removeClass('online_icon').addClass('offline_icon');
                    $('ul.files #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_files_offline']);
                } else {
                    $('ul.files #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 0).removeClass('offline_icon').addClass('online_icon');
                    $('ul.files #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_files_online']);
                }
            } else {
                $('ul.files #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_file_not_changed']);
            }
        }
    });
}

/**
 * edit a file
 * @param element element to get data from
 */
function showEditFile(element) {

    jElement = $(element);
    jPlaceholder = $(element).closest('.placeholder');

    // set src for editing image title
    $('#editFileForm form').attr('action', jElement.attr('href'));
    $('#editFileForm input[name="title"]').val(jPlaceholder.data('title'));
    $('#editFileForm input[name="mediaId"]').val(jPlaceholder.data("mediaid"));
    $('#editFileFormLink').click();
}

/**
 * save file from form
 * @param element (form element)
 */
function saveFile(element) {
    var jForm = $(element);

    $.ajax({
        type: 'POST',
        url: jForm.attr('action'),
        data: jForm.serialize(),
        success: function(data) {
            var dataObj = eval('(' + data + ')');
            if (dataObj.success === true) {
                //change title from image in list
                $('ul.files #placeholder-' + dataObj.mediaId).data('title', dataObj.title);
                $('ul.files #placeholder-' + dataObj.mediaId + ' .filePlaceholder .title').html(dataObj.title ? dataObj.title : dataObj.name);
                $.fancybox.close(); //close fancybox after saving
                setTimeout(function() {
                    $('ul.files #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_file_title_changed']);
                }, 800);// timeout for closing fancybox first
            } else {
                showStatusUpdate(jsTranslations['js_file_not_changed']);
            }
        }
    });
}

/**
 * delete a file and on success its placeholder
 * @param element clicked <a> element
 */
function deleteFile(element) {

    // confirm deleting image
    if (confirmChoice('dit bestand') !== true) {
        return false;
    }

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr('href'),
        data: 'mediaId=' + jElement.closest('.placeholder').data('mediaid'),
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success === true) {
                $('ul.files #placeholder-' + dataObj.mediaId).hide(750, function() {
                    $(this).remove();
                    // if checkMaxFiles exists, call it
                    if (typeof checkMaxFiles == 'function') {
                        checkMaxFiles();
                    }
                });
                showStatusUpdate(jsTranslations['js_file_deleted']);
            } else {
                showStatusUpdate(jsTranslations['js_file_not_deleted']);
            }
        }
    });
    return true;
}

/**
 * save link order
 * @param mediaIds int
 * @param url string url to save order
 * @param containerID string id of the overall container
 */
function updateLinkOrder(mediaIds, url, containerID) {
    $.ajax({
        type: 'POST',
        url: url,
        data: 'mediaIds=' + mediaIds.join(','),
        success: function(data) {
            var dataObj = eval("(" + data + ")");
            if (dataObj.success === true) {
                $("#" + containerID + " ul.links > li.placeholder").effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_link_order_changed']);
            } else {
                showStatusUpdate(jsTranslations['js_link_order_not_changed']);
            }
        }
    });
}

/**
 * set a link to online/offline
 * @param element (clicked <a> element)
 */
function setOnlineLink(element) {

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr('href'),
        data: 'mediaId=' + jElement.closest('.placeholder').data('mediaid') + '&online=' + jElement.data('online'),
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success === true) {
                // set online/offline btn to right value value will be online?
                if (dataObj.online === 1) {
                    $('ul.links #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 1).removeClass('online_icon').addClass('offline_icon');
                    $('ul.links #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_link_offline']);
                } else {
                    $('ul.links #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 0).removeClass('offline_icon').addClass('online_icon');
                    $('ul.links #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_link_online']);
                }
            } else {
                $('ul.links #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_link_not_changed']);
            }
        }
    });
}

/**
 * edit a link
 * @param element element to get data from
 */
function showEditLink(element) {

    jElement = $(element);
    jPlaceholder = $(element).closest(".placeholder");

    // set src for editing image title
    $('#editLinkForm form').attr('action', jElement.attr('href'));
    $('#editLinkForm input[name="title"]').val(jPlaceholder.data('title'));
    $('#editLinkForm input[name="link"]').val(jPlaceholder.data('link'));
    $('#editLinkForm input[name="mediaId"]').val(jPlaceholder.data("mediaid"));
    $('#editLinkFormLink').click();
}

/**
 * save file from form
 * @param element (form element)
 */
function saveLink(element) {
    var jForm = $(element);

    $.ajax({
        type: 'POST',
        url: jForm.attr('action'),
        data: jForm.serialize(),
        success: function(data) {
            var dataObj = eval('(' + data + ')');
            if (dataObj.success === true) {
                //change title from image in list
                $('ul.links #placeholder-' + dataObj.mediaId).data('title', dataObj.title);
                $('ul.links #placeholder-' + dataObj.mediaId + ' .linkPlaceholder').attr('title', dataObj.link);
                $('ul.links #placeholder-' + dataObj.mediaId + ' .linkPlaceholder .title').html(dataObj.title ? dataObj.title : dataObj.link);
                $('ul.links #placeholder-' + dataObj.mediaId).data('url', dataObj.link);
                $('ul.links #placeholder-' + dataObj.mediaId + ' .linkPlaceholder a').attr('href', dataObj.link);
                $.fancybox.close(); //close fancybox after saving
                setTimeout(function() {
                    $('ul.links #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_link_saved']);
                }, 800);// timeout for closing fancybox first
            } else {
                showStatusUpdate(jsTranslations['js_link_order_not_changed']);
            }
        }
    });
}

/**
 * delete a file and on success its placeholder
 * @param element clicked <a> element
 */
function deleteLink(element) {

    // confirm deleting image
    if (confirmChoice('deze link') !== true) {
        return false;
    }

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr('href'),
        data: 'mediaId=' + jElement.closest('.placeholder').data('mediaid'),
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success === true) {
                $('ul.links #placeholder-' + dataObj.mediaId).hide(750, function() {
                    $(this).remove();
                });
                showStatusUpdate(jsTranslations['js_link_deleted']);
            } else {
                showStatusUpdate(jsTranslations['js_link_not_deleted']);
            }
        }
    });
    return true;
}

/**
 * save youtubeLink order
 * @param mediaIds int
 * @param url string url to save order
 * @param containerID string id of the overall container
 */
function updateYoutubeLinkOrder(mediaIds, url, containerID) {
    $.ajax({
        type: 'POST',
        url: url,
        data: 'mediaIds=' + mediaIds.join(','),
        success: function(data) {
            var dataObj = eval('(' + data + ')');
            if (dataObj.success === true) {
                $('#' + containerID + ' ul.youtubeLinks > li.placeholder').effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_youtube_order_changed']);
            } else {
                showStatusUpdate(jsTranslations['js_youtube_not_changed']);
            }
        }
    });
}

/**
 * set a link to online/offline
 * @param element (clicked <a> element)
 */
function setOnlineYoutubeLink(element) {

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr('href'),
        data: 'mediaId=' + jElement.closest('.placeholder').data('mediaid') + '&online=' + jElement.data('online'),
        success: function(data) {
            var dataObj = eval('(' + data + ')');

            if (dataObj.success === true) {
                // set online/offline btn to right value value will be online?
                if (dataObj.online === 1) {
                    $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 1).removeClass('online_icon').addClass('offline_icon');
                    $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_link_offline']);
                } else {
                    $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' a.onlineOfflineBtn').data('online', 0).removeClass('offline_icon').addClass('online_icon');
                    $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_link_online']);
                }
            } else {
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                showStatusUpdate(jsTranslations['js_youtube_not_changed']);
            }
        }
    });
}

/**
 * edit a youtubeLink
 * @param element element to get data from
 */
function showEditYoutubeLink(element) {

    jElement = $(element);
    jPlaceholder = $(element).closest(".placeholder");

    // set src for editing youtubeLink
    $('#editYoutubeLinkForm form').attr('action', jElement.attr('href'));
    $('#editYoutubeLinkForm input[name="title"]').val(jPlaceholder.data('title'));
    $('#editYoutubeLinkForm input[name="link"]').val(jPlaceholder.data('link'));
    $('#editYoutubeLinkForm input[name="mediaId"]').val(jPlaceholder.data("mediaid"));
    $('#editYoutubeLinkFormLink').click();
}

/**
 * save youtubeLink from form
 * @param element (form element)
 */
function saveYoutubeLink(element) {
    var jForm = $(element);

    $.ajax({
        type: 'POST',
        url: jForm.attr('action'),
        data: jForm.serialize(),
        success: function(data) {
            var dataObj = eval('(' + data + ')');
            if (dataObj.success === true) {
                //change title from image in list
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).data('title', dataObj.title);
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).data('link', dataObj.link);

                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeLinkPlaceholder').attr('title', dataObj.link);

                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeLinkPlaceholder .title').html(dataObj.title);
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeLinkPlaceholder a').attr('href', dataObj.embedLink);

                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeThumbsPlaceholder img.thumb1').attr('src', dataObj.thumbLink1);
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeThumbsPlaceholder img.thumb2').attr('src', dataObj.thumbLink2);
                $('ul.youtubeLinks #placeholder-' + dataObj.mediaId + ' .youtubeThumbsPlaceholder img.thumb3').attr('src', dataObj.thumbLink3);
                $.fancybox.close(); //close fancybox after saving
                setTimeout(function() {
                    $('ul.youtubeLinks #placeholder-' + dataObj.mediaId).effect('highlight', 1500);
                    showStatusUpdate(jsTranslations['js_youtube_saved']);
                }, 800);// timeout for closing fancybox first
            } else {
                showStatusUpdate(jsTranslations['js_youtube_not_changed']);
            }
        }
    });
}

/**
 * delete a youtubeLink and on success its placeholder
 * @param element clicked <a> element
 */
function deleteYoutubeLink(element) {

    // confirm deleting image
    if (confirmChoice('deze youtube link') !== true) {
        return false;
    }

    jElement = $(element);

    $.ajax({
        type: 'POST',
        url: jElement.attr("href"),
        data: 'mediaId=' + jElement.closest('.placeholder').data("mediaid"),
        success: function(data) {
            var dataObj = eval("(" + data + ")");

            if (dataObj.success === true) {
                $("ul.youtubeLinks #placeholder-" + dataObj.mediaId).hide(750, function() {
                    $(this).remove();
                });
                showStatusUpdate(jsTranslations['js_youtube_deleted']);
            } else {
                showStatusUpdate(jsTranslations['js_youtube_not_deleted']);
            }
        }
    });
    return true;
}

/**
 * set datepicker region values
 */
function setDatepickerRegionValues() {
    $.datepicker.regional['nl'] = {
        closeText: 'Sluiten',
        prevText: '←',
        nextText: '→',
        currentText: 'Vandaag',
        monthNames: ['januari', 'februari', 'maart', 'april', 'mei', 'juni',
            'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
        monthNamesShort: ['jan', 'feb', 'maa', 'apr', 'mei', 'jun',
            'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
        dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
        dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
        dayNamesMin: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
        weekHeader: 'Wk',
        dateFormat: 'dd-mm-yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.regional['en'] = {
        closeText: 'Close',
        prevText: '←',
        nextText: '→',
        currentText: 'Today',
        monthNames: ['january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'],
        monthNamesShort: ['jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec'],
        dayNames: ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        dayNamesShort: ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
        dayNamesMin: ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'],
        weekHeader: 'Wk',
        dateFormat: 'mm-dd-yy',
        firstDay: 0,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''        
    };    
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '←',
        nextText: '→',
        currentText: 'Hoy',
        monthNames: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        monthNamesShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun',
            'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
        dayNames: ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'],
        dayNamesShort: ['dom', 'lun', 'mar', 'mie', 'jue', 'vie', 'sab'],
        dayNamesMin: ['do', 'lu', 'ma', 'mi', 'ju', 'vi', 'sa'],
        weekHeader: 'Sm',
        dateFormat: 'dd-mm-yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''        
    };
}
/**
 * set timepicker region values
 */
function setTimepickerRegionValues() {
    $.timepicker.regional['nl'] = {
        currentText: 'Huidige tijd',
        closeText: 'Klaar',
        ampm: false,
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        timeFormat: 'hh:mm',
        timeSuffix: '',
        timeOnlyTitle: 'Kies tijd',
        timeText: 'Tijd',
        hourText: 'Uren',
        minuteText: 'Minuten',
        secondText: 'Seconden',
        millisecText: 'Milliseconden',
        timezoneText: 'Tijd zone'
    };
    $.timepicker.regional['en'] = {
        currentText: 'Current time',
        closeText: 'Done',
        ampm: true,
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        timeFormat: 'hh:mm',
        timeSuffix: '',
        timeOnlyTitle: 'Select time',
        timeText: 'Time',
        hourText: 'Hours',
        minuteText: 'Minutes',
        secondText: 'Seconds',
        millisecText: 'Milliseconds',
        timezoneText: 'Time zone'
    };
    $.timepicker.regional['es'] = {
        currentText: 'Hora actual',
        closeText: 'Hecho',
        ampm: false,
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        timeFormat: 'hh:mm',
        timeSuffix: '',
        timeOnlyTitle: 'Elija hora',
        timeText: 'Hora',
        hourText: 'Horas',
        minuteText: 'Minutos',
        secondText: 'Secondes',
        millisecText: 'Milisegundos',
        timezoneText: 'Zona horario'
    };    
}

/**
 * attach a random pass button to a input
 * generate pass on click and put in input
 */
(function($) {
    $.fn.randomPass = function(options) {

        var defaults = {
            length: 10,
            charsLower: "abcdefghijkmnpqrstuvwxyz",
            charsUpper: "ABCDEFGHIJKLMNPQRSTUVWXYZ",
            numbers: "23456789",
            specialChars: "!@#$%^&*()?",
            btnClass: "action_icon present_icon",
            minLower: 1, // minimum number of lower case chars
            minUpper: 1, // minimum number of upper case chars
            minNumber: 1, // minimum number of number chars
            minSpecial: 1 // minimum number of spacial chars
        };

        var o = $.extend(defaults, options);


        /**
         * generate a random password
         * @param length integer
         * @param charsLower string
         * @param charsUpper string
         * @param numbers string
         * @param specialChars string
         * @param minLower integer
         * @param minUpper integer
         * @param minNumber integer
         * @param minSpecial integer
         */
        function randomPassword(length, charsLower, charsUpper, numbers, specialChars, minLower, minUpper, minNumber, minSpecial) {

            var charLowerArr = charsLower.split("");
            var charUpperArr = charsUpper.split("");
            var numberArr = numbers.split("");
            var specialArr = specialChars.split("");

            //all characters together
            var totalArr = charUpperArr.concat(numberArr, specialArr, charLowerArr);

            var passChars = ""; //all chars for generating a password
            //for minimum number of lower case chars
            for (var i = 0; i < minLower; i++) {
                var index = Math.floor(Math.random() * charLowerArr.length);
                passChars += charLowerArr[index];
            }

            //for minimum number of upper case chars
            for (var i = 0; i < minUpper; i++) {
                var index = Math.floor(Math.random() * charUpperArr.length);
                passChars += charUpperArr[index];
            }

            //for minimum number of number chars
            for (var i = 0; i < minNumber; i++) {
                var index = Math.floor(Math.random() * numberArr.length);
                passChars += numberArr[index];
            }

            //for minimum number of upper case chars
            for (var i = 0; i < minSpecial; i++) {
                var index = Math.floor(Math.random() * specialArr.length);
                passChars += specialArr[index];
            }

            //add chars to pass chars string until enough chars
            for (var i = passChars.length; i < length; i++) {
                var index = Math.floor(Math.random() * totalArr.length);
                passChars += totalArr[index];
            }

            //convert passChars string to an array
            var passCharsArr = passChars.split("");

            var pass = ''; //real password for input field
            //shuffle password chars for real random password
            for (var i = 0; i < length; i++) {
                var index = Math.floor(Math.random() * passCharsArr.length);
                pass += passCharsArr[index];
                passCharsArr.splice(index, 1); //remove used char from array
            }

            return pass;
        }

        return this.each(function() {

            var now = new Date();

            var input = $(this);
            input.wrap('<span style="position: relative; display: inline-block;" />');

            var randomBtn = '<a id="random_' + now.getTime() + '" title="'+jsTranslations['js_gen_password']+'" style="position: absolute; right: 2px; top: 3px;" class="' + o.btnClass + '" href="#"></a>';

            input.after(randomBtn);

            var inputPaddingRight = (input.css("padding-right"));
            var inputWidth = (input.css("width"));

            NewPaddingRight = inputPaddingRight.match(/[0-9]+/gi);
            NewWidth = inputWidth.match(/[0-9]+/gi);

            input.css("padding-right", (parseInt(NewPaddingRight[0]) + 30) + "px");
            input.css("width", (parseInt(NewWidth[0]) - 30) + "px");

            $('#random_' + now.getTime()).click(function(e) {
                var pass = randomPassword(o.length, o.charsLower, o.charsUpper, o.numbers, o.specialChars, o.minLower, o.minUpper, o.minNumber, o.minSpecial);
                input.val(pass);
                e.preventDefault();
            });
        });
    };
})(jQuery);

/**
 * add a hint to an input on blur and remove on focus
 * show the hint attribute in the input and empty on focus
 * reset on blur
 */
(function($) {
    $.fn.withHint = function(options) {

        var defaults = {
            attr: 'hint',
            className: 'withHint'
        };

        /**
         * add hint to input
         * element javascript element
         * o object options from plugin
         */
        function setHint(element, o) {
            var jElement = $(element);

            // input is empty, set hint
            if (jElement.val() == '') {
                jElement.val(jElement.attr(o.attr)).addClass(o.className);
            }

        }

        /**
         * remove hint from input
         * element javascript element
         * o object options from plugin
         */
        function removeHint(element, o) {
            var jElement = $(element);

            // input equals hint, remove hint
            if (jElement.val() == jElement.attr(o.attr)) {
                jElement.val('').removeClass(o.className);
            }
        }

        var o = $.extend(defaults, options);

        return this.each(function() {
            // on load set hint
            setHint(this, o);

            // on focus remove hint
            $(this).focus(function(e) {
                removeHint(this, o);
            });

            // on blur set hint
            $(this).blur(function(e) {
                setHint(this, o);
            });
        });
    };
})(jQuery);

/**
 * count words or chars and print amount in specified field
 * type : what needs to be counted (string words/chars)
 * counterID : field to put amount of chars/words in ('' = let plugin try)
 * min : min amount of chars/words wanted (int)
 * max : max chars/words wanted (int)
 * limit: limit field to max words/chars (true,false)
 * showLeft: show how many chars/words are left (true,false)
 */
(function($) {
    $.fn.charWordCounter = function(options) {

        var defaults = {
            type: 'chars', // chars or words
            counterID: '', // will try ID from element added with `Counter`
            min: 0,
            max: 0, // unlimited, just count
            limit: false, // limit number of chars/words entered in the field
            format: '{0}', // format for text in counter element ({0} = already typed, {1} = max, {2} = min, {3} = chars/words left)
            errorClass: 'errorColor', // class when not between min and max
            validClass: 'validColor' // class when not between min and max
        };

        /**
         * char counter/limiter
         * @param element javascript element
         * @param o options object 
         */
        function handleChars(element, o) {
            var value = $(element).val();
            var count = value.length;
            var countOK = true;

            // if a max is set, check max
            if (o.max > 0) {
                if (count > o.max) {
                    countOK = false;
                    // if limit is set, limit value at max
                    if (o.limit) {
                        value = value.substr(0, o.max); // reset value
                        count = value.length; // reset count
                        $(element).val(value);
                    }
                }
            }

            // is minimum is set, check min
            if (o.min > count) {
                countOK = false;
            }

            // show text imn counter
            showCounterText(element, o, countOK, count);

        }

        /**
         * word counter/limiter
         * @param element javascript element
         * @param o options object 
         */
        function handleWords(element, o) {
            var value = $(element).val();
            var words = value.split(" ");
            var count = words.length;
            var countOK = true;

            // if no chars, do words -1 (becomes `0` then)
            if (value.length == 0) {
                count -= 1;
            }

            // if string ends with a space, count one word less
            if (value.substring(value.length - 1, value.length) == " ") {
                count -= 1;
            }

            // if a max is set, check max
            if (o.max > 0) {
                if (count > o.max) {
                    countOK = false;
                    // if limit is set, limit value at max
                    if (o.limit) {
                        words = words.splice(0, o.max, 999999); // reset value
                        count = words.length; // reset count
                        $(element).val(words.join(' ') + ' ');
                    }
                }
            }

            // is minimum is set, check min
            if (o.min > count) {
                countOK = false;
            }

            // show text imn counter
            showCounterText(element, o, countOK, count);
        }
        /**
         * @param element javascript element
         * @param o options object 
         * @param countOK boolean is count ok? 
         * @param count int number of words/chars 
         */
        function showCounterText(element, o, countOK, count) {
            var counterEl = null;
            // if counterID is empty, try name attr from element and add Counter
            if (o.counterID == '') {
                var elementID = $(element).attr('id');
                counterEl = $('#' + elementID + 'Counter');
            } else {
                counterEl = $('#' + o.counterID);
            }

            // if counter is not OK (under min or above max) add and remove classes
            if (!countOK) {
                counterEl.addClass(o.errorClass);
                counterEl.removeClass(o.validClass);
            } else {
                counterEl.removeClass(o.errorClass);
                counterEl.addClass(o.validClass);
            }

            // show status in counter Element
            counterEl.html(o.format.format(count, o.max, o.min, (o.max - count)));
        }

        var o = $.extend(defaults, options);

        return this.each(function() {

            // do on call once
            if (o.type == 'chars') {
                handleChars(this, o);
            } else if (o.type == 'words') {
                handleWords(this, o);
            }

            // do on keyup
            $(this).keyup(function(e) {
                if (o.type == 'chars') {
                    handleChars(this, o);
                } else if (o.type == 'words') {
                    handleWords(this, o);
                }
            });
        });
    };
})(jQuery);

/**
 * function for easy formatting string in javascript replaces {\d} with arguments
 * takes a few arguments and for each match it checks if there is a replacement value
 * if there is, it replaces the match otherwise the match stays itself
 */
String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
        return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
    });
};

/**
 * add classes odd and even to child elements within a container element
 * @param string elementID ID of the container with children
 */
function makeZebra(elementID) {
    $(elementID + ' > *').each(function(index, element) {
        // even?
        if (index % 2 == 0) {
            $(element).removeClass('odd').addClass('even');
        } else {
            $(element).removeClass('even').addClass('odd');
        }
    });
}