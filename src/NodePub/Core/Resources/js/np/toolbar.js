define(['jquery', 'editableBlock', 'panel'], function($, editableBlock, panel) {

    var enabledClass = 'npub_enabled',
        disabledClass = 'npub_disabled';

    return {

        init: function() {
            var that = this;
            $('#npub_menu_toggle').click(function(e) {
                e.preventDefault();

                var $this = $(this);

                if ($this.data('enabled') === true) {
                    that.disable();
                    $this.data('enabled', false);
                    $(document).trigger('npubDisabled');
                } else {
                    that.enable();
                    $this.data('enabled', true);
                    $(document).trigger('npubEnabled');
                }
            });

            $('#npub_icons a').click(function(e) {
                e.preventDefault();
                var $this = $(this),
                    href = $this.attr('href');
                    panel.open(href);
            });
        },

        enable: function() {
            $('body').addClass(enabledClass);
            $('body').removeClass(disabledClass);
            $('#npub_container').addClass(enabledClass);
            $('#npub_container').removeClass(disabledClass);

            editableBlock.enableEditableAreas();
            editableBlock.enableEditableBlocks();
        },

        disable: function() {
            $('body').addClass(disabledClass);
            $('body').removeClass(enabledClass);
            $('#npub_container').addClass(disabledClass);
            $('#npub_container').removeClass(enabledClass);

            editableBlock.disableEditableAreas();
            editableBlock.disableEditableBlocks();
        }
    };
});