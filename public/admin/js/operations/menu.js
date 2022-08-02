$(document).ready(function () {
    window.setTimeout(hideLoader, 3000);
    if (sessionStorage.getItem("current_page") === null) {
        updateView('calendar');
    } else {
        updateView(sessionStorage.getItem("current_page"));
    }

    $("#create_invoice_tab").click(function (event) {
        sessionStorage.setItem("property_manager_action", "create");
        $("#submit_create_invoice").prop("value", "Create Invoice");
        //in case it was disabled by stayover and checkout update
        $("#rooms_select").prop('disabled', false);
        $("#checkin_date").prop('disabled', false);
        $("#checkout_date").prop('disabled', false);
        $("#userNumber").prop('disabled', false);
        $("#userName").prop('disabled', false);
    });

    $(".info-input-box").click(function (event) {
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

    $('.nav-links').unbind('click')
    $(".nav-links").click(function (event) {
        event.stopImmediatePropagation();
        $(".headcol").css("position", "absolute");
    });
});

function hideLoader() {
    $("body").removeClass("startup-loading");
    $("body").removeClass("loading");
}

const guid = a => (a ?
    (a ^ ((16 * Math.random()) >> (a / 4))).toString(16) :
    ([1E7] + -1E3 + -4E3 + -8E3 + -1E11).replace(/[018]/g, guid));


function updateView(selectedDiv) {
    //check if javascript loaded for div
    $(".toggleable").addClass("display-none");
    $(".headcol").css("position", "absolute");
    $("#checkbox_toggle").prop("checked", false);
    $("#div-" + selectedDiv).removeClass("display-none");
    sessionStorage.setItem("current_page", selectedDiv);
    loadDataOnMenuClick(selectedDiv);
}

function loadDataOnMenuClick(selectedDiv) {
    switch (selectedDiv) {
        case 'calendar':
            loadCalendarPageData();
            break;
        case 'notifications':
            loadNotificationsPageData();
            break;
        case 'upcoming-reservations':
            loadReservationsPageData();
            break;
        case 'other-tabs':
            loadOccupancyPageData();
            loadCleaningPageData();
            loadBlockedRoomsPageData();
            break;
        case 'configuration':
            loadConfigurationPageData();
            break;
        default:
    }
}


function logout() {
    window.location.href = "/logout";
}



function isRetry(functionName){
    if (sessionStorage.getItem(functionName) === null) {
        sessionStorage.setItem(functionName, "0");
    }
    let count = parseInt(sessionStorage.getItem(functionName));
    count++;
    console.log("calling the request retry -  " +functionName + " - "  + count);
    sessionStorage.setItem(functionName, count.toString());
    if (count > 5) {
        sessionStorage.setItem(functionName, "0");
        return false;
    }

    return true;
}



function isUserLoggedIn() {
    let url =  "/public/userloggedin/";
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if(data.logged_in.localeCompare("false") === 0){
                logout()
            }
        }
    });
}