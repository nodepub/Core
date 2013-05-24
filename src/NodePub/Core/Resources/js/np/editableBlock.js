define(['jquery', 'underscore'], function($, _) {

    return {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var that = this;
            $(document).bind('npEnabled', function(e) {
                that.enableEditableAreas();
                that.enableEditableBlocks();
            });

            $(document).bind('npDisabled', function(e) {
                that.disableEditableAreas();
                that.disableEditableBlocks();
            });
        },

        enableEditableAreas: function() {

            var that = this;

            $('.np_area').each(function() {
                var $this = $(this),
                    $addBlock = $this.find('.np_add_block')
                    ;
                $addBlock.find('a').attr('href', '/admin/blocks/' + area + '/new');

                if (!$addBlock.length) {
                    var area = $this.attr('data-area');
                    $addBlock = $('<div class="np_add_block"><i class="icon-plus-sign"></i><a href="#">Add block to '+area+'</a></div>');

                    $addBlock.find('a').attr('href', '/admin/blocks/' + area + '/new');

                    $addBlock.click(that.doAddBlockClick);

                    $this.append($addBlock);
                }

                $addBlock.show();
            });
        },

        disableEditableAreas: function() {
            $('.np_add_block').hide();
        },

        enableEditableBlocks: function() {

            var that = this;

            $('.np_editable_block').each(function() {
                var $this = $(this),
                    $mask = $this.find('.np_mask')
                    ;

                if (!$mask.length) {
                    $mask = $('<div class="np_mask"></div>');
                    $mask.width($this.width() - 2);
                    $mask.height($this.height() - 2);
                    $mask.click(that.doBlockClick);
                    $this.prepend($mask);
                }

                $mask.show();
            });
        },

        disableEditableBlocks: function() {
            $('.np_mask').hide();
        },

        doAddBlockClick: function(e) {
            e.preventDefault();

            var $this = $(this);

            $this.popover({
                title: 'Block Types',
                placement: 'right',
                html: true,
                trigger: 'manual',
                content: $("#np_block_types_temp").html()
            });

            if ($this.data('active') === true) {
                $this.popover('hide');
                $this.data('active', false);
            } else {
                $this.popover('show');
                $this.data('active', true);
            }

            $(document).bind('npDisabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });

            $(document).bind('npPanelEnabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });
        },

        doBlockClick: function(e) {
            e.preventDefault();

            var template = $("#np_block_actions_temp").html(),
                $this = $(this),
                id = $this.parent().attr('data-block-id')
                ;

            $(this).popover({
                placement: 'right',
                html: true,
                trigger: 'manual',
                content: _.template(template,{id:id})
            });

            if ($this.data('active') === true) {
                $this.popover('hide');
                $this.data('active', false);
            } else {
                $this.popover('show');
                $this.data('active', true);
            }

            $(document).bind('npDisabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });

            $(document).bind('npPanelEnabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });
        }
    };
});