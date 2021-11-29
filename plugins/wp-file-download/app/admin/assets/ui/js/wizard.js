(function ($) {
  $(document).ready(function ($) {
    var wpfd_wizard = {
      init: function () {
        $(document).on('change', '.ju-switch-button .switch input[type="checkbox"]', this.switch);
        $(document).on('click', '.wizard-theme-config .wpfd-theme .overlay', this.theme_select);
      },
      theme_select: function (e) {
        var $this = $(e.target);
        // Clear selected
        $('.wizard-theme-config .wpfd-theme').removeClass('checked');
        $('.wizard-theme-config input').prop('checked', false);

        // Select current
        $this.parent().addClass('checked');
        $this.parent().find('input').prop('checked', true);
      },
      switch: function (e) {
        var $this = $(e.target);
        var ref = $this.attr('name').replace('ref_', '');
        $('input[name="' + ref + '"]').val($this.prop('checked') ? 1 : 0);
      },
      minicolors: function () {
        $('.minicolors').minicolors({position: "bottom right"});
      },
    };

    // Wizard Init
    wpfd_wizard.init();
    wpfd_wizard.minicolors();
  });
})(jQuery);