$(document).ready(function () {
    $("#contact_us_form").submit(function (event) {
        event.preventDefault();
    });

    $("#contact_us_form").validate({
        // Specify validation rules
        rules: {
            customerName: "required",
            phoneNumber: "required",
            email: {
                required: false,
                email: true
            },
            message: "required"
        },
        submitHandler: function () {
            sendMessage();
        }
    });

    $("#trial_form").submit(function (event) {
        event.preventDefault();
    });

    $("#trial_form").validate({
        // Specify validation rules
        rules: {
            customerName: "required",
            phoneNumber: "required",
            email: {
                required: false,
                email: true
            },
            hotel_name: "required"
        },
        submitHandler: function () {
            newTrialMessage();
        }
    });
});

function sendMessage() {
    const customerName = $('#customerName').val();
    const email = $('#email').val();
    const phoneNumber = $('#phoneNumber').val();
    const message = $('#message').val();
    let url = "/public/sales/contact/" + customerName + "/" + email + "/" + phoneNumber+ "/" + encodeURIComponent(message);
    $("#success_message_div").addClass("display-none");
    $("#error_message_div").addClass("display-none");
    $.get(url, function(data){
        if (data[0].result_code === 0) {
            $("#success_message_div").removeClass("display-none");
            $("#success_message").text(data[0].result_message)
        }else{
            $("#error_message_div").removeClass("display-none");
            $("#error_message").text(data[0].result_message)
        }
        $('html, body').animate({scrollTop: $('#success_message').offset().top -100 }, 'slow');
    });
}