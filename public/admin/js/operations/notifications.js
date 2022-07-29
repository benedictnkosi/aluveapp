$(document).ready(function() {

});

function loadNotificationsPageData(){
	getNotifications();
}

function getNotifications() {
	$("body").addClass("loading");
	let url =   "/api/notifications" + "/";
	$.ajax({
		type: "get",
		url: url,
		crossDomain: true,
		cache: false,
		dataType: "jsonp",
		contentType: "application/json; charset=UTF-8",
		success: function (data) {
			$("body").removeClass("loading");
			$("#notifications-list").html(data.html);
			const numNotifications = $('.notification_message').length;
			if(numNotifications > 0){
				$("#notification_count").text(numNotifications);
				$("#notification_count").addClass("badge-red");
				$("#notification_count").removeClass("badge-green");
			}else{
				$("#notification_count").text("0");
				$("#notification_count").removeClass("badge-red");
				$("#notification_count").addClass("badge-green");
			}
		},
		error: function (xhr) {
			$("body").removeClass("loading");
			console.log("request for getNotifications is " + xhr.status);
			if (!isRetry("getNotifications")) {
				return;
			}
			getNotifications();
		}
	});
}