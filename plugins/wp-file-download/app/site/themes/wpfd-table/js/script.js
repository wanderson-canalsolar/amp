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


    var table_tree = $('.wpfd-foldertree-table');
    var table_hash = window.location.hash;
    var table_root_cat = $('.wpfd-content-table').data('category');
    var table_cParents = {};
    if (window.wpfdAjax === undefined) {
        window.wpfdAjax = {};
    }
    window.wpfdAjax[table_root_cat] = {category: null, file: null};
    $(".wpfd-content-table").each(function () {
        var table_topCat = $(this).data('category');
        var topCatName = $(this).find('.head-category-table li:first-child').text();
        var currentCatName = $(this).find("h2").text();
        if (currentCatName !== '') {
            topCatName = currentCatName;
        }
        if (table_topCat == 'all_0') {
            table_cParents[table_topCat] = {parent: 0, term_id: 0, name: topCatName};
        } else {
            table_cParents[table_topCat] = {parent: 0, term_id: table_topCat, name: topCatName};
        }
        $(this).find(".wpfdcategory.catlink").each(function () {
            var tempidCat = $(this).data('idcat');
            table_cParents[tempidCat] = {parent: table_topCat, term_id: tempidCat, name: $(this).text()};
        });
        initInputSelected(table_topCat);
        initDownloadSelected(table_topCat);
    });

    //load media tables
    $('.wpfd-content .mediaTable').mediaTable();

    Handlebars.registerHelper('bytesToSize', function (bytes) {
        return bytes.toString().toLowerCase() === 'n/a' ? bytes : bytesToSize(parseInt(bytes));
    });

    function table_initClick() {
        $('.wpfd-content-table .catlink').unbind('click').click(function (e) {
            e.preventDefault();
            table_load($(this).parents('.wpfd-content-table').data('category'), $(this).data('idcat'));
        });
    }

    function initInputSelected(sc) {
        $(document).on('change', ".wpfd-content-table.wpfd-content-multi[data-category=" + sc + "] input.cbox_file_download", function () {
            var rootCat = ".wpfd-content-table.wpfd-content-multi[data-category=" + sc + "]";
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
                $(rootCat + " .table-download-selected").remove();
                var downloadSelectedBtn = $('<a href="javascript:void(0);" class="table-download-selected" style="display: block;">' + wpfdparams.translates.download_selected + '<i class="zmdi zmdi-check-all wpfd-download-category"></i></a>');
                downloadSelectedBtn.insertAfter($(rootCat).find("#current_category_slug_" + sc));
            } else {
                $(rootCat + " .wpfdSelectedFiles").remove();
                $(rootCat + " .table-download-selected").remove();
                hideDownloadAllBtn(sc, false);
            }
        });
    }

    function hideDownloadAllBtn(sc, hide) {
        var rootCat = ".wpfd-content-table.wpfd-content-multi[data-category=" + sc + "]";
        var downloadCatButton = $(rootCat + " .table-download-category");
        if (downloadCatButton.length === 0 || downloadCatButton.hasClass('display-download-category')) {
            return;
        }
        if (hide) {
            $(rootCat + " .table-download-category").hide();
        } else {
            $(rootCat + " .table-download-category").show();
        }
    }

    function initDownloadSelected(sc) {
        var rootCat = ".wpfd-content-table.wpfd-content-multi[data-category=" + sc + "]";
        $(document).on('click', rootCat + ' .table-download-selected', function () {
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
    table_initClick();

    table_hash = table_hash.replace('#', '');
    if (table_hash !== '') {
        var hasha = table_hash.split('-');
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
                table_load(hash_sourcecat, hash_category_id, page);
            }, 100);

        }
    }


    function table_load(sourcecat, catid, page) {
        $(document).trigger('wpfd:category-loading');
        var pathname = window.location.href.replace(window.location.hash, '');
        var container = $(".wpfd-content-table.wpfd-content-multi[data-category=" + sourcecat + "]");
        container.find('#current_category_' + sourcecat).val(catid);
        container.next('.wpfd-pagination').remove();

        $(".wpfd-content-multi[data-category=" + sourcecat + "] table tbody").empty();
        wpfd_remove_loading($(".wpfd-content-multi"));
        $(".wpfd-content-multi[data-category=" + sourcecat + "] table").after($('#wpfd-loading-wrap').html());
        $(".wpfd-content-multi[data-category=" + sourcecat + "] .wpfd-categories").empty();
        //Get categories
        var oldCategoryAjax = window.wpfdAjax[table_root_cat].category;
        if (oldCategoryAjax !== null) {
            oldCategoryAjax.abort();
        }
        window.wpfdAjax[table_root_cat].category = $.ajax({
            url: wpfdparams.wpfdajaxurl + "task=categories.display&view=categories&id=" + catid + "&top=" + sourcecat,
            dataType: "json"
        }).done(function (categories) {

            if (page !== null && page !== undefined) {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + catid + '-' + categories.category.slug + '-p' + page);
            } else {
                window.history.pushState('', document.title, pathname + '#' + sourcecat + '-' + catid + '-' + categories.category.slug);
            }

            container.find('#current_category_slug_' + sourcecat).val(categories.category.slug);
            var tpltable_sourcecategories = container.parents().find("#wpfd-template-table-categories-" + sourcecat).html();
            if (tpltable_sourcecategories) {
                var template = Handlebars.compile(tpltable_sourcecategories);
                var html = template(categories);
                $(".wpfd-content-multi[data-category=" + sourcecat + "] .wpfd-categories").replaceWith(html);
            }
            if (categories.category.breadcrumbs !== undefined) {
                $(".wpfd-content-multi[data-category=" + sourcecat + "] .breadcrumbs").html(categories.category.breadcrumbs);
            }
            if (table_tree.length) {
                var currentTree = container.find('.wpfd-foldertree-table');
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

            var ordering = $(".wpfd-content-multi[data-category=" + sourcecat + "]").find('#current_ordering_' + sourcecat).val();
            var orderingDirection = $(".wpfd-content-multi[data-category=" + sourcecat + "]").find('#current_ordering_direction_' + sourcecat).val();
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
            var oldCategoryAjax = window.wpfdAjax[table_root_cat].file;
            if (oldCategoryAjax !== null) {
                oldCategoryAjax.abort();
            }
            window.wpfdAjax[table_root_cat].file = $.ajax({
                url: wpfdparams.wpfdajaxurl + params,
                dataType: "json"
            }).done(function (content) {
                // $.extend(content,categories);

                if (content.files.length) {
                    container.find(".table-download-category").removeClass("display-download-category");
                } else {
                    container.find(".table-download-category").addClass("display-download-category");
                }
                $(".wpfd-content-multi[data-category=" + sourcecat + "]").after(content.pagination);
                delete content.pagination;

                var tpltable_source = container.parents().find("#wpfd-template-table-" + sourcecat).html();
                var template_table = Handlebars.compile(tpltable_source);
                var html = template_table(content);
                //html = $('<textarea/>').html(html).val();
                $(".wpfd-content-multi[data-category=" + sourcecat + "] table tbody").append(html);
                $(".wpfd-content-multi[data-category=" + sourcecat + "] table tbody").trigger('change');
                $(".wpfd-content-multi[data-category=" + sourcecat + "] .mediaTableMenu").find('input').trigger('change');

                for (var i = 0; i < categories.categories.length; i++) {
                    table_cParents[categories.categories[i].term_id] = categories.categories[i];
                }

                table_breadcrum(sourcecat, catid, categories.category);

                table_initClick();
                if (typeof wpfdColorboxInit !== 'undefined') {
                    wpfdColorboxInit();
                }
                wpfdTrackDownload();

                table_init_pagination($('.wpfd-content-table[data-category=' + sourcecat + '] + .wpfd-pagination'));
                wpfd_remove_loading($(".wpfd-content-multi"));
                $(".wpfd-content-table.wpfd-content-multi[data-category=" + sourcecat + "] .wpfdSelectedFiles").remove();
                $(".wpfd-content-table.wpfd-content-multi[data-category=" + sourcecat + "] .table-download-selected").remove();
                hideDownloadAllBtn(sourcecat, false);
            });

        });

        $(document).trigger('wpfd:category-loaded');
    }

    function table_breadcrum(sourcecat, catid, category) {
        var links = [];
        var current_Cat = table_cParents[catid];
        if (!current_Cat) {
            $(".wpfd-content-table[data-category=" + sourcecat + "] .table-download-category").attr('href', category.linkdownload_cat);
            return false;
        }

        links.unshift(current_Cat);

        if (current_Cat.parent !== 0) {
            while (table_cParents[current_Cat.parent]) {
                current_Cat = table_cParents[current_Cat.parent];
                links.unshift(current_Cat);
            }
        }

        var html = '';
        for (var i = 0; i < links.length; i++) {
            if (i < links.length - 1) {
                html += '<li><a class="catlink" data-idcat="' + links[i].term_id + '" href="javascript:void(0)">' + links[i].name + '</a><span class="divider"> &gt; </span></li>';
            } else {
                html += '<li><span>' + links[i].name + '</span></li>';
            }
        }
        $(".wpfd-content-table[data-category=" + sourcecat + "] .wpfd-breadcrumbs-table li").remove();
        $(".wpfd-content-table[data-category=" + sourcecat + "] .wpfd-breadcrumbs-table").append(html);
        $(".wpfd-content-table[data-category=" + sourcecat + "] .table-download-category").attr('href', category.linkdownload_cat);
    }

    if (table_tree.length) {
        table_tree.each(function () {
            var table_topCat = $(this).parents('.wpfd-content-table.wpfd-content-multi').data('category');
            $(this).jaofiletree({
                script: wpfdparams.wpfdajaxurl + 'task=categories.getCats',
                usecheckboxes: false,
                root: table_topCat,
                showroot: table_cParents[table_topCat].name,
                onclick: function (elem, file) {

                    var table_topCat = $(elem).parents('.wpfd-content-table.wpfd-content-multi').data('category');
                    if (table_topCat !== file) {
                        $('.directory', $(elem).parents('.wpfd-content-table.wpfd-content-multi')).each(function() {
                            if (!$(this).hasClass('selected') && $(this).find('> ul > li').length === 0) {
                                $(this).removeClass('expanded');
                            }
                        });
                        $(elem).parents('.directory').each(function () {
                            var $this = $(this);
                            var category = $this.find(' > a');
                            var parent = $this.find('.icon-open-close');
                            if (parent.length > 0) {
                                if (typeof table_cParents[category.data('file')] === 'undefined') {
                                    table_cParents[category.data('file')] = {
                                        parent: parent.data('parent_id'),
                                        term_id: category.data('file'),
                                        name: category.text()
                                    };
                                }
                            }
                        });

                    }

                    table_load(table_topCat, file);
                }
            });
        })
    }


    $('.wpfd-content-table + .wpfd-pagination').each(function (index, elm) {
        var $this = $(elm);
        table_init_pagination($this);
    });

    function table_init_pagination($this) {

        var number = $this.find('a:not(.current)');

        var wrap = $this.prev('.wpfd-content-table');

        var sourcecat = wrap.data('category');
        var current_category = wrap.find('#current_category_' + sourcecat).val();

        number.unbind('click').bind('click', function () {
            var page_number = $(this).attr('data-page');
            var current_sourcecat = $(this).attr('data-sourcecat');
            var wrap = $(".wpfd-content-multi[data-category=" + current_sourcecat + "]");
            var current_category = wrap.find('#current_category_' + sourcecat).val();
            if (typeof page_number !== 'undefined') {
                var pathname = window.location.href.replace(window.location.hash, '');
                var category = $(".wpfd-content-multi[data-category=" + current_sourcecat + "]").find('#current_category_' + current_sourcecat).val();
                var category_slug = $(".wpfd-content-multi[data-category=" + current_sourcecat + "]").find('#current_category_slug_' + current_sourcecat).val();
                var ordering = $(".wpfd-content-multi[data-category=" + current_sourcecat + "]").find('#current_ordering_' + current_sourcecat).val();
                var orderingDirection = $(".wpfd-content-multi[data-category=" + current_sourcecat + "]").find('#current_ordering_direction_' + current_sourcecat).val();

                window.history.pushState('', document.title, pathname + '#' + current_sourcecat + '-' + category + '-' + category_slug + '-p' + page_number);

                $(".wpfd-content-multi[data-category=" + current_sourcecat + "] table tbody tr:not(.topheader)").remove();
                $(".wpfd-content-multi[data-category=" + current_sourcecat + "] table").after($('#wpfd-loading-wrap').html());
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
                        $('html, body').animate({scrollTop: wrap.offset().top}, 'fast');
                    }
                }).done(function (content) {

                    delete content.category;
                    wrap.next('.wpfd-pagination').remove();
                    wrap.after(content.pagination);
                    delete content.pagination;
                    var tpltable_source = wrap.parents().find("#wpfd-template-table-" + current_sourcecat).html();
                    var template_table = Handlebars.compile(tpltable_source);
                    var html = template_table(content);
                    $(".wpfd-content-multi[data-category=" + current_sourcecat + "] table tbody").append(html);
                    $(".wpfd-content-multi[data-category=" + current_sourcecat + "] table tbody").trigger('change');
                    $(".wpfd-content-multi[data-category=" + current_sourcecat + "] .mediaTableMenu").find('input').trigger('change');

                    if (typeof wpfdColorboxInit !== 'undefined') {
                        wpfdColorboxInit();
                    }
                    table_init_pagination(wrap.next('.wpfd-pagination'));
                    wpfd_remove_loading($(".wpfd-content-multi"));
                });
            }

        });
    }

    function optimize_Show_fields() {
        if($('.wpfd-content-table .wpfd-container-table').width() < 600) {
            $('.mediaTableMenu li').each(function () {
                if($(this).find('label').text() == 'Description') {
                    $(this).find('input').prop('checked',false);
                }
            });
            $('.wpfd-table .file_desc').hide();
        }
    }

    function wpfd_Table_with_foldertree() {
        //parent-content
        $('.wpfd-content-table').each(function () {
            if($(this).children().has('.wpfd-foldertree').length > 0) {
                $(this).addClass('wpfdcontent_table_folder_tree');
            } else {
                if($(this).hasClass('wpfdcontent_table_folder_tree')) {
                    $(this).removeClass('wpfdcontent_table_folder_tree');
                }
            }
        });
    }

    optimize_Show_fields();

    wpfd_Table_with_foldertree();


});
