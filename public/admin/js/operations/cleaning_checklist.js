$(document).ready(function() {
	getRooms("cleaning_rooms_select")
	$("#cleaning_rooms_select").change(function(event) {
		getCleaning(event.target.value);
	});
});

function getCleaning(room) {
	$("#cleaning-list").load("/api/cleanings/"+room, function() {
	});
}


