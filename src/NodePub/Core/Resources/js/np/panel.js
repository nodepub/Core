define(['jquery'], function($) {

    var panel = {
        panelContainer: $('#npub_panel_container'),

        init: function() {
            var that = this;
            // Activate panel links
            $('a[data-panel="open"]').live('click', function(e) {
                e.preventDefault();
                that.open($(this).attr('href'));
            });
        },

        open: function(url) {

            $(document).trigger('npubPanelEnabled');

            var that = this;

            $.ajax({
                url: url,
                success: function(data) {
                    that.panelContainer.html(data);
                    that.panelContainer.show();

                    that.panelContainer.find('a[data-close="panel"]').click(function(e) {
                        e.preventDefault();
                        that.close();
                    });
                }
            });
        },

        close: function() {
            this.panelContainer.hide();
            $(document).trigger('npubPanelDisabled');
        },

        showSpinner: function() {}
    };

    panel.init();

    return panel;
});