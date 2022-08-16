$(document).ready(function () {
    $("#birdview_form").submit(function(event) {
        event.preventDefault();
        getBirdView();
    });

    $("#properties_form").submit(function(event) {
        event.preventDefault();
        getProperties();
    });
    $("body").removeClass("loading");
});


function getBirdView() {
    let searchType = $('#search_type').val();
    let percentage = $('#percentage').val();
    let bedrooms = $('#bedrooms').val();
    let bathrooms = $('#bathrooms').val();
    let erf = $('#erf').val();
    $("body").addClass("loading");
    let url = "/public/birdview/"+searchType+"/" + percentage+"/" + bedrooms+"/" + bathrooms+"/" + erf;

    $.get( url, function( response ) {
        $('#bird_view_summary_table').html(response[0].html);
        $("body").removeClass("loading");
    });
}

function getProperties() {
    let searchType = $('#search_type').val();
    let percentage = $('#percentage').val();
    let bedrooms = $('#bedrooms').val();
    let bathrooms = $('#bathrooms').val();
    let erf = $('#erf').val();
    $("body").addClass("loading");
    let url = "/public/properties/"+searchType+"/" + percentage+"/" + bedrooms+"/" + bathrooms+"/" + erf;

    $.get( url, function( response ) {
        $('#property_table').html(response[0].html);
        $("body").removeClass("loading");
    });
}