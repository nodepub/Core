define(['jquery', 'underscore', 'panel'], function($, _, panel) {

    return {

        enableEditableAreas: function() {

            var that = this;

            $('.npub_area').each(function() {
                var $this = $(this),
                    $addBlock = $this.find('.npub_add_block')
                    ;
                $addBlock.find('a').attr('href', '/admin/blocks/' + area + '/new');

                if (!$addBlock.length) {
                    var area = $this.attr('data-area');
                    $addBlock = $('<div class="npub_add_block"><i class="icon-plus-sign"></i><a href="#">Add block to '+area+'</a></div>');

                    $addBlock.find('a').attr('href', '/admin/blocks/' + area + '/new');

                    $addBlock.click(that.doAddBlockClick);

                    $this.append($addBlock);
                }

                $addBlock.show();
            });
        },

        disableEditableAreas: function() {
            $('.npub_add_block').hide();
        },

        enableEditableBlocks: function() {

            var that = this;

            $('.npub_editable_block').each(function() {
                var $this = $(this),
                    $mask = $this.find('.npub_mask')
                    ;

                if (!$mask.length) {
                    $mask = $('<div class="npub_mask"></div>');
                    $mask.width($this.width() - 2);
                    $mask.height($this.height() - 2);
                    $mask.click(that.doBlockClick);
                    $this.prepend($mask);
                }

                $mask.show();
            });
        },

        disableEditableBlocks: function() {
            $('.npub_mask').hide();
        },

        doAddBlockClick: function(e) {
            e.preventDefault();

            var $this = $(this);

            $this.popover({
                title: 'Block Types',
                placement: 'right',
                html: true,
                trigger: 'manual',
                content: $("#npub_block_types_temp").html()
            });

            if ($this.data('active') === true) {
                $this.popover('hide');
                $this.data('active', false);
            } else {
                $this.popover('show');
                $this.data('active', true);
            }

            $(document).bind('npubDisabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });

            $(document).bind('npubPanelEnabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });
        },

        doBlockClick: function(e) {
            e.preventDefault();

            var template = $("#npub_block_actions_temp").html(),
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

            $(document).bind('npubDisabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });

            $(document).bind('npubPanelEnabled', function() {
                $this.popover('hide');
                $this.data('active', false);
            });
        }
    };
});