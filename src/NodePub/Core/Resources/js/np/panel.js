define(['jquery'], function($) {

    function handleOpenClick(e) {
        e.preventDefault();
        panel.open($(this).attr('href'));
    }

    function handleCloseClick(e) {
        e.preventDefault();
        panel.close();
    }

    var panel = {
        panelContainer: $('#np_panel_container'),

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            this.panelContainer.delegate('a[data-panel="open"]', 'click', handleOpenClick);
            this.panelContainer.delegate('a[data-close="panel"]', 'click', handleCloseClick);
        },

        open: function(url) {

            $(document).trigger('npPanelEnabled');

            var that = this;

            $.ajax({
                url: url,
                success: function(data) {
                    that.panelContainer.html(data);
                    that.panelContainer.show();
                }
            });
        },

        close: function() {
            this.panelContainer.hide();
            $(document).trigger('npPanelDisabled');
        },

        showSpinner: function() {}
    };

    return panel;
});