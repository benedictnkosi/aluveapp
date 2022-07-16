function scrollToContactSection(){
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#contact_us_div").offset().top
    });
}