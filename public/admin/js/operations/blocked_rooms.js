$(document).ready(function () {

});

function loadBlockedRoomsPageData() {
    getBlockedRooms();
    getBlockRooms();
    bindBlockedRoomsEvents();
}

function bindBlockedRoomsEvents(){
    $("#block-form").validate({
        // Specify validation rules
        rules: {
            block_notes: "required"
        },
        // Specify validation error messages
        messages: {
            block_notes: "Please enter notes",
        }
    });

    $("#block-form").submit(function (event) {
        event.preventDefault();
        blockRoom();
    });

    $.getScript("https://cdn.jsdelivr.net/jquery/latest/jquery.min.js", function () {
        $.getScript("https://cdn.jsdelivr.net/momentjs/latest/moment.min.js", function () {
            $.getScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js", function () {
                const date = new Date();

                $('input[name="block_date"]').daterangepicker({
                    autoApply: false,
                    minDate: date,
                    autoUpdateInput: false,
                }, function (start, end, label) {
                    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                });

                $('input[name="block_date"]').on('apply.daterangepicker', function (event, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    sessionStorage.setItem('blockStartDate', picker.startDate.format("YYYY-MM-DD"));
                    sessionStorage.setItem('blockEndDate', picker.endDate.format("YYYY-MM-DD"));
                });
            });
        });
    });

    $(".filter-block-room-tabs").click(function (event) {
        event.stopImmediatePropagation();
        filterBlockedRoomsTabs(event);
    });
}

function getBlockedRooms() {
    let url = "/api/blockedroom/get";
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("body").removeClass("loading");
            $("#block-list").html(data.html);
            $(".deleteBlockRoom").click(function (event) {
                event.stopImmediatePropagation();
                deleteBlockRoom(event);
            });
        },
        error: function (xhr) {
            $("body").removeClass("loading");
            console.log("request for getBlockedRooms is " + xhr.status);
            if (!isRetry("getBlockedRooms")) {
                return;
            }
            getBlockedRooms();
        }
    });
}

function deleteBlockRoom(event) {
    const id = event.target.id.replace("delete_blocked_", "");
    let url = "/api/blockedroom/delete/" + id;
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (response) {
            const jsonObj = response[0];
            if (jsonObj.result_code === 0) {
                getCalendar("future");
                getBlockedRooms();
            }
        },
        error: function (xhr) {
            console.log("request for deleteBlockRoom is " + xhr.status);
            if (xhr.status > 400) {
                getOverallOccupancy(period, elementId)
            }
        }
    });
}

function filterBlockedRoomsTabs(event) {
    var id = event.currentTarget.id;
    $('.blocked_rooms_tab').addClass("display-none");

    switch (id) {
        case "view_blocked_rooms_tab":
            $('#block-list').removeClass("display-none");
            break;
        case "block_a_room_tab":
            $('#div-block-room').removeClass("display-none");
            break;
        default:
        // code block
    }
}

function blockRoom() {
    const block_room = $("#block_rooms_select").val();
    const block_note = $("#block_notes").val().trim();

    if (block_room.localeCompare("none") === 0) {
        showResErrorMessage("block", "Select Room")
        return;
    }

    if (block_note.length < 1) {
        showResErrorMessage("block", "Please provide notes")
        return;
    }

    $("body").addClass("loading");

    let url = "/api/blockroom/" + block_room + "/" + sessionStorage.getItem("blockStartDate") + "/" + sessionStorage.getItem("blockEndDate") + "/" + block_note;
    $.getJSON(url + "?callback=?", null, function (data) {
        $("body").removeClass("loading");
        const jsonObj = data[0];
        if (jsonObj.result_code === 0) {
            showResSuccessMessage("block", jsonObj.result_message)
            getCalendar("future");
            getBlockedRooms();
        } else {
            showResErrorMessage("block", jsonObj.result_message)
        }

    });

}

function getBlockRooms() {
    getRooms("block_rooms_select");
}
