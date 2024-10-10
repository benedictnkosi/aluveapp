function getUrlParameter(sParam) {
  let sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split("&"),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split("=");

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined
        ? true
        : decodeURIComponent(sParameterName[1]);
    }
  }
  return false;
}

function showBackToReservationsLink() {
  let url = "/noauth/me";
  $.get(url, function (data) {
    isLoggedin = data[0].authenticated;
    sessionStorage.setItem("authenticated", isLoggedin);
    if (isLoggedin) {
      $(".loginBtn").addClass("hidden");
      $(".signUpBtn").addClass("hidden");
      $("#backToReservations").removeClass("hidden");
    }
  });
}

function showResErrorMessage(divName, message) {
  $("body").removeClass("loading");
  $("body").removeClass("startup-loading");
  $("#" + divName + "_error_message_div").removeClass("display-none");
  $("#" + divName + "_error_message").text(message);
  $("#" + divName + "_success_message_div").addClass("display-none");
  $([document.documentElement, document.body]).animate(
    {
      scrollTop: $("#" + divName + "_error_message_div").offset().top - 100,
    },
    2000
  );
}

function showResSuccessMessage(divName, message) {
  $("body").removeClass("loading");
  $("body").removeClass("startup-loading");
  $("#" + divName + "_error_message_div").addClass("display-none");
  $("#" + divName + "_success_message").text(message);
  $("#" + divName + "_success_message_div").removeClass("display-none");
  $([document.documentElement, document.body]).animate(
    {
      scrollTop: $("#" + divName + "_error_message_div").offset().top,
    },
    2000
  );
}
