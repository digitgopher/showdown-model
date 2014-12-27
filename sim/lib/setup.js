requirejs.config({
    //By default load any module IDs from baseUrl
    baseUrl: 'sim/lib',
    //except, if the module ID starts with "app", "sim", etc
    //paths config is relative to the baseUrl, and
    //never includes a ".js" extension since
    //the paths config could be for a directory.
    paths: {
        app: '../app',
        sim: '..',
        jquery: 'jquery-1.11.1.min'
    }
});

// Start the main app logic.
requirejs(['jquery','sim/simpage',"sim/simrun"],
function   ($, page, run) {
    //jQuery, sim loaded and can be used here now.
});