$(document).ready(function() {
	$('#refresh_calendar_button').unbind('click')
	$("#refresh_calendar_button").click(function (event) {
		getCalendar();
	});

	$("#refresh_rooms_not_cleaned_button").click(function (event) {
		getRoomsNotCleaned();
	});
});

function loadCalendarPageData(){
	getCalendar();
	getPropertyUid();
	getRoomsNotCleaned();
}

function getCalendar() {
	isUserLoggedIn();
	$("body").addClass("loading");
	let url =  "/api/calendar";
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


function getRoomsNotCleaned() {
	isUserLoggedIn();
	$("body").addClass("loading");
	let url =  "/api/outstandingcleanings/today";
	$.ajax({
		type: "get",
		url: url,
		crossDomain: true,
		cache: false,
		dataType: "jsonp",
		contentType: "application/json; charset=UTF-8",
		success: function (data) {
			$("body").removeClass("loading");
			$("#not-cleaned-rooms-table").html(data.html);
		},
		error: function (xhr) {
			$("body").removeClass("loading");
			console.log("request for getRoomsNotCleaned is " + xhr.status);
			if (!isRetry("getRoomsNotCleaned")) {
				return;
			}
			getRoomsNotCleaned();
		}
	});
}