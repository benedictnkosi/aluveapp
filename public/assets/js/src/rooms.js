$(document).ready(function () {
    getAllRooms();

    //get available rooms for today
    const date = new Date();
    const tomorrow = new Date(date.getTime());
    tomorrow.setDate(date.getDate() + 2);
    const strToday = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
    const strTomorrow = tomorrow.getFullYear() + "-" + (tomorrow.getMonth() + 1) + "-" + tomorrow.getDate();
    localStorage.setItem('checkInDate',strToday );
    localStorage.setItem('checkOutDate',strTomorrow);

    $("body").addClass("loading");

    //date picker
    $.getScript("https://cdn.jsdelivr.net/jquery/latest/jquery.min.js", function () {
        $.getScript("https://cdn.jsdelivr.net/momentjs/latest/moment.min.js", function () {
            $.getScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js", function () {

                $('#rooms_checkindate').daterangepicker({
                    opens: 'right',
                    autoApply: false,
                    minDate: date,
                    autoUpdateInput: false,
                }, function (start, end, label) {
                    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                });


                $('#rooms_checkindate').on('apply.daterangepicker', function (event, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    localStorage.setItem('checkInDate',picker.startDate.format("YYYY-MM-DD") );
                    localStorage.setItem('checkOutDate',picker.endDate.format("YYYY-MM-DD") );
                    filterRooms(picker.startDate.format("YYYY-MM-DD"), picker.endDate.format("YYYY-MM-DD"));
                });

                $("body").removeClass("loading");
            });
        });
    });

});


function getAllRooms(){
    $("#rooms_parent_div").load("/api/allrooms", function() {

    });
}

function filterRooms(checkIn, checkOut){
    $("body").addClass("loading");
    $("#rooms_parent_div").load("/api/roomspage/"+checkIn+"/"+checkOut, function() {
        $("body").removeClass("loading");
    });
}
