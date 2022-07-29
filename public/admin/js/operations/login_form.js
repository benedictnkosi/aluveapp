$(document).ready(function() {
	$("#login-form").submit(function(event) {
		event.preventDefault();
		login()
	});
});

function isEmptyOrSpaces(str) {
	return str === null || str.match(/^ *$/) !== null;
}



