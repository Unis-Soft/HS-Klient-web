(function () {
  "use strict";

  function initReservationsEmbed() {
    var frame = document.querySelector("[data-hs-reservations-frame]");
    var wrap = document.querySelector("[data-hs-reservations-wrap]");
    var reloadButton = document.querySelector("[data-hs-reservations-reload]");

    if (!frame || !wrap) {
      return;
    }

    function showLoading() {
      wrap.classList.add("is-loading");
    }

    function hideLoading() {
      wrap.classList.remove("is-loading");
    }

    frame.addEventListener("load", hideLoading);

    if (reloadButton) {
      reloadButton.addEventListener("click", function () {
        showLoading();
        frame.src = frame.getAttribute("src");
      });
    }

    window.setTimeout(function () {
      hideLoading();
    }, 15000);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initReservationsEmbed);
  } else {
    initReservationsEmbed();
  }
})();
