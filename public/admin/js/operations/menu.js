
$(document).ready(function() {
	if (sessionStorage.getItem("current_page") === null) {
		updateView(sessionStorage.getItem("calendar"));
	} else {
		updateView(sessionStorage.getItem("current_page"));
	}

	$("#create_invoice_tab").click(function(event) {
		localStorage.setItem("property_manager_action", "create");
		$("#submit_create_invoice").prop("value", "Create Invoice");
		//in case it was disabled by stayover and checkout update
		$("#rooms_select").prop('disabled', false);
		$("#checkin_date").prop('disabled', false);
		$("#checkout_date").prop('disabled', false);
		$("#userNumber").prop('disabled', false);
		$("#userName").prop('disabled', false);
	});

	$(".info-input-box").click(function(event) {
		getPage();
		var copyText = event.target;
		/* Select the text field */
		copyText.select();
		copyText.setSelectionRange(0, 99999); /* For mobile devices */

		/* Copy the text inside the text field */
		document.execCommand("copy");

		var text = document.createTextNode("Copied");
		copyText.parentNode.insertBefore(text, copyText.nextSibling)

	});

});

const guid = a => (a ?
    (a ^ ((16 * Math.random()) >> (a / 4))).toString(16) :
    ([1E7] + -1E3 + -4E3 + -8E3 + -1E11).replace(/[018]/g, guid));


function updateView(selectedDiv) {
	$(".toggleable").addClass("display-none");
	$("#div-" + selectedDiv).removeClass("display-none");
	sessionStorage.setItem("current_page", selectedDiv);
	toggleMenu();
}

function logout(){
	$("body").addClass("loading");

	$.post("/api/logout" , function(data) {
		$("body").removeClass("loading");
		if (data[0].result_code === 0) {
			delete_cookie( 'PROPERTY_ID');
			window.location.href = "/admin/true";
		}
	});
}
function delete_cookie( name ) {
	document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}