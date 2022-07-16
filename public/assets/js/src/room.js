$(document).ready(function () {
    $.getScript("assets/js/simple-lightbox.min.js", function(){
        const gallery = $('.gallery a').simpleLightbox({});
    });
});
