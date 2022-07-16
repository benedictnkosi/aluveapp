$(document).ready(function() {
	$("#login-form").submit(function(event) {
		event.preventDefault();
		login()
	});
});

function isEmptyOrSpaces(str) {
	return str === null || str.match(/^ *$/) !== null;
}

function setCookie(name, value) {
	//expires in one hour
	var now = new Date();
	now.setTime(now.getTime() + 1 * 3600 * 1000);
	document.cookie = name + '=' + value + '; ' + now.toUTCString() + '; path=/';
}



function login() {
	if (isEmptyOrSpaces($("#secret").val())) {
		showResErrorMessage("login", "Please enter pin")
		return;
	}

	$("#login_error_message_div").addClass("display-none");
	$("#login_success_message_div").addClass("display-none");

	$("body").addClass("loading");

	$.post("/api/login/" + $("#secret").val(), function(data) {
		$("body").removeClass("loading");
		if (data[0].result_code === 0) {
			setCookie("PROPERTY_ID", data[0].property_id);
			window.location.href = "/admin/true";
		} else {
			showResErrorMessage("login", data[0].result_message)
		}
	});

}


