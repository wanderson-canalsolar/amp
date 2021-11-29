(function ($) {
  $(document).on('click', function (e) {
    var domNode = $('#wpfd-core #restableMenu0');
    if (!domNode || domNode.find(e.target).length === 0) {
      domNode.addClass('restableMenuClosed');
    }
  });
  $(document).ready(function ($) {
    var wpfd_core = {
      scrolls: {},
      scrollBarSettings: { // document: http://manos.malihu.gr/jquery-custom-content-scroller/
        axis: 'y',
        theme: 'dark',
        scrollInertia: 100,
        live: true,
        autoHideScrollbar: true,
        contentTouchScroll: false,
        autoExpandScrollbar: true,
      },
      init: function () {
        if (typeof Wpfd !== 'object') {
          console.error('Fail on load Wpfd object!');
          return false;
        }

        if (window.innerWidth < 768) {
          this.scrollBarSettings.autoHideScrollbar = false;
        }
        // Add border-radius to visible tb in table
        $(document).on('load mouseover mouseout', '#wpfd-core tr.file td', this.addLastBorder);
        $(document).on('change', '#wpfd-core #rightcol .switch input[type="checkbox"]', this.switch);
        $(document).on('click', '.shortcode-copy', this.copy);
        $(document).on('DOMNodeInserted', '#category-theme-params', this.initCategoryFieldset);
        $(document).on('click', '#wpfd-hamburger', function() {
          var leftCol = $('#wpfd-categories-col');
          if (leftCol.css('left') == '-230px') {
            leftCol.css('left', '0');
          } else {
            leftCol.css('left', '-230px');
          }

        });

        this.scrollBarInit();
        this.loadPreviousCategory();
        $(document).on('click', '#wpfd-categories-col .wpfd_add_new', this.dropDownClick);

        // Events trigger
        $(document).on('wpfd_preview_updated wpfd_admin_search', '#wpfd-core #preview', this.wpfdPreviewUpdated);
        $(document).on('wpfd_category_created', '#categorieslist .dd-content', this.wpfdCategoryCreated);
        $(document).on('wpfd_category_click', '#categorieslist .dd-content', this.wpfdCategoryClick);
        $(document).on('wpfd_category_param_loaded', this.initThemeSelect);
        $(document).on('wpfd_category_param_loaded', this.initCategoryFieldset);
        $(document).on('wpfd_admin_search', '#wpfd-core #wpreview', this.wpfdAdminSearch);

        // Hide right column on 1366 of width
        $(window).on('resize', this.showHideRightCol);
        this.showHideRightCol();
      },
      showHideRightCol: function () {
        // Do not run this on an iframe
        if ($('#insertcategory').length === 0 && $('#insertfile').length === 0) {
          if (1366 >= window.innerWidth) {
            wpfd_core.hideRightCol();
          } else {
            wpfd_core.showRightCol();
          }
        }
      },
      hideRightCol: function () {
        var rightCol = $('#rightcol');
        var flipButton = $('.wpfd-flip');
        if (rightCol.is(':visible')) {
          rightCol.addClass('hide').removeClass('show');
          flipButton.css('transform', 'scale(-1)');
        }
      },
      showRightCol: function () {
        var rightCol = $('#rightcol');
        var flipButton = $('.wpfd-flip');
        if (!rightCol.is(':visible')) {
          rightCol.addClass('show').removeClass('hide');
          flipButton.css('transform', 'scale(1)');
        }
      },
      copy: function (e) {
        e.stopPropagation();
        var $this = $(this);
        var inputId = $this.data('ref');
        var linkcopy = $('input[name="' + inputId + '"]').val();

        var inputlink = document.createElement("input");
        inputlink.setAttribute("value", linkcopy);
        document.body.appendChild(inputlink);
        inputlink.select();
        document.execCommand("copy");
        document.body.removeChild(inputlink);
        $.gritter.add({text: wpfd_admin.msg_shortcode_copied_to_clipboard});
      },
      addLastBorder: function (e) {
        var $this = $(this);
        $this.parent().find('td').removeClass('bfirst blast');
        $this.parent().find('td:visible:first').addClass('bfirst');
        $this.parent().find('td:visible:last').addClass('blast');
      },
      switch: function (e) {
        var $this = $(this);
        var ref = $this.attr('name').replace('ref_', '');
        $('input[name="' + ref + '"]').val($this.prop('checked') ? 1 : 0);
      },
      scrollBarInit: function () {
        var leftScrollSettings = $.extend({}, this.scrollBarSettings);
        leftScrollSettings.callbacks = {
          whileScrolling: this.onLeftScrollDown,
          onTotalScrollBack: this.onLeftScrollBack,
        };
        var centerScrollSettings = $.extend({}, this.scrollBarSettings);
        centerScrollSettings.callbacks = {
          whileScrolling: this.onCenterScrollDown,
          onTotalScrollBack: this.onCenterScrollBack,
        };

        this.scrolls.left = $('#wpfd-core .scroller_wrapper').mCustomScrollbar(leftScrollSettings);
        this.scrolls.center = $('#wpfd-core #pwrapper .wpfd_center').mCustomScrollbar(centerScrollSettings);
        this.scrolls.right = $('#wpfd-core #rightcol').mCustomScrollbar(this.scrollBarSettings);
        if (window.location.href.indexOf('wpfd-config') === -1) {
          this.scrolls.adminmenuwrap = $('#adminmenuwrap').mCustomScrollbar({
            axis: 'y',
            theme: 'light',
            scrollInertia: 800,
            autoHideScrollbar: true,
            autoExpandScrollbar: false,
          });
        }


        var cnfLeft = $('.ju-left-panel');
        if (cnfLeft.length) {
          var cnfLeftScrollSettings = $.extend({}, this.scrollBarSettings);
          cnfLeftScrollSettings.theme = 'minimal';
          cnfLeftScrollSettings.autoExpandScrollbar = false;
          cnfLeft.mCustomScrollbar(cnfLeftScrollSettings)
        }
      },
      onLeftScrollDown: function () {
        $('#wpfd-core .scroller_wrapper').css('box-shadow', 'inset 0 12px 15px -17px #111');
      },
      onLeftScrollBack: function () {
        $('#wpfd-core .scroller_wrapper').css('box-shadow', 'unset');
      },
      onCenterScrollDown: function () {
        $('.wpfd-toolbar-wrapper').css('box-shadow', '0px 12px 15px -17px #111');
      },
      onCenterScrollBack: function () {
        $('.wpfd-toolbar-wrapper').css('box-shadow', 'unset');
      },
      loadPreviousCategory: function () {
        var catId = localStorage.getItem('wpfdSelectedCatId');
        if (catId) {
          if (typeof window.parent.selectedCatId !== "undefined") {
            return;
          }
          if (typeof window.parent.selectedFileId !== "undefined") {
            return;
          }
          var previousCat = $('[data-id-category=' + catId + '] .dd-content').first();
          previousCat.click();
          $('#wpfd-core .scroller_wrapper').mCustomScrollbar("scrollTo", previousCat);
        }
      },
      wpfdCategoryClick: function (e) {
        Wpfd.log('wpfd_category_click fired!');
        // Save category
        localStorage.setItem('wpfdSelectedCatId', $(e.target).parent().data('id-category'));
      },
      wpfdAdminSearch: function (e) {
        var wpreview = $(this);
        Wpfd.log('event wpfd_admin_search fired!');
        // Move toolbar to position
        $('.wpfd-toolbar-wrapper .restableMenu').remove();
        // todo: checkbox not recheck when search
        wpreview.find('.restableMenu').insertAfter($('.wpfd-filter-file'));

        // Check correct state for flip icon
        var rightCol = $('#rightcol');
        var flipButton = $('.wpfd-flip');
        if (!rightCol.is(':visible')) {
          flipButton.css('transform', 'rotate(180deg)');
        } else {
          flipButton.css('transform', 'rotate(0deg)');
        }
      },
      wpfdPreviewUpdated: function (e) {
        var preview = $(this);
        Wpfd.log('event wpfd_preview_updated fired!');
        // Move toolbar to position
        $('.wpfd-toolbar-wrapper .restableMenu').remove();
        // todo: checkbox not recheck when load other category
        preview.find('.restableMenu').insertAfter($('.wpfd-filter-file'));
        Wpfd.showhidecolumns();
        // Init Drop block for overlay
        // Remove old overlay
        if ($('#wpfd-drop-overlay').length) {
          $('#wpfd-drop-overlay').remove();
        }
        var dropOverlay = $('<div id="wpfd-drop-overlay" class="wpfd-drop-overlay hide"><div class="wpfd-overlay-inner">' + wpfd_admin.msg_upload_drop_file + '</div></div>');
        $('#wpfd-core').append(dropOverlay);
        Wpfd.uploader.assignDrop($('#wpfd-drop-overlay'));
        $('#wpfd-drop-overlay').on('drop', function () {
          $(this).addClass('hide');
          ;
        });
        // Show overlay on drag to #preview
        $('#preview').on("dragenter", function (e) {
          if (e.target === this) {
            return;
          }

          $('#wpfd-drop-overlay').removeClass('hide');
        });
        $(document).on("dragleave", function (e) {
          // Detect is real dragleave
          if (e.originalEvent.pageX !== 0 || e.originalEvent.pageY !== 0) {
            return false;
          }

          $('#wpfd-drop-overlay').addClass('hide');
        });

        // Check correct state for flip icon
        var rightCol = $('#rightcol');
        var flipButton = $('.wpfd-flip');
        if (!rightCol.is(':visible')) {
          flipButton.css('transform', 'scale(-1)');
        } else {
          flipButton.css('transform', 'scale(1)');
        }
      },
      wpfdCategoryCreated: function (e) {
        Wpfd.log('event wpfd_category_created fired!');
        $('#wpfd-core .scroller_wrapper').mCustomScrollbar("scrollTo", $(this));
      },
      dropDownClick: function (e) {
        e.preventDefault();
        $('#wpfd-categories-col .ju-dropdown-menu').show();
        return false;
      },
      initThemeSelect: function () {
        $('.wpfd-themes-select .wpfd-theme').on('click', function (e) {
          var $this = $(this);
          $('.wpfd-themes-select .wpfd-theme').removeClass('checked');
          $this.addClass('checked');
          var input = $('#wpfd-theme');
          input.val($this.attr('ref'));
          input.trigger('change'); // Made sure it trigger a change
        });
      },
      initCategoryFieldset: function () {
        wpfd_core.initCategoryFieldsetState();
        $('#category_params legend').unbind('click').on('click', function (e) {
          var $this = $(this);
          if ($this.hasClass('collapsed')) {
            $this.removeClass('collapsed');
          } else {
            $this.addClass('collapsed');
          }
          $this.parent().find('div.control-group:not(".hidden")').slideToggle(150, 'swing', function () {
            wpfd_core.saveCategoryFieldsetState();
          });

        });
      },
      initCategoryFieldsetState: function () {
        var wpfdFieldsetState = localStorage.getItem('wpfdFieldsetState');

        if (wpfdFieldsetState) {
          wpfdFieldsetState = JSON.parse(wpfdFieldsetState);
          if (wpfdFieldsetState.length) {
            $.each(wpfdFieldsetState, function (index, fieldset) {
              if (parseInt(fieldset.state) === 0) {
                $('#' + fieldset.id).find('div.control-group:not(".hidden")').hide();
                $('#' + fieldset.id + ' legend').addClass('collapsed');
              }
            });
          }
        }
      },
      saveCategoryFieldsetState: function () {
        var fieldsets = $('#category_params fieldset');
        if (fieldsets.length) {
          var wpfdFieldsetState = [];
          $.each(fieldsets, function (index, fieldset) {
            var item = {id: $(fieldset).prop('id'), state: 1};
            if ($(fieldset).find('legend').length && $(fieldset).find('legend').hasClass('collapsed')) {
              item.state = 0;
            }
            wpfdFieldsetState.push(item);
          });
          localStorage.setItem('wpfdFieldsetState', JSON.stringify(wpfdFieldsetState));
        }
      },
    };

    wpfd_core.init();

  });
})(jQuery);
