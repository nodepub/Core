requirejs.config({
    baseUrl: '/js/lib',
    paths: {
        "np": "../np"
    },
    shim: {
        "spectrum": ["jquery"]
    }
});

requirejs(['np/themeEngine']);