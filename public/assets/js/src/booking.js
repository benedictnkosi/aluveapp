$(document).ready(function () {


    $("#new-res-form").validate({
        // Specify validation rules
        rules: {
            guestName: "required",
            phoneNumber: "required"
        },
    });

    $("#new-res-form").submit(function (event) {
        event.preventDefault();
    });


    $("#bookNowButton").click(function (event) {
        event.preventDefault();
        createReservation();
    });

    $("body").addClass("loading");

    let date = new Date();
    let endDate = new Date(date.getTime());
    //get available rooms for today if previous date not set
    if(localStorage.getItem("checkInDate") == null){
        endDate.setDate(date.getDate() + 1);
        const strToday = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
        const strTomorrow = endDate.getFullYear() + "-" + (endDate.getMonth() + 1) + "-" + endDate.getDate();
        localStorage.setItem('checkInDate',strToday );
        localStorage.setItem('checkOutDate',strTomorrow);
        getAvailableRooms( strToday , strTomorrow);
    }else{
        date = new Date(localStorage.getItem('checkInDate'));
        endDate = new Date(localStorage.getItem('checkOutDate'));
        $(this).val(localStorage.getItem('checkInDate') + ' - ' + localStorage.getItem('checkOutDate'));
        getAvailableRooms( localStorage.getItem('checkInDate') , localStorage.getItem('checkOutDate'));
    }

    //date picker
    $.getScript("https://cdn.jsdelivr.net/jquery/latest/jquery.min.js", function () {
        $.getScript("https://cdn.jsdelivr.net/momentjs/latest/moment.min.js", function () {
            $.getScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js", function () {

                $('#checkindate').daterangepicker({
                    opens: 'left',
                    autoApply: false,
                    minDate: date
                }, function (start, end, label) {
                    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                });

                $('#checkindate').daterangepicker({ startDate: date, endDate: endDate});

                $('#checkindate').on('apply.daterangepicker', function (event, picker) {
                    getAvailableRooms(picker.startDate.format("YYYY-MM-DD"), picker.endDate.format("YYYY-MM-DD"));
                    localStorage.setItem('checkInDate',picker.startDate.format("YYYY-MM-DD") );
                    localStorage.setItem('checkOutDate',picker.endDate.format("YYYY-MM-DD"));

                    let checkInDate = new Date(picker.startDate.format("YYYY-MM-DD"));
                    let checkOutDate = new Date(picker.endDate.format("YYYY-MM-DD"))
                    let difference = checkOutDate - checkInDate;
                    let totalDays = Math.ceil(difference / (1000 * 3600 * 24));
                    console.log("date diff is " + totalDays);
                    localStorage.setItem('numberOfNights',totalDays);
                });
                $("body").removeClass("loading");
            });
        });
    });
});

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function displayTotal(){
    let numberOfNights =  parseInt(localStorage.getItem('numberOfNights'));
    let selectedRoomName =  $('#btn-select').find('.room_name').html();

    let selectedRoomPrice =   parseInt($('#btn-select').find('img').attr("data-price"));
    let total = numberOfNights * selectedRoomPrice;
    let totalMessage = "Total: R"+total+".00";
    let nightsMessage = numberOfNights + " x nights @ R"+selectedRoomPrice+".00";

    $('#total_message').html(totalMessage);
    $('#nights_message').text(nightsMessage);
}

function getAvailableRooms(checkInDate, checkOutDate){
    $("#availableRoomsDropdown").load("/api/rooms/"+checkInDate+"/"+checkOutDate, function() {
        var langArray = [];
        $('.vodiapicker option').each(function(){
            var img = $(this).attr("data-thumbnail");
            var price = $(this).attr("data-price");
            var room_id = $(this).attr("data-roomId");
            var room_name = this.innerText;
            var item = '<li><img src="'+ img +'" data-price="'+price+'" data-roomId="'+room_id+'" data-roomName="'+room_name+'"/><div class="div-select-room-name">'+ room_name +'<div class="select_sleeps"><span class="fa fa-users">2 Guests</span><span>ZAR '+price+'</span></div></div></li>';
            langArray.push(item);
        })

        $('#a').html(langArray);

//Set the button value to the first el of the array
        $('.btn-select').html(langArray[0]);
        $('.btn-select').attr('value', 'en');

//change button stuff on click
        $('#a li').click(function(){
            var img = $(this).find('img').attr("src");
            var price = $(this).find('img').attr("data-price");
            var room_id = $(this).find('img').attr("data-roomId");
            var value = $(this).find('img').attr('value');
            var room_name = $(this).find('img').attr("data-roomName");
            var item = '<li><img src="'+ img +'"  data-price="'+price+'" data-roomId="'+room_id+'" data-roomName="'+room_name+'"/><div class="div-select-room-name">'+ room_name +'<div class="select_sleeps"><span class="fa fa-users">2 Guests</span><span>ZAR 599</span></div></div></li>';
            $('.btn-select').html(item);
            $('.btn-select').attr('value', value);
            $(".b").toggle();
            displayTotal();
        });

        $(".btn-select").click(function(){
            $(".b").css("display","block");
        });

        //check local storage for the lang
        const sessionLang = localStorage.getItem('lang');
        if (sessionLang){
            //find an item with value of sessionLang
            var langIndex = langArray.indexOf(sessionLang);
            $('.btn-select').html(langArray[langIndex]);
            $('.btn-select').attr('value', sessionLang);
        } else {
            var langIndex = langArray.indexOf('ch');
            console.log(langIndex);
            $('.btn-select').html(langArray[langIndex]);
            //$('.btn-select').attr('value', 'en');
        }
        let checkInDateDate = new Date(checkInDate);
        let checkOutDateDate = new Date(checkOutDate)
        let difference = checkOutDateDate - checkInDateDate;
        let totalDays = Math.ceil(difference / (1000 * 3600 * 24));
        console.log("date diff is " + totalDays);
        localStorage.setItem('numberOfNights',totalDays);

        displayTotal();
    });
}

function createReservation() {
    $("#reservation_error_message_div").addClass("display-none");
    const guestName = $('#guestName').val();
    const phoneNumber = $('#phoneNumber').val();
    const email = $('#email').val();
    const checkInDate =  localStorage.getItem('checkInDate');
    const checkOutDate = localStorage.getItem('checkOutDate');
    const roomId = $('#btn-select').find('img').attr("data-roomId");

    if(guestName.length < 1){
        $("#reservation_message").text("Please provide guest name")
        $("#reservation_error_message_div").removeClass("display-none");
        return;
    }
    if(phoneNumber.length < 1){
        $("#reservation_message").text("Please provide guest phone number")
        $("#reservation_error_message_div").removeClass("display-none");
        return;
    }

    if(email.length > 0){
        if(!isEmail(email)){
            $("#reservation_message").text("The email format is invalid")
            $("#reservation_error_message_div").removeClass("display-none");
            return;
        }
    }
    $("body").addClass("loading");
    $.get("/api/reservations/create/" + roomId + '/'+guestName+ '/'+phoneNumber+ '/'+checkInDate+ '/'+checkOutDate+'/'+email, function (data) {
        $("body").removeClass("loading");
        if (data[0].result_code !== 0) {
            $("#reservation_message").text(data[0].result_message)
            $("#reservation_error_message_div").removeClass("display-none");
        }else{
            localStorage.setItem("reservation_id",data[0].reservation_id)
            window.location.href = "/confirmation.html";
        }
    });
}