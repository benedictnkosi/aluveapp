$(document).ready(function () {
  $("#birdview_form").submit(function (event) {
    event.preventDefault();
    getBirdView();
  });

  $("#properties_form").submit(function (event) {
    event.preventDefault();
    getProperties();
  });

  $("#exclude_location").val(localStorage.getItem("exclude_location"));
  $("body").removeClass("loading");
});

function getBirdView() {
  let searchType = $("#search_type").val();
  let percentage = $("#percentage").val();
  let bedrooms = $("#bedrooms").val();
  let bathrooms = $("#bathrooms").val();
  let erf = $("#erf").val();
  $("body").addClass("loading");
  let url =
    "/noauth/birdview/" +
    searchType +
    "/" +
    percentage +
    "/" +
    bedrooms +
    "/" +
    bathrooms +
    "/" +
    erf;

  $.get(url, function (response) {
    $("#bird_view_summary_table").html(response[0].html);
    $("body").removeClass("loading");
  });
}

function getProperties() {
  let searchType = $("#search_type").val();
  let percentage = $("#percentage").val();
  let bedrooms = $("#bedrooms").val();
  let bathrooms = $("#bathrooms").val();
  let excludeLocation = $("#exclude_location").val();
  localStorage.setItem("exclude_location", excludeLocation);
  let erf = $("#erf").val();
  $("body").addClass("loading");
  let url =
    "/noauth/properties/" +
    searchType +
    "/" +
    percentage +
    "/" +
    bedrooms +
    "/" +
    bathrooms +
    "/" +
    erf +
    "/" +
    excludeLocation;

  $.get(url, function (response) {
    $("#property_table").html(response[0].html);
    $("body").removeClass("loading");
    var sortableTables = document.querySelectorAll("table.sortable");
    for (var i = 0; i < sortableTables.length; i++) {
      new SortableTable(sortableTables[i]);
    }
    $(".analyse_property").click(function (event) {
      event.preventDefault();
      let propertyUrl = event.target.getAttribute("data-property-link");
      sessionStorage.setItem("propertyUrl", propertyUrl);
      window.open(event.target.getAttribute("data-href"), "_blank").focus();
    });

    $(".delete_property").click(function (event) {
      event.preventDefault();
      let propertyId = event.target.getAttribute("data-property-id");
      let url = "/noauth/properties/delete/" + propertyId;
      $(this).closest("tr").remove();
      $.get(url, function () {});
    });

    $(".state-dropdown").change(function (event) {
      event.stopImmediatePropagation();
      let propertyId = event.target.getAttribute("data-property-id");
      let optionSelected = $(this).children("option:selected").val();
      let url = "/noauth/properties/state/" + propertyId + "/" + optionSelected;
      $.get(url, function () {});
    });
  });
}
