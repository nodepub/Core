define(['jquery', 'jquery.tumblrFeed'], function($) {
    var $feed = $('[data-feed="tumblr"]');
    if ($feed.length && typeof $.fn.tumblrFeed === 'function') {
        $feed.tumblrFeed();
    }
});