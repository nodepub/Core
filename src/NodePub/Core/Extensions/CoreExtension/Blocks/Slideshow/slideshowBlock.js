define(['jquery', 'jquery.nivoSlider'], function($) {
    var $slideshow = $('[data-showcase="slideshow"]');
    if ($slideshow.length && typeof $.fn.nivoSlider === 'function') {
        $slideshow.nivoSlider({
            effect: 'fade',
            animSpeed: 500,
            pauseTime: 9000,
            directionNav: true,
            captionOpacity: 0.5
        });
    }
});