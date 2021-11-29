/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */
// https://gist.github.com/rcmachado/242617
var unserialize = function (serializedString) {
    var str = decodeURI(serializedString);
    var pairs = str.split('&');
    var obj = {}, p, idx, val;
    for (var i=0, n=pairs.length; i < n; i++) {
        p = pairs[i].split('=');
        idx = p[0];

        if (idx.indexOf("[]") == (idx.length - 2)) {
            // Eh um vetor
            var ind = idx.substring(0, idx.length-2)
            if (obj[ind] === undefined) {
                obj[ind] = [];
            }
            obj[ind].push(p[1]);
        }
        else {
            obj[idx] = p[1];
        }
    }
    return obj;
};

jQuery(document).ready(function ($) {
    if (typeof(Wpfd) === 'undefined') {
        Wpfd = {};
        Wpfd.filetocat = false;
        Wpfd.catRefTofileId = false;
        Wpfd.log = function($logMessage, $type) {
            if (typeof wpfd_debug !== 'undefined') {
                if (wpfd_debug.debug) {
                    if ($type === undefined || $type === 'log') {
                        console.log('WPFD DEBUG: ' + $logMessage);
                    }
                    if ($type === 'warn') {
                        console.warn('WPFD WARNING: ' + $logMessage);
                    }
                    if ($type === 'error') {
                        console.error('WPFD ERROR: ' + $logMessage);
                    }
                }
            }
        };
        Wpfd.time = function($label) {
            if (wpfd_debug.debug) {
                console.time($label);
            }
        };
        Wpfd.timeEnd = function ($label) {
            if (wpfd_debug.debug) {
                console.timeEnd($label);
            }
        }
    }
    if (typeof wpfd_debug !== 'undefined' && wpfd_debug.debug && wpfd_debug.ajax) {
        $(document).ajaxSend(function (e, jqhxr, settings) {
            if (settings.url.includes('action=wpfd')) {
                Wpfd.time('WPFD AJAX: ' + settings.url);
            }
        });
        $(document).ajaxComplete(function (e, jqhxr, settings) {
            if (settings.url.includes('action=wpfd')) {
                Wpfd.timeEnd('WPFD AJAX: ' + settings.url);
            }
        });
    }
    var categoryAjax = null;
    var fileAjax = null;
    var versionAjax = null;
    _wpfd_text = function (text) {
        if (typeof(l10n) !== 'undefined') {
            return l10n[text];
        }
        return text;
    };
    var updateCatCount = function(catid, value) {
        var span = $('li[data-id-category="'+catid+'"] .dd-content .countfile').first();
        if (typeof span.html() !== 'undefined') {
            var currentValue = span.html().replace(/[^0-9.]/g, "");
            span.html('('+(Number(currentValue) + Number(value))+')');
        }
    };
    var leftwidth = parseInt($("#wpfd-categories-col").width());
    $("#wpfd-categories-col").resizable({handles: "e"}).resize(function () {
        var width = parseInt(this.style.width);
        return this.style['-webkit-flex-basis'] = (width - leftwidth) + 'px';
    });
    Wpfd.getCategoriesState = function() {
        var categoriesState = localStorage.getItem('wpfdCategoriesState');
        if (categoriesState) {
            return JSON.parse(categoriesState);

        } else {
            // Get current state then save
            var openCategories = $('li.dd-collapsed');
            var currentState = [];
            $.each(openCategories, function (index, li) {
                currentState.push($(li).attr('data-id-category'));
            });
            localStorage.setItem('wpfdCategoriesState', JSON.stringify(currentState));
            return currentState;
        }
    };
    Wpfd.saveCategoriesState = function() {
        var openCategories = $('li.dd-collapsed');
            var currentState = [];
            $.each(openCategories, function (index, li) {
                currentState.push($(li).attr('data-id-category'));
            });
            localStorage.setItem('wpfdCategoriesState', JSON.stringify(currentState));
    };
    Wpfd.restoreCategoriesState = function() {
        var openCategoriesId = Wpfd.getCategoriesState();
        if (openCategoriesId.length) {
            $.each(openCategoriesId, function (index, catId) {
                $('#categorieslist li[data-id-category="'+catId+'"]').addClass('dd-collapsed');
            });
        }
    };
    /* Title edition */
    Wpfd.initMenu = function() {
        /**
         * Click on delete category btn
         */
        $('#categorieslist .dd-content .trash').unbind('click').on('click', function () {
            var id_category = $(this).closest('li').data('id-category');
            var hasElement = $(this).parent().parent().find("div.dd3-handle i.google-drive-icon");
            var hasDropElement = $(this).parent().parent().find("div.dd3-handle i.dropbox-icon");
            var hasOneDriveElement = $(this).parent().parent().find("div.dd3-handle i.onedrive-icon");
            var hasOneDriveBusinessElement = $(this).parent().parent().find("div.dd3-handle i.onedrive-business-icon");
            var typeCloud = "null";
            if (hasElement.length > 0) {
                typeCloud = "googledrive";
            }
            if (hasDropElement.length > 0) {
                typeCloud = "dropbox";
            }
            if (hasOneDriveElement.length > 0) {
                typeCloud = "onedrive";
            }
            if (hasOneDriveBusinessElement.length > 0) {
                typeCloud = "onedrive_business";
            }
            bootbox.dialog(
                _wpfd_text('Do you want to delete') + ' "' + $(this).parent().find('.title').text() + '"?',
                [
                    {
                        "label": _wpfd_text('Cancel')
                    },
                    {
                        "label": _wpfd_text('Confirm'),
                        "callback": function() {
                            var title = $('li[data-id-category=' + id_category + '] span.title');
                            var deleteCatTitle = title.html();

                            var wpfdAjaxurl = wpfdajaxurl + "task=category.delete&id_category=" + id_category;
                            if (typeCloud === 'googledrive') {
                                wpfdAjaxurl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonDeleteCategory&id_category=" + id_category
                            } else if (typeCloud === 'dropbox') {
                                wpfdAjaxurl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonDeleteDropboxCategory&id_category=" + id_category
                            } else if (typeCloud === 'onedrive') {
                                wpfdAjaxurl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonDeleteOneDriveCategory&id_category=" + id_category
                            } else if (typeCloud === 'onedrive_business') {
                                wpfdAjaxurl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonDeleteOneDriveBusinessCategory&id_category=" + id_category
                            }
                            $.ajax({
                                url: wpfdAjaxurl,
                                type: 'POST',
                                data: {security: wpfd_var.wpfdsecurity},
                                beforeSend: function () {
                                    title.html(_wpfd_text('Deleting...'));
                                }
                            }).done(function (data) {
                                result = jQuery.parseJSON(data);
                                if (result.response === true) {
                                    $('.nested').nestable('remove', id_category, function () {
                                    });
                                    $('#preview').contents().remove();
                                    first = $('#wpfd-categories-col #categorieslist li .dd-content').first();
                                    if (first.length > 0) {
                                        first.click();
                                    } else {
                                        $('#insertcategory').hide();
                                    }
                                    $('.gritter-item-wrapper ').remove();
                                    $.gritter.add({text: wpfd_admin.msg_remove_category});
                                } else {
                                    title.html(deleteCatTitle);
                                    bootbox.alert(result.response);
                                }
                            }).error(function (a, b, c) {
                                bootbox.alert('Error: ' + c + '!! Please check your connection then try again.');
                                title.html(deleteCatTitle);
                            });
                        }
                    }
                ]
            );

            return false;
        });

        /* Set the active category on menu click */
        $('#categorieslist .dd-content').unbind('click').on('click', function (e) {
            id_category = $(this).parent().data('id-category');
            $('input[name=id_category]').val(id_category);
            if (Wpfd.catRefTofileId) {
                updatepreview(id_category, Wpfd.catRefTofileId);
                Wpfd.catRefTofileId = false;
            } else {
                updatepreview(id_category);
                Wpfd.catRefTofileId = false;
            }
            $('#categorieslist li').removeClass('active');
            $(this).parent().addClass('active');
            var event = $.Event('wpfd_category_click');
            $(this).trigger(event);

            return false;
        });
        $('#categorieslist .dd-content a.edit').unbind().click(function (e) {

            if (!wpfd_permissions.can_edit_category) {
                bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
                return false;
            }

            e.stopPropagation();
            $this = this;
            link = $(this).parent().find('a span.title');
            oldTitle = link.text();
            $(link).attr('contentEditable', true);
            $(link).addClass('editable');
            $(link).selectText();

            $('#categorieslist a span.editable').bind('click.mm', hstop);  //let's click on the editable object
            $(link).bind('keypress.mm', hpress); //let's press enter to validate new title'
            $('*').not($(link)).bind('click.mm', houtside);

            function unbindall() {
                $('#categorieslist a span').unbind('click.mm', hstop);  //let's click on the editable object
                $(link).unbind('keypress.mm', hpress); //let's press enter to validate new title'
                $('*').not($(link)).unbind('click.mm', houtside);
            }

            //Validation
            function hstop(event) {
                event.stopPropagation();
                return false;
            }

            //Press enter
            function hpress(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    unbindall();
                    updateTitle($(link).text());
                    $(link).removeAttr('contentEditable');
                    $(link).removeClass('editable');
                }
            }

            //click outside
            function houtside(e) {
                unbindall();
                updateTitle($(link).text());
                $(link).removeAttr('contentEditable');
                $(link).removeClass('editable');
            }


            function updateTitle(title) {
                id_category = $(link).parents('li').data('id-category');
                if (title !== '') {
                    $.ajax({
                        url: wpfdajaxurl + "task=category.setTitle",
                        data: {id_category: id_category, title: title},
                        type: "POST"
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result.response === true) {
                            $('.gritter-item-wrapper ').remove();
                            $.gritter.add({text: wpfd_admin.msg_edit_category});
                            return true;
                        }
                        $(link).text(oldTitle);
                        return false;
                    });
                } else {
                    $(link).text(oldTitle);
                    return false;
                }

            }
        });
        // Restore categories state
        Wpfd.restoreCategoriesState();
        // Init categories state handler
        $('#categorieslist').on('click', 'button', function(e) {
            setTimeout(function() {Wpfd.saveCategoriesState();}, 100);
        });

    };
    // save temp
    Wpfd.saveTemp = function() {
        id_category = $('input[name=id_category]').val();
        $.ajax({
            url: wpfdajaxurl + "task=category.saveparams&id=" + id_category,
            type: "POST",
            data: $('#category_params').serialize()
        }).done(function (data) {
        });
    };
    var selectedFiles = [];
    // file action
    Wpfd.submitbutton = function ($task) {
        if ($task === 'files.copyfile' || $task === 'files.movefile') {
            if ($('#preview .file.selected').length === 0) {
                bootbox.alert(_wpfd_text('Please select file(s)'));
                return;
            }
            lastAction = $task;
            copySourceCat = $('#categorieslist li.active').data('id-category');
            selectedFiles = [];
            $('#preview .file.selected').each(function (index) {
                selectedFiles.push($(this).data('id-file'));
            });
            if (lastAction === 'files.copyfile') {
                //do nothing
            } else {
                $('#preview .file').removeAttr('style');
                $('#preview .file.selected').addClass('cuted');
                if ($('#preview .file.selected').prop('tagName').toLowerCase() === 'tr') {
                    $('#preview .file.selected').css('opacity', '.7');
                } else {
                    $('#preview .file .overlay').remove();
                    $('#preview .file.selected').append('<div class="overlay"></div>');
                }

            }

            var numberfiles = '<span class="wpfd-number-files">' + $('#preview .file.selected').length + '</span>';
            var type = 'cut';
            if ($task === 'files.copyfile') {
                type = 'copy';
            } else if ($task === 'files.movefile') {
                type = 'cut';
            }
            $('.wpfd-number-files').remove();

            $('#wpfd-' + type).prepend(numberfiles);

        }
        else if ($task === 'files.paste') {
            if (selectedFiles.length === 0) {
                bootbox.alert(_wpfd_text('There is no copied/cut files yet'));
            }
            cat_target = $('#categorieslist li.active').data('id-category');
            if (cat_target !== copySourceCat) {
                countFiles = selectedFiles.length;
                iFile = 0;
                while (selectedFiles.length > 0) {
                    id_file = selectedFiles.pop();
                    $.ajax({
                        url: wpfdajaxurl + "task=" + lastAction + "&id_category=" + cat_target + '&active_category=' + copySourceCat + '&id_file=' + id_file,
                        type: "POST",
                        data: {}
                    }).done(function (data) {
                        iFile++;
                        if (iFile === countFiles) {
                            if (lastAction === 'files.copyfile') {
                                $('.gritter-item-wrapper ').remove();
                                $.gritter.add({text: wpfd_admin.msg_copy_files});
                            } else {
                                updateCatCount(copySourceCat, 0 - iFile);
                                $('.gritter-item-wrapper ').remove();
                                $.gritter.add({text: wpfd_admin.msg_move_files});
                            }
                            updateCatCount(cat_target, iFile);
                            updatepreview(cat_target);
                        }
                    });
                }


            }
            $('.wpfd-number-files').remove();
        }
        else if ($task === 'files.selectall') {
            $('.file').addClass('selected');
            $('.wpfd-btn-toolbar').find('#wpfd-cut, #wpfd-copy, #wpfd-paste, #wpfd-delete, #wpfd-download, #wpfd-uncheck').show();
        }
        else if ($task === 'files.uncheck') {
            $('.file').removeClass('selected');
            $('.wpfd-btn-toolbar').find('#wpfd-cut, #wpfd-copy, #wpfd-paste, #wpfd-delete, #wpfd-download, #wpfd-uncheck').hide();
            showCategory();
        }
        else if ($task === 'files.delete') {
            bootbox.dialog(
                wpfd_admin.msg_ask_delete_files,
                [
                    {
                        "label": _wpfd_text('Cancel')
                    },
                    {
                        "label": _wpfd_text('Confirm'),
                        "callback": function () {
                            sourceCat = $('#categorieslist li.active').data('id-category');
                            selectedFilesInfos = [];
                            $('#preview .file.selected').each(function (index) {
                                selectedFilesInfos.push({
                                    'fileId': $(this).data('id-file'),
                                    'catIdRef': $(this).data('catid-file'),
                                    'isWoo': $(this).hasClass('isWoocommerce')
                                });
                            });

                            while (selectedFilesInfos.length > 0) {
                                filesInfos = selectedFilesInfos.pop();
                                id_file = filesInfos.fileId;
                                catIdRef = filesInfos.catIdRef;
                                confirmDeleteWooFiles = false;

                                if (filesInfos.isWoo) {
                                    if (confirmDeleteWooFiles || confirm('This file linked to a product, are you sure delete this?')) {
                                        confirmDeleteWooFiles = true;
                                    } else {
                                        continue;
                                    }
                                }

                                $.ajax({
                                    url: wpfdajaxurl + "task=file.delete&id_file=" + id_file + "&id_category=" + sourceCat + "&catid_file_ref=" + catIdRef,
                                    type: "POST",
                                    data: {},
                                    success: function (data) {
                                        var res = JSON.parse(data);
                                        if (res.response) {
                                            updateCatCount(sourceCat, -1);
                                        }
                                    }
                                });

                                $('.file[data-id-file="' + id_file + '"]').fadeOut(500, function () {
                                    $(this).remove();
                                });
                            }
                            $.gritter.add({text: wpfd_admin.msg_remove_files});
                        }
                    }
                    ]);
            return false;
        } else if ($task === 'files.download') {
            $('#preview .file.selected').each(function (index) {
                var link = document.createElement("a");
                link.download = '';
                link.href = $(this).data('linkdownload');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                $(link).remove();
            });
        }
    };
    // set user value on user modal
    $('.button-select-user').on('click', function () {
        user_name = [];
        user_id = [];
        var type = $('.fieldtype').val();
        var cataction = $('.cataction').val();
        user_id_str = window.parent.jQuery('.' + type).val();
        if (user_id_str) {
            user_id = user_id_str.split(",");
            user_name = window.parent.jQuery('.' + type + '-name').val().split(",");
        }

        var $this = $(this);
        var username = $this.data('name').toString();
        var uservalue = $this.data('user-value').toString();
        if (user_id.indexOf(uservalue) === -1) {
            user_id.push(uservalue);
            user_name.push(username);
        }
        if (!cataction) {
            window.parent.jQuery('.' + type + '-name' + '.file').val(user_name.toString());
            window.parent.jQuery('.' + type + '.file').val(user_id.toString());
        } else {
            window.parent.jQuery('.' + type + '-name').val(username);
            window.parent.jQuery('.' + type).val(uservalue);
        }
        window.parent.tb_remove();
    });

    $('.btn-insert-user').on('click', function () {
        user_name = [];
        user_id = [];
        check_selected = 0;
        $('input:checkbox[name=cb-selected]:checked').each(function () {
            var $this = $(this);
            user_id.push($this.val());
            user_name.push($('a[data-user-value="' + $this.val() + '"]').data('name'));
            check_selected = check_selected + 1;
        });
        if (!check_selected) {
            return;
        }
        var type = $('.fieldtype').val();
        url = 'admin.php?page=wpfd&task=user.display&noheader=true&fieldtype=field-user-input&listCanview=' + user_id.toString() + '&TB_iframe=true&height=400&width=800';
        window.parent.jQuery('.' + type + '-name' + '.file').val(user_name.toString());
        window.parent.jQuery('.field-user-wrapper .button-select.file').attr("href", url);
        window.parent.jQuery('.' + type + '.file').val(user_id.toString());
        window.parent.tb_remove();
    });

    var scrollerTimer;
    /**
     * Init sortable files
     * Save order after each sort
     */
    $('#preview').sortable({
        placeholder: 'highlight file',
        revert: true,
        distance: 5,
        items: ".file",
        tolerance: "pointer",
        appendTo: "body",
        cursorAt: {top: 0, left: 0},
        helper: function (e, item) {
            var fileext = $(item).find('.ext').text()
            var filename = $(item).find('.title').text() + "." + fileext;
            var count = $('#preview').find('.file.selected').length;
            if (count > 1) {
                return $("<span id='file-handle' class='wpfd_draged_file ui-widget-header' ><div class='ext "+fileext+"'><span class='txt'>"+fileext+"</span></div><div class='filename'>" + filename + "...</div><span class='fCount'>" + count + "</span></div>");
            } else {
                return $("<div id='file-handle' class='wpfd_draged_file ui-widget-header' ><div class='ext "+fileext+"'><span class='txt'>"+fileext+"</span></div><div class='filename'>" + filename + "</div></div>");
            }
        },
        sort: function (e, item) {
            if (scrollerTimer) {
                clearInterval(scrollerTimer);
            }

            if (typeof jQuery.fn.mCustomScrollbar == 'undefined') {
                return;
            }

            var wrapper = $('#wpfd-core #pwrapper .wpfd_center');
            var wrapper_height = wrapper.height();
            var sign = '+';
            var triggerScroll = false;
            var itemPositionWithWrapper = item.position.top - wrapper.offset().top;

            if (itemPositionWithWrapper > 0 && itemPositionWithWrapper < 50) {
                sign = '+';
                triggerScroll = true;
            }

            if (itemPositionWithWrapper > 0 && itemPositionWithWrapper < wrapper_height && itemPositionWithWrapper > wrapper_height - 100) {
                sign = '-';
                triggerScroll = true;
            }

            scrollerTimer = setInterval(function() {
                if (triggerScroll) {
                    wrapper.mCustomScrollbar('scrollTo', [sign + '=200', 0], {scrollInertia: 300,scrollEasing: "linear"});
                }
            }, 50);
        },
        update: function () {
            if (scrollerTimer) {
                clearInterval(scrollerTimer);
            }
            var json = '';
            $.each($('#preview .file'), function (i, val) {
                if (json !== '') {
                    json += ',';
                }
                json += '"' + i + '":' + $(val).data('id-file');
            });
            json = '{' + json + '}';
            $.ajax({
                url: wpfdajaxurl + "task=files.reorder&order=" + json,
                type: "POST",
                data: {}
            }).done(function (data) {
                $('.gritter-item-wrapper ').remove();
                if (!Wpfd.filetocat) {
                    $.gritter.add({text: wpfd_admin.msg_ordering_file2});
                }
                if ($('#ordering').val() !== 'ordering') {
                    $('#ordering option[value="ordering"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                    $('#orderingdir option[value="asc"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                    id_category = $('input[name=id_category]').val();
                    $.ajax({
                        url: wpfdajaxurl + "task=category.saveparams&id=" + id_category,
                        type: "POST",
                        data: $('#category_params').serialize()
                    }).done(function (data) {
                    });
                }
            });
        },
        /** Prevent firefox bug positionnement **/
        start: function (event, ui) {
            $(ui.helper).css('width', 'auto');

            var userAgent = navigator.userAgent.toLowerCase();
            if (ui.helper !== "undefined" && userAgent.match(/firefox/)) {
                ui.helper.css('position', 'absolute');
            }
            ui.placeholder.html("<td colspan='8'></td>");

        },
        stop: function (event, ui) {

            $('#file-handle').removeClass('wpfdzoomin');

        },
        beforeStop: function (event, ui) {
            if (scrollerTimer) {
                clearInterval(scrollerTimer);
            }
            var userAgent = navigator.userAgent.toLowerCase();
            if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                ui.helper.css('margin-top', 0);
            }
        },
        beforeRevert: function (e, ui) {

            if ($('#categorieslist .wpfddropzoom').length > 0) {
                return false; // copy/move file
            }
            $('#file-handle').addClass('wpfdzoomin');
            $('#file-handle').fadeOut();
            return true;
        }
    });
    $('#preview').disableSelection();

    /*Color field*/
    initColor();
    /* Load category */
    updatepreview();

    /* Load nestable */
    $('.nested').nestable({
        maxDepth: 16,
        effect: {
            animation: 'fade-up',
            time: 'slow'
        },
      onClick: function(l, e, p) {
            id_category = $(e).data('id-category');
            $('input[name=id_category]').val(id_category);
            if (Wpfd.catRefTofileId) {
              updatepreview(id_category, Wpfd.catRefTofileId);
              Wpfd.catRefTofileId = false;
            } else {
              updatepreview(id_category);
              Wpfd.catRefTofileId = false;
            }
            $('#categorieslist li').removeClass('active');
            $(e).addClass('active');
            var event = $.Event('wpfd_category_click');
            $(e).find('.dd-content', 0).trigger(event);
            return false;
      },
        callback: function (event, e) {
            var isCloudItem = $(e).find('div.dd3-handle i.google-drive-icon').length;
            var isDropboxItem = $(e).find('div.dd3-handle i.dropbox-icon').length;
            var isOneDriveItem = $(e).find('div.dd3-handle i.onedrive-icon').length;
            var isOneDriveBusinessItem = $(e).find('div.dd3-handle i.onedrive-business-icon').length;
            var itemChangeType = 'default';
            if (isCloudItem > 0) {
                itemChangeType = 'googledrive';
            } else if (isDropboxItem > 0) {
                itemChangeType = 'dropbox';
            } else if (isOneDriveItem > 0) {
                itemChangeType = 'onedrive';
            } else if (isOneDriveBusinessItem > 0) {
                itemChangeType = 'onedrive_business';
            }

            pk = $(e).data('id-category');
            if ($(e).prev('li').length === 0) {
                position = 'first-child';
                if ($(e).parents('li').length === 0) {
                    //root
                    ref = 0;
                } else {
                    ref = $(e).parents('li').data('id-category');
                }
            } else {
                position = 'after';
                ref = $(e).prev('li').data('id-category');
            }

            $.ajax({
                url: wpfdajaxurl + "task=category.changeOrder&pk=" + pk + "&position=" + position + "&ref=" + ref + "&dragType=" + itemChangeType + "&security=" + wpfd_var.wpfdsecurity,
                type: "POST",
                data: {},
                dataType: 'json'
            }).done(function (result) {
                //result = jQuery.parseJSON(data);
                if (result.response === true) {
                    $('.gritter-item-wrapper ').remove();
                    $.gritter.add({text: wpfd_admin.msg_move_category});
                } else {
                    bootbox.alert(result.response);
                }
            });
        }
    });

    /* init menu actions */
    Wpfd.initMenu();
    var ctrlDown = false;
    $(window).on("keydown", function (event) {
        if (event.ctrlKey || event.metaKey) {
            ctrlDown = true;
        }
    }).on("keyup", function (event) {
        ctrlDown = false;
    });
    // init categories items
    var runOnce = 0;
    catDroppable = function () {
        $("#categorieslist .dd-handle").droppable({
            accept: '.file',
            revert: 'valid',
            hoverClass: "dd-content-hover",
            tolerance: "pointer",
            //greedy: true,
            over: function (event, ui) {
                $(event.target).closest('li').addClass("wpfddropzoom");
                runOnce = 0;
            },
            out: function (event, ui) {
                $(event.target).closest('li').removeClass("wpfddropzoom");
            },
            drop: function (event, ui) {

                $(this).addClass("ui-state-highlight");
                cat_target = $(event.target).closest('li').data("id-category");
                current_cat = $("#categorieslist .dd-item.active").data('id-category');
                Wpfd.filetocat = true;

                if (current_cat !== cat_target) {
                    count = $('#preview').find('.file.selected').length;
                    if (count > 0) { //multiple file
                        if(runOnce === 0) {
                            iFile = 0;
                            $('#preview').find('.file.selected').each(function () {
                                id_file = $(this).data("id-file");
                                if (ctrlDown) { //copy file
                                    $.ajax({
                                        url: wpfdajaxurl + "task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                        type: "POST",
                                        data: {}
                                    }).done(function (data) {
                                        iFile++;
                                        if (iFile === count) {
                                            updateCatCount(cat_target, iFile);
                                            $('.gritter-item-wrapper ').remove();
                                            $.gritter.add({text: wpfd_admin.msg_copy_file});
                                        }
                                    });
                                } else {
                                    $.ajax({
                                        url: wpfdajaxurl + "task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                        type: "POST",
                                        data: {},
                                        dataType: "json"
                                    }).done(function (result) {
                                        iFile++;
                                        if (typeof result.datas.id_file !== "undefined") {
                                            $('tr[data-id-file="' + result.datas.id_file + '"]').remove();
                                        }
                                        if (iFile === count) {
                                            updateCatCount(current_cat, 0 - iFile);
                                            updateCatCount(cat_target, iFile);
                                            $('.gritter-item-wrapper ').remove();
                                            $.gritter.add({text: wpfd_admin.msg_move_file});
                                        }
                                    });
                                }
                            })
                            runOnce = 1;
                        }
                    }
                    else {  //single file
                        if(runOnce === 0) {
                            id_file = $(ui.draggable).data("id-file");
                            if (ctrlDown) { //copy file
                                $.ajax({
                                    url: wpfdajaxurl + "task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST",
                                    data: {}
                                }).done(function (data) {
                                    updateCatCount(cat_target, 1);
                                    $('.gritter-item-wrapper ').remove();
                                    $.gritter.add({text: wpfd_admin.msg_copy_file});
                                });
                            } else {
                                $.ajax({
                                    url: wpfdajaxurl + "task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST",
                                    data: {}
                                }).done(function (data) {
                                    updateCatCount(current_cat, -1);
                                    updateCatCount(cat_target, 1);
                                    $('.file[data-id-file="' + id_file + '"]').remove();
                                    $('.gritter-item-wrapper ').remove();
                                    $.gritter.add({text: wpfd_admin.msg_move_file});
                                });
                            }
                            runOnce = 1;
                        }
                    }
                }
                $(this).removeClass("ui-state-highlight");
                $(event.target).closest('li').removeClass("wpfddropzoom");
            }
        });
    };
    catDroppable();
    runOnce = 0;

    /* Init version dropbox */
    // initDropboxVersion($('#fileversion'));
    // $('#upload_button_version').on('click', function () {
    //     $('#upload_input_version').trigger('click');
    //     return false;
    // });

    function showCategory() {
        $('.fileblock').fadeOut(function () {
            $('.categoryblock').fadeIn();
        });
        $('#insertfile').fadeOut(function () {
            $('#insertcategory').fadeIn();
        });

        if (typeof gcaninsert !== "undefined" && gcaninsert) {
            $('#insertfiletowoo').fadeOut();
        }
    }

    function showFile(e) {
        $('.categoryblock').fadeOut(function () {
            $('.fileblock').fadeIn();
        });
        $('#insertcategory').fadeOut(function () {
            $('#insertfile').fadeIn();
        });

        if (typeof gcaninsert !== "undefined" && gcaninsert) {
            $('#insertfiletowoo').fadeIn();
        }
    }

    function checkCateActive(id_category) {
        id_category_ck = null;
        var listIdDisable = [];
        $('#categorieslist li').each(function (index) {
            if ($(this).hasClass('disabled')) {
                $(this).removeClass('active');
                listIdDisable.push($(this).data('item-disable'));
            }
            id_category_ck = $('#categorieslist li.active').data('id-category');
            if (id_category) {
                if (jQuery.inArray(id_category, listIdDisable >= 0)) {
                    if (typeof(id_category_ck) === 'undefined') {
                        $('#categorieslist li.not_disable:first').addClass('active');
                        id_category = $('#categorieslist li.active').data('id-category');
                    }
                } else {
                    if (typeof(id_category_ck) === 'undefined') {
                        $('#categorieslist li.not_disable:first').addClass('active');
                        id_category = $('#categorieslist li.active').data('id-category');
                    }
                }
            } else {
                if (typeof(id_category_ck) === 'undefined') {
                    $('#categorieslist li.not_disable:first').addClass('active');
                    id_category = $('#categorieslist li.active').data('id-category');
                }
                else {
                    id_category = id_category_ck;
                }
            }
        });
        $('input[name=id_category]').val(id_category);
        return id_category;
    }
    function isCloudCategory() {
        var currentActive = $('.dd3-item.active');
        if (typeof currentActive !== 'undefined') {
            if ($('.dd3-item.active > .dd3-handle  > i.onedrive-icon').length > 0 ||
                $('.dd3-item.active > .dd3-handle  > i.onedrive-business-icon').length > 0 ||
                $('.dd3-item.active > .dd3-handle  > i.google-drive-icon').length > 0 ||
                $('.dd3-item.active > .dd3-handle  > i.dropbox-icon').length > 0
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reload a category preview
     * @param id_category
     * @param id_file
     * @param $ordering
     * @param $ordering_dir
     * @param $ordering
     * @param $ordering_dir
     */
    function updatepreview(id_category, id_file, $ordering, $ordering_dir) {
        if (typeof(id_category) === "undefined" || id_category === null) {
            id_category = checkCateActive(id_category);
            if (typeof(id_category) === 'undefined') {
                $('#insertcategory').hide();
                return;
            }
            $('input[name=id_category]').val(id_category);
        } else {
            id_category = checkCateActive(id_category);
        }
        if ($("#wpreview").length === 0) return;
        loading('#wpreview');

        var url = wpfdajaxurl + "view=files&format=raw&id_category=" + id_category;
        if ($ordering !== null && $ordering !== undefined) {
            url += '&orderCol=' + $ordering;
        }

        if ($ordering_dir === 'asc') {
            url += '&orderDir=desc';
        } else if ($ordering_dir === 'desc') {
            url += url + '&orderDir=asc';
        }
        var oldCategoryAjax = categoryAjax;
        if (oldCategoryAjax !== null) {
            oldCategoryAjax.abort();
        }
        categoryAjax = $.ajax({
            url: url,
            type: "POST",
            data: {}
        }).done(function (data) {
            $('#wpfd_filter_catid').val(id_category);
            $('#preview').contents().remove();
            $(data).hide().appendTo('#preview').fadeIn(200);

            if ($ordering !== null && $ordering !== undefined) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_ordering_file});
            }

            if (selectedFiles.length === 0) {
                $('.wpfd-btn-toolbar').find('#wpfd-cut, #wpfd-copy, #wpfd-paste, #wpfd-delete, #wpfd-download, #wpfd-uncheck').hide();
            }

            if (wpfd_permissions.can_edit_category) {
                var remote_file = (_wpfd_text('add_remote_file') == '1' && !isCloudCategory()) ? '<a href="" id="add_remote_file" class="ju-button gray-outline-button">' + _wpfd_text('Add remote file') + '</a> ' : '';
                $('<div id="file_dropbox"><span class="message">' + _wpfd_text('Drag & Drop your Document here') + '</span><input class="hide" type="file" id="upload_input" multiple="">' + remote_file + '<span id="upload_button" class="ju-button gray-outline-button">' + _wpfd_text('Select files') + '</span></div><div class="clr"></div>').appendTo('#preview');
                $('#add_remote_file').on('click', function (e) {

                    var allowed = wpfd_admin.allowed.split(',');
                    allowed.sort();
                    var allowed_select = '<select id="wpfd-remote-type">';
                    $.each(allowed, function (i, v) {
                        allowed_select += '<option value="' + v + '">' + v + '</option>';
                    });
                    allowed_select += '</select>';
                    bootbox.dialog('<div class="">  ' +
                        '<div class="form-horizontal wpfd-remote-form"> ' +
                        '<div class="control-group"> ' +
                        '<label class=" control-label" for="wpfd-remote-title">'+ _wpfd_text('Title') + '</label> ' +
                        '<div class="controls"> ' +
                        '<input id="wpfd-remote-title" name="wpfd-remote-title" type="text" placeholder="'+ _wpfd_text('Title') + '" class=""> ' +
                        '</div> ' +
                        '</div> ' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="wpfd-remote-url">'+ _wpfd_text('Remote URL') + '</label> ' +
                        '<div class="controls">' +
                        '<input id="wpfd-remote-url" name="wpfd-remote-url" type="text" placeholder="'+ _wpfd_text('URL') + '" class=""> ' +
                        '</div> </div>' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="wpfd-remote-type">'+ _wpfd_text('File Type') + '</label> ' +
                        '<div class="controls">' +
                        allowed_select +
                        '</div> </div>' +
                        '</div>  </div>',
                        [{
                            "label": _wpfd_text('Save'),
                            "class": "button-primary",
                            "callback": function () {
                                var category_id = $('input[name=id_category]').val();
                                var remote_title = $('#wpfd-remote-title').val();
                                var remote_url = $('#wpfd-remote-url').val();
                                var remote_type = $('#wpfd-remote-type').val();
                                $.ajax({
                                    url: wpfdajaxurl + "task=files.addremoteurl&id_category=" + category_id,
                                    data: {
                                        remote_title: remote_title,
                                        remote_url: remote_url,
                                        remote_type: remote_type
                                    },
                                    type: "POST"
                                }).done(function (data) {
                                    result = $.parseJSON(data);
                                    if (result.response === true) {
                                        updateCatCount(category_id, 1);
                                        updatepreview();
                                    } else {
                                        bootbox.alert(result.response);
                                    }
                                });
                            }
                        }, {
                            "label": _wpfd_text('Cancel'),
                            "class": "s",
                            "callback": function () {

                            }
                        }]
                    );
                    return false;
                });
            }

            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            Wpfd.showhidecolumns();
            $('#preview').sortable('refresh');

            initDeleteBtn();
            $('#preview input[name="restable-toggle-cols"]').click(function (e) {
                setcookie_showcolumns();
            });

            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                updatepreview(null, null, $(this).data('ordering'), $(this).data('direction'));

                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }

                $('#ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                $('#orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                id_category = $('input[name=id_category]').val();
                $.ajax({
                    url: wpfdajaxurl + "task=category.saveparams&id=" + id_category,
                    type: "POST",
                    data: $('#category_params').serialize()
                }).done(function (data) {
                });
            });

            /** Show/hide right colum **/
            $('#preview .wpfd-flip').click(function (e) {
                if ($('#rightcol').hasClass('hide')) {
                    $('#rightcol').addClass('show').removeClass('hide');
                    $(this).css('transform', 'scale(1)');
                } else {
                    $('#rightcol').addClass('hide').removeClass('show');
                    $(this).css('transform', 'scale(-1)');
                }
            });

            // initUploadBtn();

            initFiles();


            $('#wpreview').unbind();

            Wpfd.uploader.assignBrowse($('#upload_button'));
            Wpfd.uploader.assignDrop($('#wpreview'));

            if (typeof(id_file) !== "undefined") {
                $('#preview .file[data-id-file="' + id_file + '"]').trigger('click');
            } else {
                showCategory();
                if (typeof($ordering) === 'undefined') {
                    loadGalleryParams();
                }
            }
            rloading('#wpreview');
            $('#wpfd-core #preview').trigger('wpfd_preview_updated');
        });
        initEditBtn();
        initDeleteBtn();


    }
    $('#wpreview .restablesearch').click(function (e) {
        e.preventDefault();
        $('#wpfd-toolbar').hide();
        $('.wpfd-search-file').addClass('show').removeClass('hide');
        $('#wpfd-categories-col').hide();
        $(this).hide();
    });

    $('.wpfd-btn-exit-search').click(function (e) {
        e.preventDefault();
        $('#wpfd-toolbar').show();
        $('.wpfd-search-file').addClass('hide').removeClass('show');
        $('#wpfd-categories-col').show();
        $('.wpfd-iconsearch').show();
    });

    $('#wpfd_filter_catid').change(function (e) {
        e.preventDefault();
        var filter_catid = $(this).val();
        if (filter_catid) {
            var keyword = $('.wpfd-search-file-input').val();
            searchFiles(keyword, filter_catid);
        }

    });

    $(".wpfd-search-file-input").on('keyup', function (e) {
        if (e.keyCode === 13) {
            var keyword = $(this).val();
            if (keyword) {
                searchFiles(keyword);
            }
        }
    });

    $('.wpfd-btn-search').click(function (e) {
        e.preventDefault();
        var keyword = $('.wpfd-search-file-input').val();
        searchFiles(keyword);
    });

    $('#versionspurge').on('click', function(e) {
        e.preventDefault();
        var vpmess = $('#versionpurgemessage');
        var securityCode = wpfd_var.wpfdsecurity;

        $.ajax({
            url: wpfdajaxurl + "task=config.prepareVersions",
            method: 'POST',
            data: {'security': securityCode},
            beforeSend: function() {

                vpmess.css('color', 'green');
                vpmess.hide().html('Loading versions...').show(200);
            },
            success: function(response) {
                if (!response.success) {
                    vpmess.css('color', 'red');
                    vpmess.hide().html(response.message).show(200);
                } else {
                    if (confirm(wpfd_admin.msg_purge_versions)) {
                        var keepVersions = $('input[name="versionlimit"]').val();
                        $.ajax({
                            url: wpfdajaxurl + "task=config.purgeVersions",
                            method: 'POST',
                            data: {'security': securityCode, 'keep': keepVersions},
                            beforeSend: function() {
                                vpmess.css('color', 'red');
                                vpmess.hide().html('Deleting versions...').show(200);
                            },
                            success: function(response) {
                                if (!response.success) {
                                    vpmess.css('color', 'red');
                                    vpmess.hide().html(response.message).show(200);
                                } else {
                                    vpmess.css('color', 'green');
                                    vpmess.hide().html('Deleted files revisions!').show(200);
                                }
                            }
                        });
                    }
                }
            }
        });
        setTimeout(function() {vpmess.hide().html('');}, 5000);
        return false;
    });

    function searchFiles(keyword, filter_catid, ordering, ordering_dir) {
        if (typeof(filter_catid) === "undefined" || filter_catid === null) {
            filter_catid = $('#wpfd_filter_catid').val();
        }
        var url = wpfdajaxurl + "task=files.search&format=raw";
        $.ajax({
            url: url,
            type: "POST",
            data: {
                "s": keyword,
                "cid": filter_catid,
                "orderCol": ordering,
                "orderDir": ordering_dir
            }
        }).done(function (data) {

            $('#preview').html($(data));

            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            $('#preview').sortable('refresh');
            Wpfd.showhidecolumns();
            initDeleteBtn();

            $('#preview .wpfd-flip').click(function (e) {
                if ($('#rightcol').hasClass('hide')) {
                    $('#rightcol').addClass('show').removeClass('hide');
                } else {
                    $('#rightcol').addClass('hide').removeClass('show');
                }
            });
            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                searchFiles(keyword, $(this).data('ordering'), $(this).data('direction'));

                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }

                $('#ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                $('#orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
            });


            // initUploadBtn();

            initFiles();


            $('#wpreview').unbind();
            // initDropbox($('#wpreview'));
            Wpfd.uploader.assignBrowse($('#upload_button'));
            Wpfd.uploader.assignDrop($('#wpreview'));

            if (typeof(id_file) !== "undefined") {
                $('#preview .file[data-id-file="' + id_file + '"]').trigger('click');
            } else {
                showCategory();
                if (typeof($ordering) === 'undefined') {
                    loadGalleryParams();
                }
            }
            rloading('#wpreview');
            $('#wpfd-core #wpreview').trigger('wpfd_admin_search');
        })
    }

    $(window).resize(function () {
        hideColumns();
    });

    //hide columns base on window size
    function hideColumns() {

        var w = $(window).width();
        if (w <= 1600 && w > 1440) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0').prop('checked', false);
        } else if (w <= 1440 && w > 1200) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0').prop('checked', false);
        } else if (w <= 1200 && w > 1024) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0').prop('checked', false);
        } else if (w <= 1024) {
            $('input[name="restable-toggle-cols"]').prop('checked', true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0,#restable-toggle-col-2-0').prop('checked', false);
        }
    }

    //show/hide columns base on cookie
    Wpfd.showhidecolumns = function() {
        if (!wpfd_admin.listColumns.length) {
            hideColumns();
            return;
        }
        $('.restable thead th').hide();
        $('.restable tbody td').hide();
        $('input[name="restable-toggle-cols"]').prop('checked', false);
        $.each(wpfd_admin.listColumns, function (i, v) {
            $('#' + v).prop('checked', true);

            var colOrder = parseInt($('#' + v).data('col'));
            if (isNaN(colOrder))  {
                colOrder = 0;
            }
            var col = colOrder + 1;
            $('.restable thead th:nth-child(' + col + ')').show();
            $('.restable tbody td:nth-child(' + col + ')').show();
        });
    }

    function setcookie_showcolumns() {
        var column_show = [];
        $('input[name="restable-toggle-cols"]').each(function (i, v) {
            if ($(v).is(':checked')) {
                column_show.push($(v).attr('id'));
            }
        });

        var url = wpfdajaxurl + "task=files.showcolumn";
        $.ajax({
            url: url,
            type: "POST",
            data: {
                column_show: column_show
            }
        }).done(function (data) {
            wpfd_admin.listColumns = column_show;
        });
    }

    /**
     * Init delete button
     */
    function initDeleteBtn() {
        $('.actions .trash').unbind('click').click(function (e) {
            that = this;
            bootbox.dialog(wpfd_admin.msg_ask_delete_file,
                [
                    {
                        "label": _wpfd_text('Cancel')
                    },
                    {
                        "label": _wpfd_text('Confirm'),
                        "callback": function() {
                            //Delete file
                            id_file = $(that).parents('.file').data('id-file');
                            var id_category = $('li.dd-item.dd3-item.active').data('id-category');
                            $.ajax({
                                url: wpfdajaxurl + "task=file.delete&id_file=" + id_file + "&id_category=" + id_category,
                                type: "POST",
                                data: {}
                            }).done(function (data) {
                                $(that).parents('.file').fadeOut(500, function () {
                                    $(this).remove();
                                    $('.gritter-item-wrapper ').remove();
                                    $.gritter.add({text: wpfd_admin.msg_remove_file});
                                });
                            });
                        }
                    }
                ]
            );
            return false;
        });
    }

    /**
     * Init files
     */
    function initFiles() {

        $(document).unbind('click.window').bind('click.window', function (e) {

            if ($(e.target).is('#rightcol')
                || $(e.target).hasClass('wpfd-flip')
                || $(e.target).parents('#rightcol').length > 0
                || $(e.target).parents('.bootbox.modal').length > 0
                || $(e.target).parents('.tagit-autocomplete').length > 0
                || $(e.target).parents('.mce-container').length > 0
                || $(e.target).parents('.calendar').length > 0
                || $(e.target).parents('.wpfd-btn-toolbar').length > 0
                || $(e.target).parents('.media-modal').length > 0
                || $(e.target).parents('#wp-link-wrap').length > 0
            ) {
                return;
            }
            $('#preview .file').removeClass('selected');
            $('.wpfd-btn-toolbar').find('#wpfd-cut, #wpfd-copy, #wpfd-paste, #wpfd-delete, #wpfd-download, #wpfd-uncheck').hide();
            showCategory();
        });

        $('#preview .file').unbind('click').click(function (e) {

            iselected = $(this).find('tr.selected').length;

            //Allow multiselect
            if (!(e.ctrlKey || e.metaKey)) {
                $('#preview .file.selected').removeClass('selected');
            }
            if (iselected === 0) {
                $(this).addClass('selected');
            }

            if ($('#preview .file.selected').length === 1) {
                loadFileParams();
                loadVersions();
                showFile();
                $('.wpfd-btn-toolbar').find('#wpfd-cut, #wpfd-copy, #wpfd-paste, #wpfd-delete, #wpfd-download, #wpfd-uncheck').show();
            } else {
                showCategory();
            }

            e.stopPropagation();
        });
    }

    /**
     * Init the file edit btn
     */
    function initEditBtn() {
        $('.wbtn a.edit').unbind('click').click(function (e) {
            that = this;
            id_file = $(that).parents('.wimg').find('img.img').data('id-file');
            $.ajax({
                url: wpfdajaxurl + "view=file&format=raw&id=" + id_file,
                type: "POST",
                data: {}
            }).done(function (data) {
                bootbox.dialog(data, [{
                    'label': _wpfd_text('Save'),
                    'class': 'btn-success',
                    'callback': function () {
                        var p = '';
                        $('#file-form .wpfdinput').each(function (index) {
                            p = p + $(this).attr('name') + '=' + $(this).attr('value') + '&';
                        });
                        $.ajax({
                            url: $('#file-form').attr('action'),
                            type: 'POST',
                            data: p,
                        }).done(function (data) {
                            //do nothing
                        });
                    },
                }, {
                    'label': _wpfd_text('Cancel', 'Cancel'),
                    'class': 'btn-warning',
                }], {header: _wpfd_text('Image parameters', 'Image parameters')});

            });
            return false;
        });
    }

    /**
     * Load category layout params
     */
    function loadGalleryParams() {
        id_category = $('input[name=id_category]').val();
        $.cookie('wpfd_selected_category', id_category);
        loading('#rightcol');
        $.ajax({
            url: wpfdajaxurl + "task=category.edit&layout=form&id=" + id_category
        }).done(function (data) {
            $('#galleryparams').html(data);
//            rloading($('.wpfdparams'));

            $('#galleryparams .wpfdparams #visibility').change(function () {
                if ($(this).val() !== '1') {
                    $('#galleryparams .wpfdparams #visibilitywrap').hide();
                    $('#galleryparams .wpfdparams #visibilitywrap input').prop('checked', false);
                } else {
                    $('#galleryparams .wpfdparams #visibilitywrap').show();
                }
            }).trigger('change');

            $('#wpfd-theme').change(function () {
                changeTheme();
            });
            initColor();
            $('.user-clear.cat').on('click', function () {
                $('.field-user-category-access-name.category').val('');
                $('.field-user-category-access.category').val('');
            });
            $('.user-clear.file').on('click', function () {
                $('.field-user-input-name.file').val('');
                $('.field-user-input.file').val('');
            });
            $('.user-clear-category').on('click', function () {
                $('.field-user-category-own-name').val('');
                $('.field-user-category-own').val('');
            });
            $('#galleryparams .wpfdparams button[type="submit"]').click(function (e) {
                e.preventDefault();
                id_category = $('input[name=id_category]').val();
                $.ajax({
                    url: wpfdajaxurl + "task=category.saveparams&id=" + id_category,
                    type: "POST",
                    data: $('#category_params').serialize()
                }).done(function (data) {

                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        $('.gritter-item-wrapper ').remove();
                        $.gritter.add({text: wpfd_admin.msg_save_category});
                        updatepreview();
                        loadGalleryParams();
                    } else {
                        bootbox.alert(result.response);
                    }
                    loadGalleryParams();
                });
                return false;
            });
            var event = $.Event('wpfd_category_param_loaded');
            $(document).trigger(event);
            rloading('#rightcol');
        });
    }



    // init change theme for category
    function changeTheme() {
        theme = $('#wpfd-theme').val();
        id_category = $('input[name=id_category]').val();

        $.ajax({
            url: wpfdajaxurl + "task=category.edit&layout=form&theme=" + theme + "&onlyTheme=1&id=" + id_category
        }).done(function (data) {
            $('#category-theme-params').html(data);
            initColor();
        })

    }

    // loading file layout
    function loadFileParams() {
        id_file = jQuery('.file.selected').data('id-file');
        catid_file = jQuery('.file.selected').data('catid-file');
        var title = jQuery('.file.selected').find('.title').text();
        is_remoteurl = $('.file.selected').hasClass('is-remote-url');
        var linkdownload = jQuery('.file.selected').data('linkdownload');
        var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');
        if (catid_file !== idCategory) {
            $('#fileversion').hide();
            var txt1 = "<p>" + wpfd_admin.msg_multi_files_text + "</p>";
            var btn = "<a class='button button-primary edit-original-file'>" + wpfd_admin.msg_multi_files_btn_label + "</a>";
            $('#fileparams').html(txt1 + btn);
            $('#fileparams .edit-original-file').click(function (e) {
                Wpfd.catRefTofileId = id_file;
                $('li.dd-item.dd3-item[data-id-category="' + catid_file + '"] >div.dd-content').click();
            });
            return true;
        }
        Wpfd.catRefTofileId = false;
        $('#fileversion').show();
        loading('#rightcol');

        var fileInfo = [];
        fileInfo.push({'fileId': id_file, 'catid': idCategory, 'title': title});

        var oldFileAjax = fileAjax;
        if (oldFileAjax !== null) {
            oldFileAjax.abort();
        }
        fileAjax = $.ajax({
            url: wpfdajaxurl + "task=file.display",
            type: 'POST',
            data: {fileInfo: fileInfo, security: wpfd_var.wpfdsecurity}
        }).done(function (data) {
            $('#fileparams').html(data);
            if (is_remoteurl) {
                $('.wpfdparams').find('.wpfd-hide').removeClass('wpfd-hide');
            }
            $('#fileparams .wpfdparams input[type="submit"]').each(function() {
                $(this).click(function (e) {
                    e.preventDefault();
                    var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');
                    id_file = jQuery('.file.selected').data('id-file');
                    var fileData = $('#fileparams .wpfdparams').serialize();

                    fileData = unserialize(fileData);

                    var publishDateField = $('#fileparams .wpfdparams #publish');
                    var newFileData = '';
                    if (publishDateField.length > 0) {
                        var d = publishDateField.datetimepicker('getValue');
                        var month = d.getMonth() + 1;
                        month = (month.toString().length === 1) ? '0' + month : month;
                        var vdate = (d.getDate().toString().length === 1) ? '0' + d.getDate() : d.getDate();
                        var hours = (d.getHours().toString().length === 1) ? '0' + d.getHours() : d.getHours();
                        var minutes = (d.getMinutes().toString().length === 1) ? '0' + d.getMinutes() : d.getMinutes();
                        var seconds = (d.getSeconds().toString().length === 1) ? '0' + d.getSeconds() : d.getSeconds();
                        fileData.publish = d.getFullYear() + '-' + month + '-' + vdate + ' ' + hours + ':' + minutes + ':' + seconds;
                    }
                    $.each(fileData, function(key, value) {
                        newFileData += key + '=' + value + '&';
                    });

                    $.ajax({
                        url: wpfdajaxurl + "task=file.save&id=" + id_file + "&idCategory=" + idCategory,
                        method: "POST",
                        //dataType: 'json',
                        data: newFileData
                    }).done(function (data) {
                        if (typeof data === 'string') {
                            result = jQuery.parseJSON(data);
                        } else {
                            result = data;
                        }
                        if (result.response === true) {
                            loadFileParams();
                            $('.gritter-item-wrapper ').remove();
                            $.gritter.add({text: wpfd_admin.msg_save_file});
                        } else {
                            bootbox.alert(result.response);
                            loadFileParams();
                        }

                        if (typeof result.datas.new_id !== 'undefined') {
                            updatepreview(null, result.datas.new_id);
                        } else {
                            if ($('.wpfd-search-file').hasClass('hide')) {
                                updatepreview(null, id_file);
                            }
                        }
                    });
                    return false;
                });
            });
            $('.user-clear.cat').on('click', function () {
                $('.field-user-input-name.category').val('');
                $('.field-user-input.category').val('');
            });
            $('.user-clear.file').on('click', function () {
                $('.field-user-input-name.file').val('');
                $('.field-user-input.file').val('');
            });

            $('.media-clear.file').on('click', function () {
                $('#file_custom_icon').val('');
            });

            $('#file_multi_category_old').parent().hide();

            $('.file_direct_link').val(linkdownload);

            $('.btn_file_direct_link').on('click', function () {
                var linkcopy = $('.file_direct_link').val();
                var inputlink = document.createElement("input");
                inputlink.setAttribute("value", linkcopy);
                document.body.appendChild(inputlink);
                inputlink.select();
                document.execCommand("copy");
                document.body.removeChild(inputlink);
                $.gritter.add({text: wpfd_admin.msg_copied_to_clipboard});
            });

            var select_media;

            $('#select_media_button').click(function (e) {
                e.preventDefault();
                //If the uploader object has already been created, reopen the dialog
                if (select_media) {
                    select_media.open();
                    return;
                }
                //Extend the wp.media object
                select_media = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Image',
                    multiple: false,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: 'Choose Image'
                    }
                });
                var contentArr = wpfd_var.contenturl.split('/');
                var contentUrl = '/' + contentArr[contentArr.length - 1];
                //When a file is selected, grab the URL and set it as the text field's value
                select_media.on('select', function () {
                    attachment = select_media.state().get('selection').first().toJSON();
                    if (typeof attachment.sizes.thumbnail !== 'undefined') {
                        $('#file_custom_icon').val(attachment.sizes.thumbnail.url.substring(attachment.sizes.thumbnail.url.indexOf(contentUrl)));
                    } else if (typeof attachment.sizes.medium !== 'undefined') {
                        $('#file_custom_icon').val(attachment.sizes.medium.url.substring(attachment.sizes.medium.url.indexOf(contentUrl)));
                    } else {
                        $('#file_custom_icon').val(attachment.sizes.full.url.substring(attachment.sizes.full.url.indexOf(contentUrl)));
                    }

                });
                //Open the uploader dialog
                select_media.open();
            });

            var dateFormat = wpfd_var.dateFormat;

            $.datetimepicker.setLocale(wpfd_admin.locale);
            $('#publish').datetimepicker({
                timepicker: true,
                closeOnDateSelect: true,
                format: dateFormat,
                validateOnBlur: false,
                scrollMonth: false,
                scrollDate: false,
                scrollInput: false
            });
            $('#publish_img').on('click', function () {
                $('#publish').datetimepicker('toggle');
            });
            $('#expiration').datetimepicker({
                timepicker: true,
                closeOnDateSelect: true,
                format: dateFormat,
                validateOnBlur: false,
                scrollMonth: false,
                scrollDate: false,
                scrollInput: false
            });
            $('#expiration_img').on('click', function () {
                $('#expiration').datetimepicker('toggle');
            });
            if (!$.isNumeric(id_file)) {
                if ($('input#expiration').length && $('input#expiration').is(':visible')) {
                    $('input#expiration').parents('.control-group').remove();
                }
            }
            rloading('#rightcol');
            $('.chosen').chosen({width: '100%', search_contains: true});
        });
    }

    // load file versions
    function loadVersions() {
        id_category = $('input[name=id_category]').val();
        id_file = jQuery('.file.selected').data('id-file');
        var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');

        var fileInfo = [];
        fileInfo.push({'fileId': id_file, 'catid': idCategory});

        loading('#fileversion');
        var oldVersionAjax = versionAjax;
        if (oldVersionAjax !== null) {
            oldVersionAjax.abort();
        }
        versionAjax = $.ajax({
            url: wpfdajaxurl + "view=file&layout=versions",
            type: 'POST',
            data: {fileInfo: fileInfo, security: wpfd_var.wpfdsecurity}
        }).done(function (data) {
            $('#versions_content').html(data);
            $('#versions_content a.trash').unbind('click').click(function (e) {
                e.preventDefault();
                that = this;
                bootbox.dialog(
                    _wpfd_text('Are you sure remove version') + '?',
                    [
                        {
                            "label": _wpfd_text("Cancel")
                        },
                        {
                            "label": _wpfd_text('Confirm'),
                            "callback": function() {
                                vid = $(that).data('vid');
                                $.ajax({
                                    url: wpfdajaxurl + "task=file.deleteVersion&vid=" + vid + "&id_file=" + id_file + "&catid=" + id_category,
                                    type: "POST",
                                    data: {}
                                }).done(function (data) {
                                    result = jQuery.parseJSON(data);
                                    if (result.response === true) {
                                        $(that).parents('tr').remove();
                                    } else {
                                        bootbox.alert(result.response);
                                    }
                                });
                            }
                        }
                    ]
                );

                return false;
            });
            $('#versions_content a.restore').click(function (e) {
                e.preventDefault();
                that = this;
                file_ext = jQuery('.file.selected .txt').text();
                file_title = jQuery('.file.selected .title').text();
                bootbox.dialog(_wpfd_text('Are you sure restore file') + file_title + "." + file_ext + '?',
                    [
                        {
                            "label": _wpfd_text('Cancel')
                        },
                        {
                            "label": _wpfd_text('Confirm'),
                            "callback": function() {
                                vid = $(that).data('vid');
                                fid = $(that).data('id');
                                catid = $(that).data('catid');
                                $.ajax({
                                    url: wpfdajaxurl + "task=file.restore&vid=" + vid + "&id=" + fid + "&catid=" + catid,
                                    type: "POST",
                                    data: {}
                                }).done(function (data) {
                                    result = jQuery.parseJSON(data);
                                    if (result.response === true) {
                                        $(that).parents('tr').remove();

                                        id_file = jQuery('.file.selected').data('id-file');
                                        updatepreview(null, id_file);

                                    } else {
                                        bootbox.alert(result.response);
                                    }
                                });
                            }
                        }
                    ]);
                return false;
            });

            rloading('#fileversion');
        });
    }

    // init upload button
    function initUploadBtn() {
        $('#upload_button').on('click', function () {
            $('#upload_input').trigger('click');
            return false;
        });
    }

    var googleInterval, onedriveInterval, onedriveBusinessInterval, dropboxInterval;
    /**
     * Click to Sync with Google Drive
     */
    $('#btn-sync-gg').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');

        $.ajax({
            url: wpfd_var.wpfdajaxurl + '?action=googleSync',
            success: function (data) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_google_drive_sync_done});
                //window.location.reload();
                googleInterval = setInterval(function() {
                    $.ajax({
                        url: wpfd_var.wpfdajaxurl + '?action=google_sync_status',
                        success: function(response) {
                            if (response.success) {
                                if (response.total === 0) {
                                    clearInterval(googleInterval);
                                    $btn.button('complete');
                                    // Queue stoped. Prompt user reload page or not?
                                    bootbox.dialog(
                                      wpfd_admin.msg_promtp_sync_reload_page,
                                      [
                                          {
                                              "label": _wpfd_text('Cancel')
                                          },
                                          {
                                              "label": _wpfd_text('Confirm'),
                                              "callback": function () {
                                                  window.location.reload()
                                              }
                                          }
                                      ]
                                    );

                                }
                            }
                        }
                    });
                }, 2000);
            }
        });
    });
    /**
     * Click to Sync with Dropbox
     */
    $('#btn-sync-drop').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');

        $.ajax({
            url: wpfd_var.wpfdajaxurl + '?action=dropboxSync',
            success: function (data) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_google_drive_sync_done});
                //window.location.reload();
                dropboxInterval = setInterval(function() {
                    $.ajax({
                        url: wpfd_var.wpfdajaxurl + '?action=dropbox_sync_status',
                        success: function(response) {
                            if (response.success) {
                                if (response.total === 0) {
                                    clearInterval(dropboxInterval);
                                    $btn.button('complete');
                                    // Queue stoped. Prompt user reload page or not?
                                    bootbox.dialog(
                                      wpfd_admin.msg_promtp_sync_reload_page,
                                      [
                                          {
                                              "label": _wpfd_text('Cancel')
                                          },
                                          {
                                              "label": _wpfd_text('Confirm'),
                                              "callback": function () {
                                                  window.location.reload()
                                              }
                                          }
                                      ]
                                    );

                                }
                            }
                        }
                    });
                }, 2000);
            }
        });
    });
    // $('#btn-sync-drop').click(function (e) {
    //     e.preventDefault();
    //     var $btn = $(this).button('loading');
    //     $.ajax({
    //         url: wpfd_var.wpfdajaxurl + '?action=dropboxSync'
    //     }).done(function (data) {
    //         $btn.button('complete');
    //         $('.gritter-item-wrapper ').remove();
    //         $.gritter.add({text: wpfd_admin.msg_sync_done});
    //         window.location.reload();
    //     });
    // });

    /**
     * Click to Sync with OneDrive
     */
    $('#btn-sync-onedrive').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');

        $.ajax({
            url: wpfd_var.wpfdajaxurl + '?action=onedriveSync',
            success: function (data) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_google_drive_sync_done});
                //window.location.reload();
                onedriveInterval = setInterval(function() {
                    $.ajax({
                        url: wpfd_var.wpfdajaxurl + '?action=onedrive_sync_status',
                        success: function(response) {
                            if (response.success) {
                                if (response.total === 0) {
                                    clearInterval(onedriveInterval);
                                    $btn.button('complete');
                                    // Queue stoped. Prompt user reload page or not?
                                    bootbox.dialog(
                                      wpfd_admin.msg_promtp_sync_reload_page,
                                      [
                                          {
                                              "label": _wpfd_text('Cancel')
                                          },
                                          {
                                              "label": _wpfd_text('Confirm'),
                                              "callback": function () {
                                                  window.location.reload()
                                              }
                                          }
                                      ]
                                    );

                                }
                            }
                        }
                    });
                }, 2000);
            }
        });
    });
    // $('#btn-sync-onedrive').click(function (e) {
    //     e.preventDefault();
    //     var $btn = $(this).button('loading');
    //
    //     $.ajax({
    //         url: wpfd_var.wpfdajaxurl + '?action=onedriveSync'
    //     }).done(function (data) {
    //         data = JSON.parse(data);
    //         if (data.response === false) {
    //             $('.gritter-item-wrapper ').remove();
    //             $.gritter.add({text: data.datas});
    //             $btn.button('complete');
    //         } else {
    //             $btn.button('complete');
    //             $('.gritter-item-wrapper ').remove();
    //             $.gritter.add({text: wpfd_admin.msg_sync_done});
    //             window.location.reload();
    //         }
    //     });
    // });

    /**
     * Click to Sync with OneDrive
     */
    $('#btn-sync-onedrive-business').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');

        $.ajax({
            url: wpfd_var.wpfdajaxurl + '?action=onedriveBusinessSync',
            success: function (data) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_google_drive_sync_done});
                //window.location.reload();
                onedriveBusinessInterval = setInterval(function() {
                    $.ajax({
                        url: wpfd_var.wpfdajaxurl + '?action=onedrive_business_sync_status',
                        success: function(response) {
                            if (response.success) {
                                if (response.total === 0) {
                                    clearInterval(onedriveBusinessInterval);
                                    $btn.button('complete');
                                    // Queue stoped. Prompt user reload page or not?
                                    bootbox.dialog(
                                      wpfd_admin.msg_promtp_sync_reload_page,
                                      [
                                          {
                                              "label": _wpfd_text('Cancel')
                                          },
                                          {
                                              "label": _wpfd_text('Confirm'),
                                              "callback": function () {
                                                  window.location.reload()
                                              }
                                          }
                                      ]
                                    );

                                }
                            }
                        }
                    });
                }, 2000);
            }
        });
    });
    // $('#btn-sync-onedrive-business').click(function (e) {
    //     e.preventDefault();
    //     var $btn = $(this).button('loading');
    //
    //     $.ajax({
    //         url: wpfd_var.wpfdajaxurl + '?action=onedriveBusinessSync'
    //     }).done(function (data) {
    //         data = JSON.parse(data);
    //         if (data.response === false) {
    //             $('.gritter-item-wrapper ').remove();
    //             $.gritter.add({text: data.datas});
    //             $btn.button('complete');
    //         } else {
    //             $btn.button('complete');
    //             $('.gritter-item-wrapper ').remove();
    //             $.gritter.add({text: wpfd_admin.msg_sync_done});
    //             window.location.reload();
    //         }
    //     });
    // });

    /**
     * Click on new category btn
     */
    Wpfd.initNewCategory = function() {
        $('#newcategory a').unbind('click').on('click', function (e) {
            if (!wpfd_permissions.can_create_category) {
                bootbox.alert(wpfd_permissions.translate.wpfd_create_category);
                return false;
            }
            e.preventDefault();
            var $newCategoryButton = $(e.target);
            var parentId = 0;
            var selectedCategory = $('#categorieslist li.dd-item.active');
            // Find parent category
            var selectedParentList = selectedCategory.parent(); // Travel to ol
            if (selectedParentList.attr('id') === 'categorieslist') {
                // Create new category on root
                parentId = 0;
            } else {
                parentId = selectedParentList.parent().data('id-category'); // Travel to parent of selected ol
            }
            var type = null;
            if ($newCategoryButton.hasClass('googleCreate')) {
                type = 'googledrive';
            } else if ($newCategoryButton.hasClass('dropboxCreate')) {
                type = 'dropbox';
            } else if ($newCategoryButton.hasClass('onedriveCreate')) {
                type = 'onedrive';
            } else if ($newCategoryButton.hasClass('onedriveBusinessCreate')) {
                type = 'onedrive_business';
            } else {
                type = 'wordpress';
            }

            var addCategoryAjaxUrl = null;
            if (type === 'googledrive') {
                addCategoryAjaxUrl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonAddCategory&type=" + type;
            } else if (type === 'dropbox') {
                addCategoryAjaxUrl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonAddDropCategory&type=" + type;
            } else if (type === 'onedrive') {
                addCategoryAjaxUrl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonAddOneDriveCategory&type=" + type;
            } else if (type === 'onedrive_business') {
                addCategoryAjaxUrl = wpfd_var.wpfdajaxurl + "?action=wpfdAddonAddOneDriveBusinessCategory&type=" + type;
            } else if (type === 'wordpress') {
                addCategoryAjaxUrl = wpfdajaxurl + "task=category.addCategory";
            }

            $.ajax({
                url: addCategoryAjaxUrl,
                type: 'POST',
                data: {parentId: parentId}
            }).done(function (data) {
                result = jQuery.parseJSON(data);
                if (result.response === true) {
                    var icon = '<i class="material-icons wpfd-folder">folder</i>';
                    if (type === 'googledrive') {
                        icon = '<i class="google-drive-icon"></i> ';
                        // $("#newcategory a.dropdown-toggle").click();
                    } else if (type === 'dropbox') {
                        icon = '<i class="dropbox-icon"></i>';
                        // $("#newcategory a.dropdown-toggle").click();
                    } else if (type === 'onedrive') {
                        icon = '<i class="onedrive-icon"></i>';
                        // $("#newcategory a.dropdown-toggle").click();
                    } else if (type === 'onedrive_business') {
                        icon = '<i class="onedrive-business-icon"></i>';
                        // $("#newcategory a.dropdown-toggle").click();
                    }

                    link = '' +
                        '<li class="dd-item dd3-item" data-id="' + result.datas.id_category +
                        '" data-id-category="' + result.datas.id_category + '">' +
                        '<div class="dd-handle dd3-handle">' + icon + '</div>' +
                        '<div class="dd-content dd3-content">';
                    if (wpfd_permissions.can_edit_category) {
                        link += '<a class="edit"><i class="icon-edit"></i></a>';
                    }
                    if (wpfd_permissions.can_delete_category) {
                        link += '<a class="trash"><i class="icon-trash"></i></a>';
                    }
                    if ($('.dd3-content .countfile').length) {
                        link += '<span class="countfile">(0)</span>';
                    }
                    link += '<a href="" class="t">' +
                        '<span class="title">' + result.datas.name + '</span>' +
                        '</a>' +
                        '</div>';
                    if (wpfd_var.new_category_position == 'end') {
                        $(link).appendTo(selectedParentList);
                    } else if (wpfd_var.new_category_position == 'top') {
                        $(link).prependTo(selectedParentList);
                    }
                    Wpfd.initMenu();
                    $('#wpfd-categories-col #categorieslist li[data-id-category=' + result.datas.id_category + '] .dd-content').click();
                    $('#insertcategory').show();
                    $('.gritter-item-wrapper ').remove();
                    $.gritter.add({text: wpfd_admin.msg_add_category});
                    setTimeout(Wpfd.saveTemp, 3000);
                    catDroppable();
                    $('#wpfd-categories-col #categorieslist li[data-id-category=' + result.datas.id_category + '] .dd-content').trigger('wpfd_category_created');
                } else {
                    bootbox.alert(result.response);

                }
                Wpfd.initNewCategory();
            });
            return false;
        });
    };
    Wpfd.initNewCategory();

    function toMB(mb) {
        return mb * 1024 * 1024;
    }

    var allowedExt = wpfd_admin.allowed;
    allowedExt = allowedExt.split(',');
    allowedExt.sort();
    // Init the Version Uploader
    var versionUploader = new Resumable({
        target: wpfdajaxurl + 'task=files.version',
        query: {
            id_file: $('.file.selected').data('id-file'),
            id_category: $('input[name=id_category]').val()
        },
        fileParameterName: 'file_upload',
        simultaneousUploads: 2,
        maxFiles: 1,
        maxFileSize: toMB(wpfd_admin.maxFileSize),
        chunkSize: wpfd_admin.serverUploadLimit - 50 * 1024, // Reduce 50KB to avoid error
        forceChunkSize: true,
        fileType: allowedExt,
        maxFilesErrorCallback: function (file) {
            bootbox.alert(_wpfd_text('Too many files') + '!');
        },
        maxFileSizeErrorCallback: function (file) {
            bootbox.alert(file.name + ' ' + _wpfd_text('is too large') + '!');
        },
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

    if (!versionUploader.support) {
        bootbox.alert(_wpfd_text('Your browser does not support HTML5 file uploads') + '!');
    }

    if (typeof (willUploadVersion) === 'undefined') {
        var willUploadVersion = true;
    }

    versionUploader.on('filesAdded', function (files) {
        if (!wpfd_permissions.can_edit_category) {
            bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
            return false;
        }

        $('#dropbox_version .upload').addClass('hide');

        files.forEach(function (file) {
            $('#dropbox_version .progress').addClass(file.uniqueIdentifier);
            $('#dropbox_version .progress').removeClass('hide');
        });

        if (files.length > 0) {
            versionUploader.opts.query = {
                id_file: $('.file.selected').data('id-file'),
                id_category: $('input[name=id_category]').val()
            };

            if (willUploadVersion) versionUploader.upload();
        }
    });
    versionUploader.on('fileProgress', function (file) {
        $('#dropbox_version .progress.' + file.uniqueIdentifier)
            .find('.bar').width(Math.floor(file.progress() * 100) + '%');

    });
    versionUploader.on('fileError', function (file, msg) {
        $('#dropbox_version .progress').removeClass(file.uniqueIdentifier);
        $('#dropbox_version .progress').addClass('hide');

        $.gritter.add({
            text: file.fileName + ' ' + _wpfd_text('error while uploading') + '!',
            class_name: 'error-msg'
        });
    });
    versionUploader.on('fileSuccess', function (file, res) {
        $('#dropbox_version .progress').removeClass(file.uniqueIdentifier);
        // $('#dropbox_version .progress').addClass('hide');

        var response = JSON.parse(res);
        if (typeof(response) === 'string') {
            bootbox.alert('<div>' + response + '</div>');
            return false;
        }

        if (response.response !== true) {
            bootbox.alert(response.response);
            return false;
        }

        $.gritter.add({
            text: file.fileName + ' ' + _wpfd_text('uploaded successfully') + '!'
        });
    });
    versionUploader.on('complete', function () {
        //$('#dropbox_version .progress').delay(500).fadeIn(500).hide(0, function () {
        $('#dropbox_version .progress').addClass('hide');
        $('#dropbox_version .upload').removeClass('hide');
        $('#dropbox_version .progress .bar')
            .width('0');
        id_file = $('.file.selected').data('id-file');
        $("#upload_input_version").val('');
        updatepreview(null, id_file);
        //});
    });
    versionUploader.assignBrowse($('#upload_button_version'));
    versionUploader.assignDrop($('#fileversion'));

    // Init the uploader
    Wpfd.uploader = new Resumable({
        target: wpfdajaxurl + 'task=files.upload',
        query: {
            id_category: $('input[name=id_category]').val()
        },
        fileParameterName: 'file_upload',
        simultaneousUploads: 2,
        maxChunkRetries: 1,
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


    if (!Wpfd.uploader.support) {
        bootbox.alert(_wpfd_text('Your browser does not support HTML5 file uploads') + '!');
    }

    if (typeof (willUpload) === 'undefined') {
        var willUpload = true;
    }

    Wpfd.uploader.on('filesAdded', function (files) {
        if (!wpfd_permissions.can_edit_category) {
            bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
            return false;
        }

        // Do not run uploader if no files added or upload same files again
        if (files.length > 0) {
            files.forEach(function (file) {
                Wpfd.log(file.uniqueIdentifier + ' added to upload queue!');
                wpfd_status.progressAdd(file.uniqueIdentifier, file.fileName, $('input[name=id_category]').val());
            });
        }
    });

    Wpfd.uploader.on('fileProgress', function (file) {
        wpfd_status.progressUpdate(file.uniqueIdentifier, Math.floor(file.progress() * 100) + '%');
    });

    Wpfd.uploader.on('fileSuccess', function (file, res) {
        wpfd_status.progressDone(file.uniqueIdentifier);

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
        var catId = Wpfd.uploader.opts.query.id_category;
        updateCatCount(catId, 1);
        $.gritter.add({
            text: file.fileName + ' ' + _wpfd_text('uploaded successfully') + '!'
        });
    });

    Wpfd.uploader.on('fileError', function (file, msg) {
        wpfd_status.progressError(file.uniqueIdentifier);

        $.gritter.add({
            text: file.fileName + ' ' + _wpfd_text('error while uploading') + '!',
            class_name: 'error-msg'
        });
    });

    Wpfd.uploader.on('complete', function () {
        wpfd_status.close();
        updatepreview();
    });

    /**
     * Init the dropbox
     **/
    function initDropbox(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input',
            maxfiles: 30,
            maxfilesize: Wpfd.maxfilesize,
            queuefiles: 2,
            data: {
                id_category: function () {
                    return $('input[name=id_category]').val();
                }
            },
            url: wpfdajaxurl + 'task=files.upload',

            uploadFinished: function (i, file, response) {
                if (response.response === true) {
                    $.data(file).addClass('done');
                    $.data(file).find('img').data('id-file', response.datas.id_file);
                } else {
                    bootbox.alert(response.response);
                    $.data(file).remove();
                }
            },

            error: function (err, file) {
                switch (err) {
                    case 'BrowserNotSupported':
                        bootbox.alert(_wpfd_text('Your browser does not support HTML5 file uploads', 'Your browser does not support HTML5 file uploads!'));
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(_wpfd_text('Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + _wpfd_text('is too large', 'is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
                if (!wpfd_permissions.can_edit_category) {
                    bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
                    return false;
                }
            },

            uploadStarted: function (i, file, len) {
                var preview = $('<div class="wpfd_process_full" style="display: block;">' +
                    '<div class="wpfd_process_run" data-w="0" style="width: 0%;"></div>' +
                    '</div>');

                var reader = new FileReader();

                // Reading the file as a DataURL. When finished,
                // this will trigger the onload function above:
                reader.readAsDataURL(file);

                $('#preview .restable').after(preview);
//                        $('#dropbox').before(preview);

                // Associating a preview container
                // with the file, using jQuery's $.data():

                $.data(file, preview);
            },

            progressUpdated: function (i, file, progress) {
                $.data(file).find('.wpfd_process_run').width(progress + '%');
            },

            afterAll: function () {
                $('#preview .progress').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .uploaded').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .file').delay(1200).show(1200, function () {
                    $(this).removeClass('done placeholder');
                });
                updatepreview();
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_upload_file});
            },
            rename: function (name) {
                ext = name.substr(name.lastIndexOf('.'), name.length);
                name = name.substr(0, name.lastIndexOf('.'));

                var uint8array = new TextEncoderLite().encode(name);

                base64 = fromByteArray(uint8array);
                base64 = base64.replace("/", "|");
                return base64 + ext;
            }
        });
    }

    if (_wpfd_text('close_categories') === '1') {
        $('.nested').nestable('collapseAll');
    }

    if (typeof(window.parent.tinyMCE) !== 'undefined') {
        var content = "";
        if (window.parent.tinyMCE.activeEditor !== null && window.parent.tinyMCE.activeEditor.selection) {
            content = window.parent.tinyMCE.activeEditor.selection.getContent();
        }
        var file = content.match('<img.*data\-file="([0-9a-zA-Z_]+)".*?>');
        var category = content.match('<img.*data\-category="([0-9]+)".*?>');
        var file_category = content.match('<img.*data\-category="([0-9]+)".*?>');
        if (window.parent.selectedCatId !== undefined) {
            file_category = [0, window.parent.selectedCatId];
        }
        if (window.parent.selectedFileId !== undefined) {
            file = [0, window.parent.selectedFileId];
        }
        if (window.parent.selectedFileId === undefined && window.parent.selectedCatId !== undefined) {
            category = [0, window.parent.selectedCatId];
        }
        if (file !== null && file_category !== null) {
            $('#categorieslist li').removeClass('active');
            $('#categorieslist li[data-id-category="' + file_category[1] + '"]').addClass('active');
            $('input[name=id_category]').val(file_category[1]);
            updatepreview(file_category[1], file[1]);
        } else if (category !== null) {
            $('#categorieslist li').removeClass('active');
            $('#categorieslist li[data-id-category="' + category[1] + '"]').addClass('active');
            $('input[name=id_category]').val(category[1]);
            updatepreview(category[1]);
            loadGalleryParams();
        } else {
            var cate = $.cookie('wpfd_selected_category');

            if (cate !== null) {
                $('#categorieslist li').removeClass('active');
                $('#categorieslist li[data-id-category="' + cate + '"]').addClass('active');
                $('input[name=id_category]').val(cate);
                setTimeout(function () {
                    updatepreview(cate);
                    loadGalleryParams();
                }, 100);

            } else {
                updatepreview();
                loadGalleryParams();
            }
        }
    }

    /**
     * Init the dropbox
     **/
    function initDropboxVersion(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input_version',
            maxfiles: 1,
            maxfilesize: Wpfd.maxfilesize,
            queuefiles: 1,
            data: {
                id_file: function () {
                    return $('.file.selected').data('id-file');
                },
                id_category: function () {
                    return $('input[name=id_category]').val();
                }
            },
            url: wpfdajaxurl + 'task=files.version',

            uploadFinished: function (i, file, response) {

                if (response.response === true) {

                } else {
                    bootbox.alert(response.response);

                    $('#dropbox_version .progress').addClass('hide');
                    $('#dropbox_version .upload').removeClass('hide');
                }
            },

            error: function (err, file) {
                switch (err) {
                    case 'BrowserNotSupported':
                        bootbox.alert(_wpfd_text('Your browser does not support HTML5 file uploads'));
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(_wpfd_text('Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + _wpfd_text('is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
//                        if(!file.type.match(/^image\//)){
//                                bootbox.alert(_wpfd_text('Only images are allowed','Only images are allowed')+'!');
//                                return false;
//                        }
            },

            uploadStarted: function (i, file, len) {

                // Associating a preview container
                // with the file, using jQuery's $.data():
                $('#dropbox_version .upload').addClass('hide');
                $('#dropbox_version .progress').removeClass('hide');
//                        $.data(file,preview);
            },

            progressUpdated: function (i, file, progress) {
                $('#dropbox_version .bar').width(progress + '%');
            },

            afterAll: function () {

                $('#dropbox_version .progress').addClass('hide');
                $('#dropbox_version .upload').removeClass('hide');
                id_file = $('.file.selected').data('id-file');
                $("#upload_input_version").val('');
                updatepreview(null, id_file);


            }
        });
    }




    if (typeof l10n !== 'undefined') {

        $('#wpfd-jao').wpfd_jaofiletree({
            script: wpfdajaxurl + "task=category.listdir",
            usecheckboxes: 'files',
            showroot: '/'
        });
    }

    // init color field
    function initColor() {
        $('.wp-color-field').minicolors({position: 'bottom right'});
    }

    function loading(e) {
        $(e).addClass('dploadingcontainer');
        $(e).append('<div class="dploading"></div>');
    }

    function rloading(e) {
        $(e).removeClass('dploadingcontainer');
        $(e).find('div.dploading').remove();
    }

    // file in category shortcode
    $('#file_cat_id,#file_cat_ordering,#file_cat_ordering_direct,#file_cat_number,#show_categories').on('change', function () {
        shortcode_file_cat_generator();
    });
    // Hide show_categories on load
    var show_categories_wrapper = $('#show_categories').parent().parent();
    if (show_categories_wrapper.length) {
        if ($('#file_cat_id').val().toString() === '0') {
            show_categories_wrapper.show();
        } else {
            show_categories_wrapper.hide();
        }
    }
    function shortcode_file_cat_generator() {
        var file_cat_id = $('#file_cat_id').val(),
            file_cat_ordering = $('#file_cat_ordering').val(),
            file_cat_ordering_direct = $('#file_cat_ordering_direct').val(),
            file_cat_number = $('#file_cat_number').val();
        if (file_cat_ordering === 'ordering') {
            file_cat_ordering = 'created_time';
        }

        var shortcode_file_cat = '[wpfd_category ';
        if (parseInt(file_cat_id) !== 0) {
            shortcode_file_cat += 'id="' + file_cat_id + '"';
        }
        if ($('#file_cat_id').val().toString() === '0') {
            show_categories_wrapper.show();
            shortcode_file_cat += ' show_categories="' + $('#show_categories').val() + '"';
        } else {
            show_categories_wrapper.hide();
        }
        shortcode_file_cat += ' order="' + file_cat_ordering +
            '" direction="' + file_cat_ordering_direct + '" number="' + file_cat_number + '" ]';
        $('#file_shortcode_generator').empty();
        $('#file_shortcode_generator').val(shortcode_file_cat);
    }


    $('#wpfd-container-config').tooltip();

    $(".widefat #select_all").click(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });
    // Close modal on click on backdrop
    $(document).on('click', '.modal-backdrop, .bootbox .button', function() {
        $('.modal-backdrop').remove();
        $(".bootbox").hide().modal("hide");
        bootbox.hideAll();
    });
});

/**
 * Insert the current category into a content editor
 */
function insertCategory() {
    id_category = jQuery('input[name=id_category]').val();
    code = '<img src="' + dir + '/app/admin/assets/images/t.gif"' +
        'data-wpfdcategory="' + id_category + '"' +
        'style="background: url(' + dir + '/app/admin/assets/images/folder_download.png) no-repeat scroll center center #D6D6D6;' +
        'border: 2px dashed #888888;' +
        'height: 200px;' +
        'border-radius: 10px;' +
        'width: 99%;" data-category="' + id_category + '" />';
    window.parent.tinyMCE.execCommand('mceInsertContent', false, code);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdmodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current file into a content editor
 */
function insertFile() {
    id_file = jQuery('.file.selected').data('id-file');
    id_category = jQuery('input[name=id_category]').val();
    code = '<img src="' + dir + '/app/admin/assets/images/t.gif"' +
        'data-file="' + id_file + '"' +
        'data-wpfdfile="' + id_file + '"' +
        'data-category="' + id_category + '"' +
        'style="background: url(' + dir + '/app/admin/assets/images/file_download.png) no-repeat scroll center center #D6D6D6;' +
        'border: 2px dashed #888888;' +
        'height: 100px;' +
        'border-radius: 10px;' +
        'width: 99%;" />';
    window.parent.tinyMCE.execCommand('mceInsertContent', false, code);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdmodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current category into a Elementor content editor
 */
function insertElementorCategory() {
    id_category = jQuery('input[name=id_category]').val();
    name_category = jQuery('#categorieslist li.dd-item.active > .dd-content span.title').text();
    jQuery('.elementor-control.elementor-control-wpfd_selected_category_id input[data-setting="wpfd_selected_category_id"]', window.parent.document).val(id_category);
    jQuery('.elementor-control.elementor-control-wpfd_selected_category_name input[data-setting="wpfd_selected_category_name"]', window.parent.document).val(name_category);
    window.parent.wpfd_category_widget_trigger_controls();
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdelementormodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current file into a Elementor content editor
 */
function insertElementorFile() {
    id_file = jQuery('.file.selected').data('id-file');
    name_file = jQuery('.file.selected .title').text();
    id_category = jQuery('input[name=id_category]').val();
    jQuery('.elementor-control.wpfd-file-id-controls input[data-setting="wpfd_file_id"]', window.parent.document).val(id_file);
    jQuery('.elementor-control.wpfd-category-id-controls input[data-setting="wpfd_category_id"]', window.parent.document).val(id_category);
    jQuery('.elementor-control.wpfd-file-name-controls input[data-setting="wpfd_file_name"]', window.parent.document).val(name_file);
    window.parent.wpfd_file_widget_trigger_controls();
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdelementormodal', window.parent.document).fadeOut(300);
}

/**
 * Insert the current category into a WPBakery content editor
 */
function insertWPBakeryCategory() {
    id_category     = jQuery('input[name=id_category]').val();
    name_category   = jQuery('#categorieslist li.dd-item.active > .dd-content span.title').text();
    jQuery('input[name="wpfd_category_random"]', window.parent.document).val(Math.random());
    jQuery('input[name="wpfd_selected_category_id"]', window.parent.document).val(id_category);
    jQuery('input.wpfd_category_title[name="wpfd_category_title"]', window.parent.document).val(name_category);
    window.parent.wpfd_wpbakery_category_trigger_controls(name_category);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdwpbakerymodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current file into a WPBakery content editor
 */
function insertWPBakeryFile() {
    fileId      = jQuery('.file.selected').data('id-file');
    fileName    = jQuery('.file.selected .title').text();
    categoryId  = jQuery('input[name=id_category]').val();
    jQuery('input[name="wpfd_file_random"]', window.parent.document).val(Math.random());
    jQuery('input[name="wpfd_file_id"]', window.parent.document).val(fileId);
    jQuery('input[name="wpfd_file_related_category_id"]', window.parent.document).val(categoryId);
    jQuery('input[name="wpfd_file_title"]', window.parent.document).val(fileName);
    window.parent.wpfd_wpbakery_file_trigger_controls(fileName);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdwpbakerymodal', window.parent.document).fadeOut(300);
}

/**
 * Insert the current category into a Avada content editor
 */
function insertAvadaCategory() {
    avada_category_id     = jQuery('input[name=id_category]').val();
    avada_category_name   = jQuery('#categorieslist li.dd-item.active > .dd-content span.title').text();
    jQuery('input#wpfd_selected_category_random', window.parent.document).val(Math.random());
    jQuery('input#wpfd_selected_category_id', window.parent.document).val(avada_category_id);
    jQuery('input#wpfd_selected_category_title', window.parent.document).val(avada_category_name);
    jQuery('input#element_content', window.parent.document).val(avada_category_name);
    window.parent.wpfd_avada_category_trigger_controls();
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdavadamodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current file into a Avada content editor
 */
function insertAvadaFile() {
    fileId      = jQuery('.file.selected').data('id-file');
    fileName    = jQuery('.file.selected .title').text();
    categoryId  = jQuery('input[name=id_category]').val();
    jQuery('input#wpfd_selected_file_random', window.parent.document).val(Math.random());
    jQuery('input#wpfd_selected_file_id', window.parent.document).val(fileId);
    jQuery('input#wpfd_selected_category_id_related', window.parent.document).val(categoryId);
    jQuery('input#wpfd_selected_file_title', window.parent.document).val(fileName);
    jQuery('input#element_content', window.parent.document).val(fileName);
    window.parent.wpfd_avada_file_trigger_controls();
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdavadamodal', window.parent.document).fadeOut(300);
}

var insertFileToWoo = function() {
    id_file = jQuery('.file.selected').data('id-file');
    id_category = jQuery('input[name=id_category]').val();
    file_name = jQuery('input[name=title]').val();

    window.parent.wpfdWooAddonAddFileRow({id: id_file, catid: id_category, name: file_name});
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdWoocommerceModal', window.parent.document).fadeOut(300);

    return false;
}
//From http://jquery-howto.blogspot.fr/2009/09/get-url-parameters-values-with-jquery.html
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getUrlVar(v) {
    if (typeof(getUrlVars()[v]) !== "undefined") {
        return getUrlVars()[v];
    }
    return null;
}

function preg_replace(array_pattern, array_pattern_replace, my_string) {
    var new_string = String(my_string);
    for (i = 0; i < array_pattern.length; i++) {
        var reg_exp = new RegExp(array_pattern[i], "gi");
        var val_to_replace = array_pattern_replace[i];
        new_string = new_string.replace(reg_exp, val_to_replace);
    }
    return new_string;
}

//https://gist.github.com/ncr/399624
jQuery.fn.single_double_click = function (single_click_callback, double_click_callback, timeout) {
    return this.each(function () {
        var clicks = 0, self = this;
        jQuery(this).click(function (event) {
            clicks++;
            if (clicks === 1) {
                setTimeout(function () {
                    if (clicks === 1) {
                        single_click_callback.call(self, event);
                    } else {
                        double_click_callback.call(self, event);
                    }
                    clicks = 0;
                }, timeout || 300);
            }
        });
    });
};

//http://stackoverflow.com/questions/11103447/jquery-sortable-cancel-and-revert-not-working-as-expected
//modified by joomunited.com
var _mouseStop = jQuery.ui.sortable.prototype._mouseStop;
jQuery.ui.sortable.prototype._mouseStop = function (event, noPropagation) {
    Wpfd.filetocat = false;

    if (!event) {
        return;
    }
    $ = jQuery;
    //If we are using droppables, inform the manager about the drop
    if ($.ui.ddmanager && !this.options.dropBehaviour) {
        $.ui.ddmanager.drop(this, event);
    }

    var options = this.options;
    var $item = $(this.currentItem);
    var el = this.element[0];
    var ui = this._uiHash(this);
    var current = $item.css(['top', 'left', 'position', 'width', 'height']);
    var cancel = options.revert && $.isFunction(options.beforeRevert) && !options.beforeRevert.call(el, event, ui);

    if (cancel) {
        this.cancel();
        $item.css(current);
        $item.animate(this.originalPosition, {
            duration: isNaN(options.revert) ? 500 : options.revert,
            always: function () {
                $('body').css('cursor', '');
                $item.css({position: '', top: '', left: '', width: '', height: '', 'z-index': ''});
                if ($.isFunction(options.update)) {
                    options.update.call(el, event, ui);
                }
            }
        });
    }

    return !cancel && _mouseStop.call(this, event, noPropagation);
};
