define(['jquery', 'jquery.twitterFeed'], function($) {
    var $feed = $('[data-feed="twitter"]');
    if ($feed.length && typeof $.fn.twitterFeed === 'function') {
        $feed.twitterFeed();
    }
});