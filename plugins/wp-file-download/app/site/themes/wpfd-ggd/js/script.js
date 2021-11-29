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
    var sourcefile = $("#wpfd-template-ggd-box").html();
    var gdd_hash = window.location.hash;
    var ggd_cParents = {};
    var ggd_tree = $('.wpfd-foldertree-ggd');
    var ggd_root_cat = $('.wpfd-content-ggd').data('category');
    if (window.wpfdAjax === undefined) {
        window.wpfdAjax = {};
    }
    window.wpfdAjax[ggd_root_cat] = {category: null, file: null};
    $(".wpfd-content-ggd").each(function () {
        var ggd_topCat = $(this).data('category');
        if (ggd_topCat == 'all_0') {
            ggd_cParents[ggd_topCat] = {parent: 0, term_id: 0, name: $(this).find("h2").text()};
        } else {
            ggd_cParents[ggd_topCat] = {parent: 0, term_id: ggd_topCat, name: $(this).find("h2").text()};
        }

        $(this).find(".wpfdcategory.catlink").each(function () {
            var tempidCat = $(this).data('idcat');
            ggd_cParents[tempidCat] = {parent: ggd_topCat, term_id: tempidCat, name: $(this).text()};
        });
        initInputSelected(ggd_topCat);
        initDownloadSelected(ggd_topCat);
    });

    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytes.toString().toLowerCase() === 'n/a' ? bytes : bytesToSize(bytes);
    });

    initClickFile();

    function ggd_initClick() {
        $('.wpfd-content-ggd .catlink').unbind('click').click(function (e) {
            e.preventDefault();
            load($(this).parents('.wpfd-content-ggd').data('category'), $(this).data('idcat'));
        });
    }

    function initInputSelected(sc) {
        $(document).on('change', ".wpfd-content-ggd.wpfd-content-multi[data-category=" + sc + "] input.cbox_file_download", function (e) {
            e.stopPropagation();
            var rootCat = ".wpfd-content-ggd.wpfd-content-multi[data-category=" + sc + "]";
            var selectedFiles = $(rootCat + " input.cbox_file_download:checked");
            var filesId = [];
            if (selectedFiles.length) {
                selectedFiles.each(function (index, file) {
                    filesId.push($(file).data('id'));
                });
            }
            if (filesId.length > 0) {
                $(rootCat + " .wpfdSelectedFiles").remove();
                $('<input type="hidden" class="wpfdSelectedFiles" value="' + filesId.join(',') + '" />')
                    .insertAfter($(rootCat).find(" #current_category_slug_" + sc));
                hideDownloadAllBtn(sc, true);
                $(rootCat + " .ggd-download-selected").remove();
                var downloadSelectedBtn = $('<a href="javascript:void(0);" class="ggd-download-selected" style="display: block;">' + wpfdparams.translates.download_selected + '<i class="zmdi zmdi-check-all wpfd-download-category"></i></a>');
                downloadSelectedBtn.insertAfter($(rootCat).find(" #current_category_slug_" + sc));
            } else {
                $(rootCat + " .wpfdSelectedFiles").remove();
                $(rootCat + " .ggd-download-selected").remove();
                hideDownloadAllBtn(sc, false);
            }
            gdd_init_pagination(rootCat.next(".wpfd-pagination"));
        });
    }

    function hideDownloadAllBtn(sc, hide) {
        var rootCat = ".wpfd-content-ggd.wpfd-content-multi[data-category=" + sc + "]";
        var downloadCatButton = $(rootCat + " .ggd-download-category");
        if (downloadCatButton.length === 0 || downloadCatButton.hasClass('display-download-category')) {
            return;
        }
        if (hide) {
            $(rootCat + " .ggd-download-category").hide();
        } else {
            $(rootCat + " .ggd-download-category").show();
        }
    }

    function initDownloadSelected(sc) {
        var rootCat = ".wpfd-content-ggd.wpfd-content-multi[data-category=" + sc + "]";
        $(document).on('click', rootCat + ' .ggd-download-selected', function () {
            if ($(rootCat).find('.wpfdSelectedFiles').length > 0) {
                var current_category = $(rootCat).find('#current_category_' + sc).val();
                var category_name = $(rootCat).find('#current_category_slug_' + sc).val();
                var selectedFilesId = $(rootCat).find('.wpfdSelectedFiles').val();
                $.ajax({
                    url: wpfdparams.wpfdajaxurl + "?action=wpfd&task=files.zipSeletedFiles&filesId=" + selectedFilesId + "&wpfd_category_id=" + current_category,
                    dataType: "json",
                }).done(function (results) {
                    if (results.success) {
                        var hash = results.data.hash;
                        window.location.href = wpfdparams.wpfdajaxurl + "?action=wpfd&task=files.downloadZipedFile&hash=" + hash + "&wpfd_category_id=" + current_category + "&wpfd_category_name=" + category_name;
                    } else {
                        alert(results.data.message);
                    }
                })
            }
        });
    }
    ggd_initClick();


    gdd_hash = gdd_hash.replace('#', '');
    if (gdd_hash !== '') {
        var hasha = gdd_hash.split('-');
        var re = new RegExp("^(p[0-9]+)$");
        var page = null;
        var stringpage = hasha.pop();

        if (re.test(stringpage)) {
            page = stringpage.replace('p', '');
        }

        var hash_category_id = hasha[1];
        var hash_sourcecat = hasha[0];

        if (parseInt(hash_category_id) > 0 || hash_category_id === 'all_0') {
            if (hash_category_id == 'all_0') {
                hash_category_id = 0;
            }
            setTimeout(function () {
                load(hash_sourcecat, hash_category_id, page);
            }, 100)
        }
    }


    function initClickFile() {
        $('.wpfd-content .wpfd-file-link').unbind('click').click(function (e) {
            var atthref = $(this).attr('href');
            if (atthref !== '#') {
                return;
            }
            e.preventDefault();
            var fileid = $(this).data('id');
            var categoryid = $(this).data('category_id');
            $.ajax({
                url: wpfdparams.wpfdajaxurl + "task=file.display&view=file&id=" + fileid + "&categoryid=" + categoryid + "&rootcat=" + ggd_root_cat,
                dataType: "json",
                beforeSend: function() {
                    // setting a timeout
                    if($('body').has('wpfd-ggd-box-loader') !== true) {
                        $('body').append('<div class="wpfd-ggd-box-loader"></div>');
                    }
                }
            }).done(function (file) {
                var template = Handlebars.compile(sourcefile);
                var html = template(file);
                var box = $("#wpfd-ggd-box");
                $('.wpfd-ggd-box-loader').each(function () {
                    $(this).remove();
                });
                if (box.length === 0) {
                    $('body').append('<div id="wpfd-ggd-box" style="display: none;"></div>');
                    box = $("#wpfd-ggd-box");
                }
                box.empty();
                box.prepend(html);
                box.click(function (e) {
                    if ($(e.target).is('#wpfd-ggd-box')) {
                        box.hide();
                    }
                    $('#wpfd-ggd-box').unbind('click.box').bind('click.box', function (e) {
                        if ($(e.target).is('#wpfd-ggd-box')) {
                            box.hide();
                        }
                    });
                });
                $('#wpfd-ggd-box .wpfd-close').click(function () {
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

                $('body.elementor-default #wpfd-ggd-box a.wpfd_downloadlink').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var link = $(this).attr('href');
                    window.location.href = link;
                });
            });
        });
    }

    function load(sourcecat, catid, page) {
        $(document).trigger('wpfd:category-loading');
        var pathname = window.location.href.replace(window.location.hash, '');
        var container = $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]");
        container.find('#current_category_' + sourcecat).val(catid);
        container.next('.wpfd-pagination').remove();
        $(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-container-ggd").empty();
        $(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-container-ggd").html($('#wpfd-loading-wrap').html());

        //Get categories
        var oldCategoryAjax = window.wpfdAjax[ggd_root_cat].category;
        if (oldCategoryAjax !== null) {
            oldCategoryAjax.abort();
        }
        window.wpfdAjax[ggd_root_cat].category = $.ajax({
            url: wpfdparams.wpfdajaxurl + "task=categories.display&view=categories&id=" + catid + "&top=" + sourcecat,
            dataType: "json"
        }).done(function (categories) {

            if (page !== null && page !== undefined) {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + catid + '-' + categories.category.slug + '-p' + page);
            } else {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + catid + '-' + categories.category.slug);
            }

            container.find('#current_category_slug_' + sourcecat).val(categories.category.slug);
            var sourcecategories = $(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-template-categories").html();
            if (sourcecategories) {
                var template = Handlebars.compile(sourcecategories);
                var html = template(categories);
                $(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-container-ggd").prepend(html);
            }
            if (categories.category.breadcrumbs !== undefined) {
                $(".wpfd-content-ggd[data-category=" + sourcecat + "] .breadcrumbs").html(categories.category.breadcrumbs);
            }
            for (var i = 0; i < categories.categories.length; i++) {
                ggd_cParents[categories.categories[i].term_id] = categories.categories[i];
            }

            ggd_breadcrum(sourcecat, catid, categories.category);
            ggd_initClick();
            if (ggd_tree.length) {
                var currentTree = container.find('.wpfd-foldertree-ggd');
                currentTree.find('li').removeClass('selected');
                currentTree.find('i.md').removeClass('md-folder-open').addClass("md-folder");

                currentTree.jaofiletree('open', catid, currentTree);

                var el = currentTree.find('a[data-file="' + catid + '"]').parent();
                el.find(' > i.md').removeClass("md-folder").addClass("md-folder-open");

                if (!el.hasClass('selected')) {
                    el.addClass('selected');
                }
                var ps = currentTree.find('.icon-open-close');

                $.each(ps.get().reverse(), function (i, p) {
                    if (typeof $(p).data() !== 'undefined' && $(p).data('id') == Number(hash_category_id)) {
                        hash_category_id = $(p).data('parent_id');
                        $(p).click();
                    }
                });

            }

        });
        var ordering = $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]").find('#current_ordering_' + sourcecat).val();
        var orderingDirection = $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]").find('#current_ordering_direction_' + sourcecat).val();
        var params = $.param({
            task: 'files.display',
            view: 'files',
            id: catid,
            rootcat: sourcecat,
            page: page,
            orderCol: ordering,
            orderDir: orderingDirection
        });
        //Get files
        var oldFileAjax = window.wpfdAjax[ggd_root_cat].file;
        if (oldFileAjax !== null) {
            oldFileAjax.abort();
        }
        window.wpfdAjax[ggd_root_cat].file = $.ajax({
            url: wpfdparams.wpfdajaxurl + params,
            dataType: "json"
        }).done(function (content) {

            if (content.files.length) {
                $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]  .ggd-download-category").removeClass("display-download-category");
            } else {
                $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]  .ggd-download-category").addClass("display-download-category");
            }

            $(".wpfd-content-ggd[data-category=" + sourcecat + "]").after(content.pagination);
            delete content.pagination;
            var sourcefiles = $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "]  .wpfd-template-files").html();
            var template = Handlebars.compile(sourcefiles);
            var html = template(content);
            html = $('<textarea/>').html(html).val();
            $(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-container-ggd").append(html);
            initClickFile();


            gdd_init_pagination($('.wpfd-content-ggd[data-category=' + sourcecat + '] + .wpfd-pagination'));

            wpfd_remove_loading($(".wpfd-content-ggd[data-category=" + sourcecat + "] .wpfd-container-ggd"));
          $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "] .wpfdSelectedFiles").remove();
          $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + sourcecat + "] .ggd-download-selected").remove();
          hideDownloadAllBtn(sourcecat, false);
        });

        $(document).trigger('wpfd:category-loaded');
    }

    function ggd_breadcrum(ggd_topCat, catid, category) {
        var links = [];
        var current_Cat = ggd_cParents[catid];
        if (!current_Cat) {
            $(".wpfd-content-ggd[data-category=" + ggd_topCat + "] .ggd-download-category").attr('href', category.linkdownload_cat);
            return false;
        }
        links.unshift(current_Cat);
        if (current_Cat.parent !== 0) {
            while (ggd_cParents[current_Cat.parent]) {
                current_Cat = ggd_cParents[current_Cat.parent];
                links.unshift(current_Cat);
            }
        }

        var html = '';
        for (var i = 0; i < links.length; i++) {
            if (i < links.length - 1) {
                html += '<li><a class="catlink" data-idcat="' + links[i].term_id + '" href="javascript:void(0)">';
                html += links[i].name + '</a><span class="divider"> &gt; </span></li>';
            } else {
                html += '<li><span>' + links[i].name + '</span></li>';
            }
        }
        $(".wpfd-content-ggd[data-category=" + ggd_topCat + "] .wpfd-breadcrumbs-ggd li").remove();
        $(".wpfd-content-ggd[data-category=" + ggd_topCat + "] .wpfd-breadcrumbs-ggd").append(html);

        $(".wpfd-content-ggd[data-category=" + ggd_topCat + "] .catlink").click(function (e) {
            e.preventDefault();
            load(ggd_topCat, $(this).data('idcat'));
            initClickFile();
        });
        $(".wpfd-content-ggd[data-category=" + ggd_topCat + "] .ggd-download-category").attr('href', category.linkdownload_cat);
    }

    if (ggd_tree.length) {
        ggd_tree.each(function () {
            var ggd_topCat = $(this).parents('.wpfd-content-ggd.wpfd-content-multi').data('category');
            $(this).jaofiletree({
                script: wpfdparams.wpfdajaxurl + 'task=categories.getCats',
                usecheckboxes: false,
                root: ggd_topCat,
                showroot: ggd_cParents[ggd_topCat].name,
                onclick: function (elem, file) {
                    var ggd_topCat = $(elem).parents('.wpfd-content-ggd.wpfd-content-multi').data('category');
                    if (ggd_topCat !== file) {
                        $('.directory', $(elem).parents('.wpfd-content-ggd.wpfd-content-multi')).each(function() {
                            if (!$(this).hasClass('selected') && $(this).find('> ul > li').length === 0) {
                                $(this).removeClass('expanded');
                            }
                        });

                        $(elem).parents('.directory').each(function () {
                            var $this = $(this);
                            var category = $this.find(' > a');
                            var parent = $this.find('.icon-open-close');
                            if (parent.length > 0) {
                                if (typeof ggd_cParents[category.data('file')] === 'undefined') {
                                    ggd_cParents[category.data('file')] = {
                                        parent: parent.data('parent_id'),
                                        term_id: category.data('file'),
                                        name: category.text()
                                    };
                                }
                            }
                        });

                    }

                    load(ggd_topCat, file);
                }
            });
        })
    }

    $('.wpfd-content-ggd + .wpfd-pagination').each(function (index, elm) {
        var $this = $(elm);
        gdd_init_pagination($this);
    });

    function gdd_init_pagination($this) {

        var number = $this.find(':not(.current)');

        var wrap = $this.prev('.wpfd-content-ggd');

        var sourcecat = wrap.data('category');
        var current_category = wrap.find('#current_category_' + sourcecat).val();

        number.unbind('click').bind('click', function () {
            var page_number = $(this).attr('data-page');
            var current_sourcecat = $(this).attr('data-sourcecat');
            var wrap = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]");
            var current_category = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]").find('#current_category_' + current_sourcecat).val();
            if (typeof page_number !== 'undefined') {
                var pathname = window.location.href.replace(window.location.hash, '');
                var category = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]").find('#current_category_' + current_sourcecat).val();
                var category_slug = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]").find('#current_category_slug_' + current_sourcecat).val();
                var ordering = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]").find('#current_ordering_' + current_sourcecat).val();
                var orderingDirection = $(".wpfd-content-ggd[data-category=" + current_sourcecat + "]").find('#current_ordering_direction_' + current_sourcecat).val();

                window.history.pushState('', document.title, pathname + '#' + current_sourcecat + '-' + category + '-' + category_slug + '-p' + page_number);

                $(".wpfd-content-ggd[data-category=" + current_sourcecat + "] .wpfd-container-ggd .wpfd_list").remove();
                $(".wpfd-content-ggd[data-category=" + current_sourcecat + "] .wpfd-container-ggd").append($('#wpfd-loading-wrap').html());

                var params = $.param({
                    task: 'files.display',
                    view: 'files',
                    id: current_category,
                    rootcat: current_sourcecat,
                    page: page_number,
                    orderCol: ordering,
                    orderDir: orderingDirection
                });

                //Get files
                $.ajax({
                    url: wpfdparams.wpfdajaxurl + params,
                    dataType: "json",
                    beforeSend: function () {
                        console.log(current_sourcecat);
                        $('html, body').animate({scrollTop: $(".wpfd-content[data-category=" + current_sourcecat + "]").offset().top}, 'fast');
                    }
                }).done(function (content) {
                    delete content.category;
                    wrap.next('.wpfd-pagination').remove();
                    wrap.after(content.pagination);
                    delete content.pagination;
                    var sourcefiles = $(".wpfd-content-ggd.wpfd-content-multi[data-category=" + current_sourcecat + "]  .wpfd-template-files").html();
                    var template = Handlebars.compile(sourcefiles);
                    var html = template(content);

                    $(".wpfd-content-ggd[data-category=" + current_sourcecat + "] .wpfd-container-ggd").append(html);
                    initClickFile();

                    gdd_init_pagination(wrap.next('.wpfd-pagination'));
                    wpfd_remove_loading($(".wpfd-content-ggd[data-category=" + current_sourcecat + "] .wpfd-container-ggd"));
                });
            }
        });

    }

    function wpfd_ggd_container_with_foldertree() {
        $('.wpfd-content-ggd .wpfd-container').each(function () {
            if($(this).children('.with_foldertree').length > 0) {
                $(this).addClass('wpfd_ggdcontainer_foldertree');
            } else {
                if($(this).hasClass('wpfd_ggdcontainer_foldertree')) {
                    $(this).removeClass('wpfd_ggdcontainer_foldertree');
                }
            }
        });

        //parent-content
        $('.wpfd-content-ggd').each(function () {
            if($(this).children().has('.wpfd-foldertree').length > 0) {
                $(this).addClass('wpfdcontent_ggd_folder_tree');
                } else {
                    if($(this).hasClass('wpfdcontent_ggd_folder_tree')) {
                        $(this).removeClass('wpfdcontent_ggd_folder_tree');
                    }
                }
        });
    }

    wpfd_ggd_container_with_foldertree();

});
