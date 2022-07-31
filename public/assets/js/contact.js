$(document).ready(function () {
    $("#contact_us_form").submit(function (event) {
        event.preventDefault();
    });

    $("#send_message").click(function (event) {
        event.preventDefault();
        sendMessage();
    });
});

function sendMessage() {
    const customerName = $('#customerName').val();
    const email = $('#email').val();
    const phoneNumber = $('#phoneNumber').val();
    const message = $('#message').val();

    let url = "/public/sales/contact/" + customerName + "/" + email + "/" + phoneNumber+ "/" + encodeURIComponent(message);
    $("body").addClass("loading");
    $("#success_message_div").addClass("display-none");
    $("#error_message_div").addClass("display-none");
    $.getJSON(url + "?callback=?", null, function (data) {
        $("body").removeClass("loading");
        if (data[0].result_code === 0) {
            $("#success_message_div").removeClass("display-none");
            $("#success_message").text(data[0].result_message)
        }else{
            $("#error_message_div").removeClass("display-none");
            $("#error_message").text(data[0].result_message)
        }
    });
}

function newTrialMessage() {
    const customerName = $('#customerName').val();
    const email = $('#email').val();
    const phoneNumber = $('#phoneNumber').val();
    const hotel_name = $('#hotel_name').val();

    let url = "/public/sales/trial/" + customerName + "/" + email + "/" + phoneNumber + "/" + hotel_name;
    $("body").addClass("loading");
    $("#success_message_div").addClass("display-none");
    $("#error_message_div").addClass("display-none");
    $.getJSON(url + "?callback=?", null, function (data) {
        $("body").removeClass("loading");
        if (data[0].result_code === 0) {
            $("#success_message_div").removeClass("display-none");
            $("#success_message").text(data[0].result_message)
        }else{
            $("#error_message_div").removeClass("display-none");
            $("#error_message").text(data[0].result_message)
        }
    });
}