function initSorting() {
    jQuery('.orderingCol').click(function (e) {
        e.preventDefault();
        var ordering = jQuery(this).data('ordering');
        var direction = jQuery(this).data('direction');
        ajaxSearch(ordering, direction);
    });

    jQuery(".list-results #limit").change(function (e) {
        e.preventDefault();
        ajaxSearch();
        return false;
    });
}

function initTags() {
    var $ = jQuery;
    if ($(".input_tags").length > 0) {
        var taglist = $(".input_tags").val();
        var tagArr = taglist.split(",");
        $('.chk_ftags').each(function () {
            var temp = $(this).val();
            if (tagArr.indexOf(temp) > -1) {
                $(this).prop('checked', true);
            }
        });
    }
    if ($("#filter_catid").length > 0) {
        catChange("filter_catid");
    }
}
function showDefautTags() {
    var $ = jQuery;
    if (typeof defaultAllTags !== 'undefined' && defaultAllTags.length > 0) {
        $('.chk-tags-filtering ul').empty();
        defaultAllTags.sort(function (a, b) {
            if (a > b) {
                return 1
            }
            if (a < b) {
                return -1
            }
            return 0;
        });
        var checklable1 = $('<label class="labletags">' + wpfdvars.msg_filter_by_tags + '</label>');
        $('.chk-tags-filtering ul').append(checklable1);
        $.each(defaultAllTags, function (index, tag) {
            var key = 'wpfd'+tag.replace(/-/g, '');
            var element = $('<li class="tags-item"><span> '+ tagsLabel[key] +' </span> <input title="" type="checkbox" name="chk_ftags[]" onclick="fillInputTags();" class="ju-input chk_ftags" id="ftags' + index + '" value="' + tag + '"></li>');
            $('.chk-tags-filtering ul').append(element);
        });
        $(".input_tags").val("");
        selectdChecktags();
    }
}
function catChange(filterCat) {
    var $ = jQuery;
    var catId = $("#" + filterCat).val();

    if (catId === "") {
        showDefautTags();
        $('.chk_ftags').parent().show();
        return;
    }
    if ($('.chk-tags-filtering ul').length === 0) {
        return;
    }

    $.ajax({
        type: "GET",
        url: wpfdajaxurl + "task=search.getTagByCatId",
        data: {catId: catId}
    }).done(function (tags) {
        //var tags = JSON.parse(tags);
        if(tags === '0') {
            $('.chk-tags-filtering ul').empty();
            var message = $('<li>' + wpfdvars.msg_no_tag_in_this_category_found + '</li>');
            $('.chk-tags-filtering ul').append(message);
            $(".input_tags").val("");
        } else {
            if (tags.success === true) {
                $('.chk-tags-filtering ul').empty();
                var checklable2 = $('<label class="labletags">' + wpfdvars.msg_filter_by_tags + '</label>');
                $('.chk-tags-filtering ul').append(checklable2);
                $.each(tags.tags, function (index, tag) {
                    var element = $('<li class="tags-item"><input title="" type="checkbox" name="chk_ftags[]" onclick="fillInputTags();" class="ju-input chk_ftags" id="ftags' + index + '" value="' + tag['slug'] + '"> <span>' + tag['name'].replace(/-/g, ' ') + '</span></li>');
                    $('.chk-tags-filtering ul').append(element);
                });
                $(".input_tags").val("");
                selectdChecktags();
            } else {
                $('.chk-tags-filtering ul').empty();
                var message = $('<li>' + tags.message + '</li>');
                $('.chk-tags-filtering ul').append(message);
                $(".input_tags").val("");
            }
        }
    });
}

function fillInputTags() {
    var tagVal = [];
    jQuery('.chk_ftags').each(function () {
        if (this.checked && jQuery(this).is(":visible")) {
            tagVal.push(jQuery(this).val());
        }
    });
    if (tagVal.length > 0) {
        jQuery(".input_tags").val(tagVal.join(","));
    } else {
        jQuery(".input_tags").val("");
    }
}

function getSearchParams(k) {
    var p = {};
    location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (s, k, v) {
        p[k] = v
    });
    return k ? p[k] : p;
}

function ajaxSearch(ordering, direction) {
    var $ = jQuery;
    var sform = $("#adminForm");
    // get the form data
    var formData = {
        'q': $(sform).find('input[name=q]').val(),
        'catid': $(sform).find('[name=catid]').val(),
        'exclude': $(sform).find('[name=exclude]').val(),
        'theme': $(sform).find('[name=theme]').val(),
        'limit': $(sform).find('[name=limit]').val(),
        'ftags': $(sform).find('input[name=ftags]').val(),
        'cfrom': $(sform).find('input[name=cfrom]').val(),
        'cto': $(sform).find('input[name=cto]').val(),
        'ufrom': $(sform).find('input[name=ufrom]').val(),
        'uto': $(sform).find('input[name=uto]').val()
    };

    formData = cleanObj(formData);

    if (jQuery.isEmptyObject(formData) ||
        (typeof (formData.q) === 'undefined' &&
            typeof (formData.ftags) === 'undefined' &&
            typeof (formData.cfrom) === 'undefined' &&
            typeof (formData.cto) === 'undefined' &&
            typeof (formData.ufrom) === 'undefined' &&
            typeof (formData.uto) === 'undefined' &&
            typeof (formData.catid) !== 'undefined' &&
            parseInt(formData.catid) === 0)) {
        $("#txtfilename").focus();
        return false;
    }
    formData = $.extend({'limit': $(sform).find('select[name=limit]').val()}, formData);
    if (typeof ordering !== 'undefined') formData.ordering = ordering;
    if (typeof direction !== 'undefined') formData.dir = direction;
    //pagination

    var filter_url = jQuery.param(formData);
    var currentUrl = window.location.search;
    var pushUrl;
    if (typeof URLSearchParams !== 'undefined') {
        var currentFilters = new URLSearchParams(currentUrl.substring(1));
        Object.keys(formData).forEach(function(key) {
            if (currentFilters.has(key)) {
                currentFilters.delete(key);
            }
        });
        if (currentUrl.substring(1) === '?') {
            pushUrl = currentFilters.toString() + '&' + filter_url;
        } else {
            pushUrl = currentFilters.toString() + '?' + filter_url;
        }
        window.history.pushState(formData, "", pushUrl);
    }


    $.ajax({
        method: "POST",
        url: wpfdajaxurl + "task=search.display",
        data: formData,
        beforeSend: function () {
            $("#wpfd-results").html('');
            $("#wpfd-results").prepend($("#loader").clone().show());
        },
        success: function (result) {
            $("#wpfd-results").html(result);
            initSorting();
            if (typeof wpfdColorboxInit !== 'undefined') {
                wpfdColorboxInit();
            }
        }
    });
}

function openSearchfilter(evt, searchName) {
    evt.preventDefault();

    var $ = jQuery;
    var $this = $(evt.target);

    $this.parent().find('.tablinks').removeClass('active');
    $this.addClass('active');

    $this.parent().parent().find('.wpfd_tabcontainer .wpfd_tabcontent').removeClass('active');
    $this.parent().parent().find('.wpfd_tabcontainer #' + searchName).addClass('active');

    return false;
}

function selectdChecktags() {
    var $ = jQuery;
    var captured = /ftags=([^&]+)/.exec(window.location.search);
    var tags = typeof captured !== "null" ? decodeURIComponent(captured).split(',') : false;
    jQuery('li.tags-item').on('click', function () {
        $(this).toggleClass("active");
        if ($(this).hasClass("active")) {
            $(this).children("input[type='checkbox']").prop('checked', true);
        } else {
            $(this).children("input[type='checkbox']").prop('checked', false);
        }
        var tagVal = [];
        jQuery(".chk_ftags").each(function () {
            if ($(this).prop("checked") == true) {
                tagVal.push($(this).val());
            }
        });
        if (tagVal.length > 0) {
            jQuery(".input_tags").val(tagVal.join(","));
        } else {
            jQuery(".input_tags").val("");
        }
    });

    //check load/reload url has tag(s) selected
    $('li.tags-item').each(function () {
        var currentname = $(this).find('input').val();
        if(tags.indexOf(currentname) > -1) {
            $(this).click();
        }
    });
}

function wpfdcancelSelectedCate() {
    var $, wpfdlCurrentselectedCate;
    $ = jQuery;
    wpfdlCurrentselectedCate = $(".categories-filtering .cate-lab");
    $(".cate-lab .cancel").unbind('click').on('click', function () {
        if (wpfdlCurrentselectedCate.hasClass('display-cate')) {
            wpfdlCurrentselectedCate.removeClass('display-cate');
        }
        wpfdlCurrentselectedCate.empty();
        wpfdlCurrentselectedCate.append("<label id='root-cate'>" + wpfdvars.msg_file_category + "</label>");
        $(".categories-filtering .wpfd-listCate #filter_catid").val('');
        if($(".categories-filtering .cate-item").hasClass("checked")) {
            $(".categories-filtering .cate-item").removeClass("checked");
        } else {
            if($(".categories-filtering .cate-item").hasClass("choosed")) {
                $(".categories-filtering .cate-item").removeClass("choosed");
            }
        }
        catChange("filter_catid");
    });
}

function wpfdshowCateReload() {
    var $, SelectedCateReloadCase, wpfddisplayCateReloadCase;
    $ = jQuery;
    SelectedCateReloadCase = $(".categories-filtering .cate-item.choosed label").text();
    wpfddisplayCateReloadCase = $(".categories-filtering .cate-lab");
    var selectedCatecontentReloadCase = "<label>" + SelectedCateReloadCase + "</label>";
    if($(".cate-item.choosed").length > 0) {
        wpfddisplayCateReloadCase.addClass('display-cate');
        wpfddisplayCateReloadCase.empty();
        wpfddisplayCateReloadCase.append(selectedCatecontentReloadCase);
        if(wpfddisplayCateReloadCase.text() !== null ) {
            wpfddisplayCateReloadCase.append('<a class="cancel"></a>');
        }
    }
    wpfdcancelSelectedCate();
}

function showCategory() {
    var $ = jQuery;
    $(".categories-filtering .cateicon").unbind('click').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        var $container = $this.parent();
        $('.wpfd-listCate', $container).toggle();
        $('li.cate-item', $container).unbind('click').on('click', function () {
            $('li.cate-item.checked', $container).removeClass("checked");
            $('li.cate-item', $container).removeClass("choosed");

            $(this).addClass("checked");
            $("#filter_catid", $container).val($(this).data('catid')).trigger("change");
            $('.wpfd-listCate', $container).hide();

            // Show selected category
            var wpfdSelectedCatename = $(".cate-item.checked label", $container).text();
            var wpfddisplayCate = $('.cate-lab', $container);
            if($('.showitems', $container).length > 0) {
                $('.show-selected-cate', $container).css("display", "");
            } else {
                var selectedCatecontent = "<label>" + wpfdSelectedCatename + "</label>";
                $('.show-selected-cate', $container).css("display", "block");
                if($('.cate-item.checked', $container).length === 1) {
                    wpfddisplayCate.addClass('display-cate');
                    wpfddisplayCate.empty();
                    wpfddisplayCate.append(selectedCatecontent);
                    if(wpfddisplayCate.text() !== null ) {
                        wpfddisplayCate.append('<a class="cancel"></a>');
                    }
                }
            }

            wpfdcancelSelectedCate();

            catChange("filter_catid");
        });

        $(document).mouseup(function(e) {
            if (!$(".categories-filtering > .ui-widget").is(e.target) // if the target of the click isn't the container...
                && !$(".categories-filtering .cateicon").is(e.target)
                && $(".categories-filtering > .ui-widget").has(e.target).length === 0) // ... nor a descendant of the container
            {
                $(".categories-filtering > .ui-widget").hide();
            }
        });
    });
}

function addPlaceholder() {
    var $ = jQuery;
    $(".tags-filtering .tagit-new input").attr("placeholder", wpfdvars.msg_search_box_placeholder);
}

//add class to search button
function addtoSearchbuttonbelow() {
    var $ = jQuery;
    var searchbox = $("#Tags").children();
    if (searchbox.hasClass("tags-filtering")) {
        $(".box-btngroup-below").addClass("searchboxClass");
    }
}

function parentFolderIcon() {
    var $ = jQuery;
    $("li.cate-item").each(function () {
        var count = $(this).find('span.child-cate').length;
        var prelevel = $(this).prev().attr("data-catlevel");
        var catelevel = $(this).attr("data-catlevel");
        if((count == 1 && catelevel > prelevel) || (count == 1 && prelevel == 9)) {
            $(this).prev().addClass("parent-cate");
        }
    });
}

function  noTagscase() {
    var $ =jQuery;
    if($("#Tags .no-tags").length > 0) {
        $("#Tags .no-tags").each(function () {
            $(this).parents('.box-search-filter').find('.box-btngroup-below').addClass("notags-case");
        });
    } else {
        $(".feature .box-btngroup-below").removeClass("notags-case");
    }
}

function initDateRangePicker(from_input, to_input) {
    var $ = jQuery;
    if ($('[id="' + from_input + '"], [id="' + to_input + '"]').length) {
        var $options = {
            locale: wpfdLocaleSettings,
            "alwaysShowCalendars": true,
            autoApply: true,
            autoUpdateInput: false
        };
        var startDate = $('#' + from_input).val();
        var endDate = $('#' + to_input).val();
        if (startDate !== '') {
            $options.startDate = startDate;
        }
        if (startDate !== '') {
            $options.endDate = endDate;
        }

        $('[id="' + from_input + '"], [id="' + to_input + '"]').daterangepicker($options, function(start, end, label) {
            // console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
            // Lets update the fields manually this event fires on selection of range
            var selectedStartDate = start.format(wpfdvars.dateFormat); // selected start
            var selectedEndDate = end.format(wpfdvars.dateFormat); // selected end

            $fromInput = $('[id="' + from_input + '"]');
            $toInput = $('[id="' + to_input + '"]');

            // Updating Fields with selected dates
            $fromInput.val(selectedStartDate);
            $toInput.val(selectedEndDate);

            // Setting the Selection of dates on calender on CHECKOUT FIELD (To get this it must be binded by Ids not Calss)
            var checkOutPicker = $toInput.data('daterangepicker');
            checkOutPicker.setStartDate(selectedStartDate);
            checkOutPicker.setEndDate(selectedEndDate);

            // Setting the Selection of dates on calender on CHECKIN FIELD (To get this it must be binded by Ids not Calss)
            var checkInPicker = $fromInput.data('daterangepicker');
            checkInPicker.setStartDate(selectedStartDate);
            checkInPicker.setEndDate(selectedEndDate);
        });
        $('[id="' + from_input + '"], [id="' + to_input + '"]').next().on('click', function(e) {$(this).prev().trigger('click')});
    }
}

function fullWidthSearch() {
    var $ = jQuery;
    var $inputSearchContainer = $('.wpfd_search_input');

    if($inputSearchContainer.parent().prev().hasClass('categories-filtering')) {
        $inputSearchContainer.addClass("fullwidth");
    } else {
        $inputSearchContainer.removeClass("fullwidth");
    }
}

jQuery(document).ready(function ($) {
    initSorting();
    //initTags();
    $(".chk_ftags").click(function () {
        fillInputTags();
    });
    $("#filter_catid").on('change', function () {
        catChange("filter_catid");
    });
    $("#search_catid").change(function () {
        catChange("search_catid");
    });

    $('.qCatesearch').on('keydown keyup', function(e) {
        if (e.keyCode === 13 || e.which === 13 || e.key === 'Enter')
        {
            e.preventDefault();
            return;
        }
        var scateList, filter, labl, txtValue;
        var $this = $(this);
        scateList = $("li.cate-item", $this.parent().parent());

        filter =  $this.val().toUpperCase();
        scateList.each(function () {
            labl = $(this).find("label");
            txtValue = labl.text().toUpperCase();
            if (txtValue.indexOf(filter) > -1) {
                $(this).css("display","");
            } else {
                $(this).css("display", "none");
            }
        });
    });
    $("#adminForm").submit(function (e) {
        e.preventDefault();
        return false;
    });
    $('#txtfilename').on('keyup', function(e) {
        var $this = $(this);
        if (e.keyCode === 13 || e.which === 13 || e.key === 'Enter')
        {
            e.preventDefault();

            if ($this.val() === '') {
                return;
            }
            ajaxSearch();

            return;
        }
    });
    jQuery('.icon-date').click(function () {
        var txt = jQuery(this).attr('data-id');
        if (txt !== 'cfrom' && txt !== 'cto' && txt !== 'ufrom' && txt !== 'uto') {
            jQuery('#' + txt).datetimepicker('show');
        }
    });

    initDateRangePicker('cfrom', 'cto');
    initDateRangePicker('ufrom', 'uto');


    jQuery('.feature-toggle').click(function () {
        var container = jQuery(this).parents('.by-feature');
        jQuery(container).find('.feature').slideToggle('slow', function () {
            jQuery(".feature-toggle").toggleClass(function () {
                if (jQuery(this).is(".toggle-arrow-up-alt")) {
                    return "toggle-arrow-down-alt";
                } else {
                    return "toggle-arrow-up-alt";
                }
            });
        });
    });

    //ajax filters
    $("#btnsearchbelow, #btnsearch").on('click', function (e) {
        e.preventDefault();
        ajaxSearch();
        return false;
    });

    $("#btnReset").on('click', function (e) {
        e.preventDefault();
        resetFilters();
        $("#wpfd-results").html("");
        return false;
    });
    $("#widget_btnReset").on('click', function (e) {
        e.preventDefault();
        resetFilters('#widget_search');
    });

    //get checktags
    selectdChecktags();

    //show cate
    showCategory();

    //display selected category when reload.
    wpfdshowCateReload();

    //set folder-icon for parent-folder
    parentFolderIcon();

    //add placeholder when tag search box is selected
    addPlaceholder();

    //get search full width
    fullWidthSearch();

    //get searchbox
    addtoSearchbuttonbelow();

    noTagscase();

    jQuery('.list-results table tr td a.file-item').click(function (e) {
        return true;
    });

    resetFilters = function (formSelect) {

        var sform = $("#adminForm");
        if (formSelect !== null && formSelect !== undefined) {
            sform = $(formSelect);
        }
        var inputs = $(sform).find('input:not([name="catid"]), select');

        $.each(inputs, function (i, el) {
            var eType = $(el).attr('type');
            if (eType === 'checkbox') {
                $(el).prop('checked', false);
            } else {
                $(el).val('');
                if ($(el).hasClass("tagit")) {
                    $(el).tagit("removeAll");
                }
            }
        });
        if (!$('.wpfd-listCate').is(':hidden')) {
            $('#filter_catid').val('');
        }

        $('.tags-item').removeClass('active');
        if($(".cate-lab .cancel").length == 1) {
            $('.cate-item').removeClass('checked');
            var wpfdlCurrentselectedCate = $(".categories-filtering .cate-lab");
            if (wpfdlCurrentselectedCate.hasClass('display-cate')) {
                wpfdlCurrentselectedCate.removeClass('display-cate');
            }
            wpfdlCurrentselectedCate.empty();
            wpfdlCurrentselectedCate.append("<label id='root-cate'>" + wpfdvars.msg_file_category + "</label>");
        }

        //window.history.pushState('', "", wpfdvars.basejUrl);
    };

    populateFilters = function (filters) {

        var sform = $("#adminForm");
        $.each(filters, function (f, v) {
            var els = $(sform).find('input[name=' + f + '], select[name=' + f + ']');
            if (els.length > 0) {
                $(els).val(v).trigger('change').trigger("liszt:updated").trigger("chosen:updated");
                if ($(els).hasClass("tagit")) {
                    $(els).tagit("removeAll");
                    if (v !== "") {
                        var tgs = v.split(",");
                        for (var i = 0; i < tgs.length; i++) {
                            $(els).tagit("createTag", tgs[i]);
                        }
                    }

                }
            }
        });
    };

    //Remove propery with empty value
    cleanObj = function (obj) {
        for (var k in obj) {
            if (obj.hasOwnProperty(k)) {
                if (!obj[k]) delete obj[k];
            }
        }
        return obj;
    };

    //back on browser
    jQuery(window).on('popstate', function (event) {
        var state = event.originalEvent.state;
        resetFilters();
        if (state !== null) {
            var formData = state;
            populateFilters(formData);
            formData.view = "frontsearch";
            formData.format = "raw";
            $.ajax({
                type: "POST",
                url: basejUrl + 'index.php?option=wpfd', // Not working
                data: formData
            }).done(function (result) {
                $("#wpfd-results").html(result);
            });
        } else {
            $("#wpfd-results").html("");
        }
    });
    var params = getSearchParams();
    if (params.q !== undefined ||
        params.catid !== undefined ||
        params.ftags !== undefined ||
        params.cfrom !== undefined ||
        params.cto !== undefined ||
        params.ufrom !== undefined ||
        params.uto !== undefined
    ) {
        ajaxSearch();
    }
});
