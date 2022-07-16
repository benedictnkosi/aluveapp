$(document).ready(function() {
	getBlockRooms();

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

	$.getScript("https://cdn.jsdelivr.net/jquery/latest/jquery.min.js", function(){
		$.getScript("https://cdn.jsdelivr.net/momentjs/latest/moment.min.js", function(){
			$.getScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js", function(){
				const date = new Date();

				$('input[name="block_date"]').daterangepicker({
					opens: 'left',
					autoApply:true,
					minDate: date
				}, function(start, end, label) {
					console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
				});
			});
		});
	});
});

function isEmptyOrSpaces(str) {
	return str === null || str.match(/^ *$/) !== null;
}

function blockRoom() {
	const block_date = $("#block_date").val().replaceAll("/","-");
	const block_room = $("#block_rooms_select").val();
	const block_note = $("#block_notes").val().trim();

	if(block_room.localeCompare("none") ===0){
		showResErrorMessage("block", "Select Room")
		return;
	}

	if(block_note.length < 1){
		showResErrorMessage("block", "Please provide notes")
		return;
	}

	$("body").addClass("loading");

	$.post("/api/blockroom/"+ block_room+ "/" + block_date + "/"+ block_note, function(data) {
		$("body").removeClass("loading");
		const jsonObj = data[0];
		if (jsonObj.result_code === 0) {
			showResSuccessMessage("block", jsonObj.result_message)
			getcalendar("future");
			getBlockedRooms();
		} else {
			showResErrorMessage("block", jsonObj.result_message)
		}

	});

}

function getBlockRooms() {
	getRooms("block_rooms_select");
}




