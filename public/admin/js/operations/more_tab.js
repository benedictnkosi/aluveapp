$(document).ready(function () {
    //other tabs
    $('.filter-other-tabs').unbind('click')
    $(".filter-other-tabs").click(function (event) {
        event.stopImmediatePropagation();
        filterOtherTabs(event);
    });
});

function filterOtherTabs(event) {
    var id = event.currentTarget.id;
    $('.other_feature_tab').addClass("display-none");

    switch (id) {
        case "view_blocked_rooms_tab":
            $('#div-blocked-rooms').removeClass("display-none");
            break;
        case "block_a_room_tab":
            $('#div-block-room').removeClass("display-none");
            break;
        case "cleaning_tab":
            $('#div-cleaning').removeClass("display-none");
            break;
        case "whatsapp_tab":
            $('#div-whatapp-chat').removeClass("display-none");
            break;
        case "occupancy_tab":
            $('#div-occupancy').removeClass("display-none");
            break;
        default:
        // code block
    }
}
