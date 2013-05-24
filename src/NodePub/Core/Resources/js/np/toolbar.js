define(['jquery'], function($) {

    var enabledClass = 'np_enabled',
        disabledClass = 'np_disabled';

    return {

        init: function() {
            var that = this;
            $('#np_menu_toggle').click(function(e) {
                e.preventDefault();

                var $this = $(this);

                if ($this.data('enabled') === true) {
                    that.disable();
                    $this.data('enabled', false);
                    $(document).trigger('npDisabled');
                } else {
                    that.enable();
                    $this.data('enabled', true);
                    $(document).trigger('npEnabled');
                }
            });
        },

        enable: function() {
            $('body').addClass(enabledClass);
            $('body').removeClass(disabledClass);
            $('#np_container').addClass(enabledClass);
            $('#np_container').removeClass(disabledClass);
        },

        disable: function() {
            $('body').addClass(disabledClass);
            $('body').removeClass(enabledClass);
            $('#np_container').addClass(disabledClass);
            $('#np_container').removeClass(enabledClass);
        }
    };
});