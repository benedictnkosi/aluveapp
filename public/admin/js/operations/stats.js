$(document).ready(function () {
    console.log("ready!");
    //get day of month
    var d = new Date();
    var date = d.getDate();


    $(".glyphicon-log-in").click(function () {
        $([document.documentElement, document.body]).animate({
            scrollTop: $("#reservations-list").offset().top
        }, 2000);
    });

    $(".glyphicon-log-out").click(function () {
        $([document.documentElement, document.body]).animate({
            scrollTop: $("#checkouts-list").offset().top
        }, 2000);
    });

    $(".glyphicon-briefcase").click(function () {
        $([document.documentElement, document.body]).animate({
            scrollTop: $("#stayOver-list").offset().top
        }, 2000);
    });


    $('#occupancy_days').click(function (event) {
        sessionStorage.setItem("occupancy_days", event.target.value )
    });

    $('#occupancy_days').blur(function (event) {
        const savedOccupancyDays = sessionStorage.getItem("occupancy_days");
        if(savedOccupancyDays.localeCompare($('#occupancy_days').val()) !== 0 &&  !isNaN($('#occupancy_days').val())){
            const days = $('#occupancy_days').val();
            getOverallOccupancy(days, "overall-30-occupancy");
            getOccupancyPerRoom(days);
        }
    });

});

function loadOccupancyPageData() {
    var d = new Date();
    var date = d.getDate();
    getOverallOccupancy("30", "overall-30-occupancy");
    getOverallOccupancy(date, "overall-month-occupancy");
    getOccupancyPerRoom("30");
}

function getcheckins(period) {
    let url = "/api/stats/getreservationcount/checkIn/" + period;
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            const jsonObj = data[0];
            if (jsonObj.result_code === 0) {
                $('#stats_checkin_count').text(data[0].count);
            }
        },
        error: function (xhr) {
            console.log("request for getcheckins is " + xhr.status);
            if (xhr.status > 400) {
                getcheckins();
            }
        }
    });
}

function getcheckouts(period) {
    let url = "/api/stats/getreservationcount/checkOut/" + period;

    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            const jsonObj = data[0];
            if (jsonObj.result_code === 0) {
                $('#stats_checkout_count').text(data[0].count);
            }
        },
        error: function (xhr) {
            console.log("request for getcheckouts is " + xhr.status);
            if (xhr.status > 400) {
                getcheckouts(period);
            }
        }
    });
}

function getstayovers(period) {
    let url = "/api/stats/getstayovercount/" + period;
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            const jsonObj = data[0];
            if (jsonObj.result_code === 0) {
                $('#stats_overstays_count').text(data[0].count);
            }
        },
        error: function (xhr) {
            console.log("request for getstayovers is " + xhr.status);
            if (xhr.status > 400) {
                getstayovers(period);
            }
        }
    });
}


function getOverallOccupancy(period, elementId) {
    let url = "/api/occupancy/" + period;
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            const jsonObj = data[0];
            if (jsonObj.result_code === 0) {
                $('#' + elementId).text(jsonObj.occupancy);
            }
        },
        error: function (xhr) {
            console.log("request for getOverallOccupancy is " + xhr.status);
            if (!isRetry("getOverallOccupancy")) {
                return;
            }
            getOverallOccupancy(period, elementId);
        }
    });

}

function getOccupancyPerRoomForMonth() {
    var myDate = new Date();
    var dayOfmonth = myDate.getDate();
    getOccupancyPerRoom(dayOfmonth)
}


function getOccupancyPerRoom(period) {
    let url = "/api/occupancy/perroom/" + period;
    $.ajax({
        type: "GET",
        url: url,
        processData: true,
        data: {},
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Headers": "origin, content-type, accept"
        },
        dataType: "jsonp",
        success: function (data) {
            $("body").removeClass("loading");
            $("#occupancy-div").html(data);
        },
        error: function (xhr) {
            $("body").removeClass("loading");
            console.log("request for getOccupancyPerRoom is " + xhr.status);
            if (!isRetry("getOccupancyPerRoom")) {
                return;
            }
            getOccupancyPerRoom(period);
        }
    });
}




