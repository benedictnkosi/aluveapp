function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
}

function showBackToReservationsLink() {
    let url =  "/public/me";
    $.get(url, function(data){
        isLoggedin = data[0].authenticated;
        sessionStorage.setItem('authenticated',isLoggedin )
        if(isLoggedin){
            $('.loginBtn').addClass("hidden");
            $('.signUpBtn').addClass("hidden");
            $('#backToReservations').removeClass("hidden");

        }
    });
}
