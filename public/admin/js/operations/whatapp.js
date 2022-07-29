$(document).ready(function() {

	$("#formStartChat").submit(function(event) {

		event.preventDefault();

		window.open(
			"https://api.whatsapp.com/send?phone=+27 " + $('#whatapp_chat').val().replace('+27', '0') + "&text=Hello, this is Aluve Guesthouse :)",
			'_blank' // <- This is what makes it open in a new window.
		);

	});

});

