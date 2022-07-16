$(document).ready(function () {
    getRooms("rooms_select");

    $("#new-res-form").submit(function (event) {
        event.preventDefault();
        createInvoice()
    });


    $("#checkin_date").datepicker({
        onSelect: function (dateText) {
            calculateNumberOfNights();
        },

        dateFormat: "yy-mm-d",
    });


    $("#checkout_date").datepicker({
        dateFormat: "yy-mm-d",

        onSelect: function () {

            calculateNumberOfNights();

        },

    });


    $("#userNumber").blur(function () {
        getCustomer();
    });
});

function getCustomer() {
    localStorage.setItem('customer_state', 'clear');
    $("#userNumber").val($("#userNumber").val().replaceAll(" ", ""));
    $("#userNumber").val($("#userNumber").val().replaceAll("+27", "0"));
    $("#userName").val("");
    $("#verified-tiny-image").addClass("display-none");


    if ($("#userNumber").val().length == 10) {

        $.get("/api/guests/" + $("#userNumber").val(), function (data) {

            var jsonObj = data[0];

            if (jsonObj.result_code === 0) {
                $('#userName').val(jsonObj.name);

                if (jsonObj.state.localeCompare("blocked") === 0) {
                    localStorage.setItem('customer_state', 'blocked');
                    $("#verified-tiny-image").removeClass("display-none");
                    $('#verified-tiny-image').attr("src", "images/blocked_guest.jpg");
                    $('#prev-rooms-label').text("Guest not welcomed at the guesthouse because: " + jsonObj.comments);
                    return;
                }


                $("#verified-tiny-image").removeClass("display-none");
                if (jsonObj.image_id.localeCompare("Not Verified") === 0) {
                    $('#verified-tiny-image').attr("src", "images/Not-Verified-tiny.png");
                } else {
                    $('#verified-tiny-image').attr("src", "images/verify-tiny.png");
                }
                $('#prev-rooms-label').text("Previous Rooms: " + jsonObj.rooms);

            }

        });

    }

}

function calculateTotal() {

    //get value for check in and checkout dates

    const checkInDate = new Date($("#checkin_date").val());

    const checkOutDate = new Date($("#checkout_date").val());


    if (checkInDate > checkOutDate || $("#checkin_date").val().length < 1 || $("#checkout_date").val().length < 1) {
        return;
    }

    var numberOfNights = parseInt($("#number_of_night").text());
    var pricePerNight = parseInt($("#price_per_night").text());

    $("#total_due").text(numberOfNights * pricePerNight);

    $("#input_total_due").val(numberOfNights * pricePerNight);

}

function createInvoice() {
    if (localStorage.getItem("customer_state").localeCompare("blocked") === 0) {
        $("#invoice_error_message").text("This guest is blocked from booking with us. Contact owner")

        $("#invoice_error_message_div").removeClass("display-none");

        $("#invoice_success_message_div").addClass("display-none");

        return;
    }

    var checkInDate = new Date($("#checkin_date").val());

    var checkOutDate = new Date($("#checkout_date").val());

    var amountPaid = $("#invoice_paid").val();

    $("#action").val(localStorage.getItem("property_manager_action"));

    $("#invoice_error_message_div").addClass("display-none");

    $("#invoice_success_message_div").addClass("display-none");


    if (checkInDate > checkOutDate) {

        $("#invoice_error_message").text("Check out should be greater than check in")

        $("#invoice_error_message_div").removeClass("display-none");

        $("#invoice_success_message_div").addClass("display-none");

        return;
    }

    if (isNaN(amountPaid)) {
        $("#invoice_error_message").text("Paid is not a number")
        $("#invoice_error_message_div").removeClass("display-none");

        $("#invoice_success_message_div").addClass("display-none");
        return;
    }


    var postData = $("#new-res-form").serialize();


    $("body").addClass("loading");
    $.post("/api/reservations/create/" + postData, function (data) {
        $("body").removeClass("loading");
        var jsonObj = data[0];

        if (jsonObj.result_code === 0) {

            $("#invoice_success_message").text(jsonObj.result_message)

            $("#invoice_error_message_div").addClass("display-none");

            $("#invoice_success_message_div").removeClass("display-none");

            getInvoices();
            getReservations("future");
            getcalendar("future");
            getStayOvers("stayover");
            getCheckouts("checkout");

        } else {

            $("#invoice_error_message").text(jsonObj.result_message)

            $("#invoice_error_message_div").removeClass("display-none");

            $("#invoice_success_message_div").addClass("display-none");
        }


    });
}

function calculateNumberOfNights() {

    //get value for check in and checkout dates

    var checkInDate = new Date($("#checkin_date").val());

    var checkOutDate = new Date($("#checkout_date").val());

    if (checkInDate > checkOutDate) {

        return;

    }

    if ($("#checkin_date").val().length > 0 && $("#checkout_date").val().length > 0) {

        const diffTime = Math.abs(checkInDate - checkOutDate);

        console.log(diffTime / (1000 * 60 * 60 * 24));
        const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));

        $("#number_of_night").text(diffDays);

        $("#input_number_of_night").val(diffDays);

        calculateTotal();

    }


}





