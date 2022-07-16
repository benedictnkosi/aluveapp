$(document).ready(function () {
    $('#thank_message').html("Thank you for your reservation (#"+localStorage.getItem("reservation_id")+")");

    $('#viewReservation').click(function(){
        window.open(
            "/invoice.html?reservation=" + localStorage.getItem("reservation_id"),
            '_blank' // <- This is what makes it open in a new window.
        );
    });
});