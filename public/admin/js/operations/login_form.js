$(document).ready(function() {
	$("#login-form").submit(function(event) {
		event.preventDefault();
		login()
	});
});

function isEmptyOrSpaces(str) {
	return str === null || str.match(/^ *$/) !== null;
}

function register() {

	const customerName = $('#customerName').val();
	const email = $('#email').val();
	const phoneNumber = $('#phoneNumber').val();
	const hotel_name = $('#hotel_name').val();

	let url = "/public/sales/trial/" + customerName + "/" + email + "/" + phoneNumber + "/" + hotel_name;
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

