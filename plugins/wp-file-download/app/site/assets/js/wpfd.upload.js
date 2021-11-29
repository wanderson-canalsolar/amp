/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */


jQuery(document).ready(function ($) {
    if (typeof (Wpfd) === 'undefined') {
        Wpfd = {};
    }

    _wpfd_text = function (text) {
        if (typeof (l10n) !== 'undefined') {
            return l10n[text];
        }
        return text;
    };

    //initUploadBtn();

    function toMB(mb) {
        return mb * 1024 * 1024;
    }

    var allowedExt = wpfd_admin.allowed;
    allowedExt = allowedExt.split(',');
    allowedExt.sort();

    var initUploader = function (currentContainer) {
        // Init the uploader
        var uploader = new Resumable({
            target: wpfd_var.wpfdajaxurl + '?action=wpfd&task=files.upload',
            query: {
                id_category: $(currentContainer).find('input[name=id_category]').val()
            },
            fileParameterName: 'file_upload',
            simultaneousUploads: 2,
            maxFileSize: toMB(wpfd_admin.maxFileSize),
            maxFileSizeErrorCallback: function (file) {
                bootbox.alert(file.name + ' ' + _wpfd_text('is too large') + '!');
            },
            chunkSize: wpfd_admin.serverUploadLimit - 50 * 1024, // Reduce 50KB to avoid error
            forceChunkSize: true,
            fileType: allowedExt,
            fileTypeErrorCallback: function (file) {
                bootbox.alert(file.name + ' cannot upload!<br/><br/>' + _wpfd_text('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration'));
            },
            generateUniqueIdentifier: function (file, event) {
                var relativePath = file.webkitRelativePath || file.fileName || file.name;
                var size = file.size;
                var prefix = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                return (prefix + size + '-' + relativePath.replace(/[^0-9a-zA-Z_-]/img, ''));
            }
        });

        if (!uploader.support) {
            bootbox.alert(_wpfd_text('Your browser does not support HTML5 file uploads!'));
        }

        if (typeof (willUpload) === 'undefined') {
            var willUpload = true;
        }

        uploader.on('filesAdded', function (files) {
            if (!wpfd_permissions.can_edit_category) {
                bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
                return false;
            }

            files.forEach(function (file) {
                var progressBlock = '<div class="wpfd_process_block" id="' + file.uniqueIdentifier + '">'
                    + '<div class="wpfd_process_fileinfo">'
                    + '<span class="wpfd_process_filename">' + file.fileName + '</span>'
                    + '<span class="wpfd_process_cancel">Cancel</span>'
                    + '</div>'
                    + '<div class="wpfd_process_full" style="display: block;">'
                    + '<div class="wpfd_process_run" data-w="0" style="width: 0%;"></div>'
                    + '</div></div>';

                //$('#preview', '.wpreview').before(progressBlock);
                currentContainer.find('#preview', '.wpreview').before(progressBlock);
                $(currentContainer).find('.wpfd_process_cancel').unbind('click').click(function () {
                    fileID = $(this).parents('.wpfd_process_block').attr('id');
                    fileObj = uploader.getFromUniqueIdentifier(fileID);
                    uploader.removeFile(fileObj);
                    $(this).parents('.wpfd_process_block').fadeOut('normal', function () {
                        $(this).remove();
                    });

                    if (uploader.files.length === 0) {
                        $(currentContainer).find('.wpfd_process_pause').fadeOut('normal', function () {
                            $(this).remove();
                        });
                    }

                    $.ajax({
                        url: wpfd_var.wpfdajaxurl + '?action=wpfd&task=files.upload',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            id_category: $('input[name=id_category]').val(),
                            deleteChunks: fileID
                        },
                        success: function (res, stt) {
                            if (res.response === true) {

                            }
                        }
                    })
                });
            });

            // Do not run uploader if no files added or upload same files again
            if (files.length > 0) {
                uploadPauseBtn = $(currentContainer).find('.wpreview').find('.wpfd_process_pause').length;
                restableBlock = $(currentContainer).find('.wpfd_process_block');

                if (!uploadPauseBtn) {
                    restableBlock.before('<div class="wpfd_process_pause">Pause</div>');
                    $(currentContainer).find('.wpfd_process_pause').unbind('click').click(function () {
                        if (uploader.isUploading()) {
                            uploader.pause();
                            $(this).text('Start');
                            $(this).addClass('paused');
                            willUpload = false;
                        } else {
                            uploader.upload();
                            $(this).text('Pause');
                            $(this).removeClass('paused');
                            willUpload = true;
                        }
                    });
                }

                uploader.opts.query = {
                    id_category: currentContainer.find('input[name=id_category]').val()
                };

                if (willUpload) uploader.upload();
            }
        });

        uploader.on('fileProgress', function (file) {
            $(currentContainer).find('.wpfd_process_block#' + file.uniqueIdentifier)
                .find('.wpfd_process_run').width(Math.floor(file.progress() * 100) + '%');
        });

        uploader.on('fileSuccess', function (file, res) {
            var thisUploadBlock = currentContainer.find('.wpfd_process_block#' + file.uniqueIdentifier);
            thisUploadBlock.find('.wpfd_process_cancel').addClass('uploadDone').text('OK').unbind('click');
            thisUploadBlock.find('.wpfd_process_full').remove();

            var response = JSON.parse(res);
            if (response.response === false && typeof(response.datas) !== 'undefined') {
                if (typeof(response.datas.code) !== 'undefined' && response.datas.code > 20) {
                    bootbox.alert('<div>' + response.datas.message + '</div>');
                    return false;
                }
            }
            if (typeof(response) === 'string') {
                bootbox.alert('<div>' + response + '</div>');
                return false;
            }

            if (response.response !== true) {
                bootbox.alert(response.response);
                return false;
            }
        });

        uploader.on('fileError', function (file, msg) {
            thisUploadBlock = currentContainer.find('.wpfd_process_block#' + file.uniqueIdentifier);
            thisUploadBlock.find('.wpfd_process_cancel').addClass('uploadError').text('Error').unbind('click');
            thisUploadBlock.find('.wpfd_process_full').remove();
        });

        uploader.on('complete', function () {
            currentContainer.find('.progress').delay(300).fadeIn(300).hide(300, function () {
                $(this).remove();
            });
            currentContainer.find('.uploaded').delay(300).fadeIn(300).hide(300, function () {
                $(this).remove();
            });
            $('#wpreview .file').delay(1200).show(1200, function () {
                $(this).removeClass('done placeholder');
            });

            $('.gritter-item-wrapper ').remove();
            var message = $('<div class="upload_message" style="width: 100%; text-align: center;">File(s) uploaded with' + ' success!</div>');
            currentContainer.before(message);
            message.delay(1200).fadeIn(1200).hide(300, function () {
                message.remove();
                $(currentContainer).find('.wpfd_process_pause').remove();
                $(currentContainer).find('.wpfd_process_block').remove();
            });
        });

        uploader.assignBrowse($(currentContainer).find('#upload_button'));
        uploader.assignDrop($(currentContainer).find('.jsWpfdFrontUpload'));
    }

    var containers = $('div[class*=wpfdUploadForm]');
    if (containers.length > 0) {
        containers.each(function(i, el) {
            initUploader($(el));
        });
    }
});
