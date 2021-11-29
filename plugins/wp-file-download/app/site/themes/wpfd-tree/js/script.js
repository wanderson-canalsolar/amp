/**
 * Wpfd
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package WP File Download
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery(document).ready(function ($) {
    var sourcefiles = $("#wpfd-template-tree-files").html();
    var sourcecategories = $("#wpfd-template-tree-categories").html();
    var sourcefile = $("#wpfd-template-tree-box").html();
    var tree_hash = window.location.hash;

    initInputSelected();
    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytes.toString().toLowerCase() === 'n/a' ? bytes : bytesToSize(bytes);
    });

    treeInitClickFile();

    tree_hash = tree_hash.replace('#', '');
    if (tree_hash !== '') {
        var hasha = tree_hash.split('-');
        var hash_category_id = hasha[1];
        var hash_sourcecat = hasha[0];

        if (parseInt(hash_category_id) > 0) {
            setTimeout(function () {
                tree_loadcategory(hash_category_id, hash_sourcecat);
            }, 100);
        }
    }

    $('.wpfd-content-tree a.catlink').unbind('click.cat').bind('click.cat', function (e) {
        e.preventDefault();
        tree_load($(this).parents('.wpfd-content-tree').data('category'), $(this).data('idcat'), $(this));
        $(this).parent().removeClass('collapsed').addClass('expanded');
    });
    function initInputSelected() {
        $(document).on('change', ".wpfd-content-tree.wpfd-content-multi input.cbox_file_download", function () {
            inputSelect( $(this).parents('.wpfd-content')[0]);
        });
    }
    function inputSelect( context) {
        var selectedFiles = $("input.cbox_file_download:checked", context);
        var filesId = [];
        if (selectedFiles.length) {
            selectedFiles.each(function (index, file) {
                filesId.push($(file).data('id'));
            });
        }
        if (filesId.length > 0) {
            $(".wpfdSelectedFiles", context).remove();
            $('<input type="hidden" class="wpfdSelectedFiles" value="' + filesId.join(',') + '" />')
                .insertAfter($(" #root_category_slug", context));
            hideDownloadAllBtn(context, true);
            $(".tree-download-selected", context).remove();
            var downloadSelectedBtn = $('<a href="javascript:void(0);" class="tree-download-selected" style="display: block;">' + wpfdparams.translates.download_selected + '<i class="zmdi zmdi-check-all wpfd-download-category"></i></a>');
            downloadSelectedBtn.insertAfter($("#root_category_slug", context));
            initDownloadSelected();
        } else {
            $(".wpfdSelectedFiles", context).remove();
            $(".tree-download-selected", context).remove();
            hideDownloadAllBtn(context);
        }
    }
    function hideDownloadAllBtn(context, hide) {
        var downloadCatButton = $(".tree-download-category", context);
        if (downloadCatButton.length === 0 || downloadCatButton.hasClass('display-download-category')) {
            return;
        }
        if (hide) {
            $(".tree-download-category", context).hide();
        } else {
            $(".tree-download-category", context).show();
        }
    }

    function initDownloadSelected() {
        $('.wpfd-content-tree.wpfd-content-multi .tree-download-selected').on('click', function () {
            var context = $(this).parents('.wpfd-content')[0];
            if ($('.wpfdSelectedFiles', context).length > 0) {
                var category_name = $('#root_category_slug', context).val();
                var selectedFilesId = $('.wpfdSelectedFiles', context).val();
                $.ajax({
                    url: wpfdparams.wpfdajaxurl + "?action=wpfd&task=files.zipSeletedFiles&filesId=" + selectedFilesId + "&wpfd_category_id=" + $(context).attr('data-category'),
                    dataType: "json"
                }).done(function (results) {
                    if (results.success) {
                        var hash = results.data.hash;
                        window.location.href = wpfdparams.wpfdajaxurl + "?action=wpfd&task=files.downloadZipedFile&hash=" + hash + "&wpfd_category_id=" + $(context).attr('data-category') + "&wpfd_category_name=" + category_name;
                    } else {
                        alert(results.data.message);
                    }
                })
            }
        });
    }
    function tree_loadcategory($catid, $sourcecat) {
        $.ajax({
            url: wpfdparams.wpfdajaxurl + "task=categories.getParentsCats&id=" + $catid + "&displaycatid=" + $sourcecat,
            dataType: "json"
        }).done(function (ob) {
            tree_load($sourcecat, ob[0], $('.wpfd-content-tree [data-idcat="' + ob[0] + '"]'), ob);
        });
    }

    function treeInitClickFile() {
        $('.wpfd-content-tree .wpfd-file-link').unbind('click').click(function (e) {
            var context = $(this).parents('.wpfd-content')[0];
            var atthref = $(this).attr('href');
            if (atthref !== '#') {
                return;
            }
            e.preventDefault();
            var fileid = $(this).data('id');
            var categoryid = $(this).data('category_id');

            $.ajax({
                url: wpfdparams.wpfdajaxurl + "task=file.display&view=file&id=" + fileid + "&categoryid=" + categoryid + "&rootcat=" + $(context).attr('data-category'),
                dataType: "json",
                beforeSend: function() {
                    // setting a timeout
                    if($('body').has('wpfd-tree-box-loader') !== true) {
                        $('body').append('<div class="wpfd-tree-box-loader"></div>');
                    }
                }
            }).done(function (file) {
                var template = Handlebars.compile(sourcefile);
                var html = template(file);
                var box = $("#tree-wpfd-box");
                $('.wpfd-tree-box-loader').each(function () {
                    $(this).remove();
                });
                if (box.length === 0) {
                    $('body').append('<div id="tree-wpfd-box" style="display: hidden;"></div>');
                    box = $("#tree-wpfd-box");
                }
                box.empty();
                box.prepend(html);
                box.click(function (e) {
                    if ($(e.target).is('#tree-wpfd-box')) {
                        box.hide();
                    }
                    $('#tree-wpfd-box').unbind('click.box').bind('click.box', function (e) {
                        if ($(e.target).is('#tree-wpfd-box')) {
                            box.hide();
                        }
                    });
                });
                $('#tree-wpfd-box .wpfd-close').click(function () {
                    box.hide();
                });

                box.show();

                var dropblock = box.find('.dropblock');

                if ($(window).width() < 400) {
                    dropblock.css('margin-top', '0');
                    dropblock.css('margin-left', '0');
                    dropblock.css('top', '0');
                    dropblock.css('left', '0');
                    dropblock.height($(window).height() - parseInt(dropblock.css('padding-top'), 10) - parseInt(dropblock.css('padding-bottom'), 10));
                    dropblock.width($(window).width() - parseInt(dropblock.css('padding-left'), 10) - parseInt(dropblock.css('padding-right'), 10));
                } else {
                    dropblock.css('margin-top', (-(dropblock.height() / 2) - 20) + 'px');
                    dropblock.css('margin-left', (-(dropblock.width() / 2) - 20) + 'px');
                    dropblock.css('height', '');
                    dropblock.css('width', '');
                    dropblock.css('top', '');
                    dropblock.css('left', '');
                }

                if (typeof wpfdColorboxInit !== 'undefined') {
                    wpfdColorboxInit();
                }
                wpfdTrackDownload();

                $('body.elementor-default #tree-wpfd-box a.wpfd_downloadlink').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var link = $(this).attr('href');
                    window.location.href = link;
                });
            });
        });
    }

    function wantDelete(item, arr) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] === item) {
                arr.splice(i, 1);
                break;
            }
        }
    }

    function tree_load(sourcecat, category, elem, loadcats) {


        if (!jQuery.isEmptyObject(loadcats)) {
            wantDelete(category, loadcats);
        }

        var pathname = window.location.href.replace(window.location.hash, '');

        var ul = elem.parent().children('ul');
        $('.wpfd-content-tree').find('.active').removeClass('active');
        elem.parent().addClass('active');
        if (ul.length > 0) {
            //close cat
            ul.slideUp(400, null, function () {
                $(this).remove();
                elem.parent().removeClass('open expanded').addClass('collapsed');
                elem.parent().removeClass('wpfd-loading-tree');
                elem.parent().find('.wpfd-loading-tree-bg').remove();
                inputSelect(sourcecat);
            });
            var root_linkdownload_cat = $(".wpfd-content-tree[data-category=" + sourcecat + "] #root_linkdownload_cat").val();
            var root_countfile_cat = $(".wpfd-content-tree[data-category=" + sourcecat + "] #root_countfile_cat").val();
            $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").attr('href', root_linkdownload_cat);

            if (root_countfile_cat !== "0") {
                $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").removeClass("display-download-category");
            } else {
                $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").addClass("display-download-category");
            }

            return;
        } else {
            elem.parent().addClass('wpfd-loading-tree');
            elem.parent().prepend($('#wpfd-loading-tree-wrap').html());
        }
        if ($(elem).hasClass('clicked')) {
            return;
        }
        $(elem).addClass('clicked');
        //Get categories
        $.ajax({
            url: wpfdparams.wpfdajaxurl + "task=categories.display&view=categories&id=" + category,
            dataType: "json"
        }).done(function (categories) {

            window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + category + '-' + categories.category.slug);

            var template = Handlebars.compile(sourcecategories);
            var html = template(categories);
            if (categories.categories.length > 0) {
                elem.parents('li').append('<ul style="display:none;">' + html + '</ul>');
                $(".wpfd-content-tree[data-category=" + sourcecat + "] a.catlink").unbind('click.cat').bind('click.cat', function (e) {
                    e.preventDefault();
                    tree_load($(this).parents('.wpfd-content-tree').data('category'), $(this).data('idcat'), $(this));
                    treeInitClickFile();
                });
            }
            $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").attr('href', categories.category.linkdownload_cat);

            //Get files
            $.ajax({
                url: wpfdparams.wpfdajaxurl + "task=files.display&view=files&id=" + category + "&rootcat=" + sourcecat,
                dataType: "json"
            }).done(function (content) {

                if (content.files.length) {
                    $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").removeClass("display-download-category");
                } else {
                    $(".wpfd-content-tree[data-category=" + sourcecat + "] .tree-download-category").addClass("display-download-category");
                }

                var template = Handlebars.compile(sourcefiles);
                var html = template(content);
                html = $('<textarea/>').html(html).val();
                if (elem.parent().children('ul').length === 0) {
                    elem.parent().append('<ul style="display:none;">' + html + '</ul>');
                } else {
                    elem.parent().children('ul').append(html);
                }

                treeInitClickFile();
                elem.parent().children('ul').slideDown(400, null, function () {

                    elem.parent().addClass('open expanded');
                    elem.parent().removeClass('wpfd-loading-tree collapsed');
                    elem.parent().find('.wpfd-loading-tree-bg').remove();
                });

                if (!jQuery.isEmptyObject(loadcats)) {
                    var ccat = loadcats[0];
                    tree_load(sourcecat, ccat, $('.wpfd-content-tree [data-idcat="' + ccat + '"]'), loadcats);
                }
                inputSelect(sourcecat);
            });

            $(elem).removeClass('clicked');
        });


    }
});
