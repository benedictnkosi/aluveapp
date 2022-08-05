$(document).ready(function () {
    let reservationIdArray = JSON.parse(sessionStorage.getItem("reservation_id"));
    let message;
    if (reservationIdArray.length > 1) {
        $('#viewReservation').css("display", "none");
        message = "Thank you for your reservations ( ";
        reservationIdArray.forEach(element => message += "#" + element + " ");
        message += ")";
        $('#thank_message').html(message);
    } else {
        let resId = sessionStorage.getItem("reservation_id").replace('[','').replace(']', '');
        $('#thank_message').html('Thank you for your reservation (<a target="_blank" href="/invoice?id='+resId+'">#' + resId + '</a>)');
    }

    if($('#item_description').length > 0){
        $('#item_description').val(sessionStorage.getItem("reservation_id"));
        $('#item_amount').val(sessionStorage.getItem("item_amount"));
        $('#return_url').val("https://" +location.hostname+"/thankyou");
        $('#cancel_url').val("https://" +location.hostname+"/cancelled");
    }
    showBackToReservationsLink();
});
