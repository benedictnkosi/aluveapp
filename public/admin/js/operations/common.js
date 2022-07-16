function showResErrorMessage(divName, message){
    $("#"+divName+"_error_message_div").removeClass("display-none");
    $("#"+divName+"_error_message").text(message)
    $("#"+divName+"_success_message_div").addClass("display-none");
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#"+divName+"_error_message_div").offset().top -100
    }, 2000);
}

function showResSuccessMessage(divName,message){
    $("#"+divName+"_error_message_div").addClass("display-none");
    $("#"+divName+"_success_message").text(message)
    $("#"+divName+"_success_message_div").removeClass("display-none");
    $([document.documentElement, document.body]).animate({
        scrollTop: $("#"+divName+"_error_message_div").offset().top
    }, 2000);
}