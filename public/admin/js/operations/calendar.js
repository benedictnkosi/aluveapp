$(document).ready(function() {

});

function loadCalendarPageData(){
	getCalendar();
}

function getCalendar() {
	$("body").addClass("loading");
	let url =  "/api/calendar/";
	$.ajax({
		type: "get",
		url: url,
		crossDomain: true,
		cache: false,
		dataType: "jsonp",
		contentType: "application/json; charset=UTF-8",
		success: function (data) {
			$("body").removeClass("loading");
			$("#calendar-table").html(data.html);
			$('.open-reservation-details').unbind('click')
			$(".open-reservation-details").click(function (event) {
				event.stopImmediatePropagation();
				getReservationById(event.target.getAttribute("data-res-id"));
			});
		},
		error: function (xhr) {
			$("body").removeClass("loading");
			console.log("request for getCalendar is " + xhr.status);
			if (!isRetry("getCalendar")) {
				return;
			}
			getCalendar();
		}
	});
}


