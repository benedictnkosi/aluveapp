$(document).ready(function () {
    getNotifications();
});


function getNotifications() {
    $("body").addClass("loading");
    let url = "/api/notifications";
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("body").removeClass("loading");

            for (let i = 0; i < data.length; i++){
                const notification = data[i];
                console.log("Notification name " + notification["name"]);
                addNotificationToBody(notification["name"], notification["message"], notification["name"], notification["link"]);
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

function addNotificationToBody(notificationName, message, linkText, url) {
    $('.notifications_alerts').remove();
    let linkHtml = ""
    if(url.length > 0){
        linkHtml = ' <a href="'+url+'" target="_blank" >'+linkText+'</a>';
    }

    const notificationHtml = '<div class="alert warning notifications_alerts">\n' +
        '    <span class="closebtn close_notification" data-notification="' + notificationName + '">Done</span>\n' +
        '    <strong>Warning! </strong>' + message + linkHtml +
        '</div>';

    $(notificationHtml).appendTo('#notifications-div');

    $(".close_notification").click(function(event){
        markNotificationAsActioned(event)

    });

}

function markNotificationAsActioned(event) {
    let confirm = prompt('Please enter "yes" to confirm'  , "");
    if (confirm == null) {
        return
    }

    if(confirm.localeCompare("yes") !== 0){
        return
    }
    let notificationName = event.target.getAttribute('data-notification');

    $("body").addClass("loading");
    let url = "/api/notifications/action/" + notificationName;
    $.ajax({
        type: "get",
        url: url,
        crossDomain: true,
        cache: false,
        dataType: "jsonp",
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("body").removeClass("loading");
            $(event.target).parent().remove();
        },
        error: function (xhr) {
            $("body").removeClass("loading");
            console.log("request for markNotificationAsActioned is " + xhr.status);
            if (!isRetry("markNotificationAsActioned")) {
                return;
            }
            markNotificationAsActioned();
        }
    });
}
