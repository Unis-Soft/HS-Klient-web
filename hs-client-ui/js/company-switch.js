/* HairSoft Klient V191 - prepinani firem */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  ready(function () {
    var root = document.getElementById("hsCompanySwitchRoot");
    var toggle = document.getElementById("hsCompanySwitchToggle");
    var panel = document.getElementById("hsCompanySwitchPanel");
    var modal = document.getElementById("hsCompanyAddModal");
    var loginField = document.getElementById("hsCompanyLogin");
    var toast = document.getElementById("hsCompanyToast");

    function closePanel() {
      if (!root || !toggle || !panel) return;
      root.classList.remove("is-open");
      toggle.setAttribute("aria-expanded", "false");
      panel.setAttribute("aria-hidden", "true");
    }

    function openPanel() {
      if (!root || !toggle || !panel) return;
      root.classList.add("is-open");
      toggle.setAttribute("aria-expanded", "true");
      panel.setAttribute("aria-hidden", "false");
    }

    function openModal() {
      closePanel();
      if (!modal) return;
      modal.classList.add("is-open");
      modal.setAttribute("aria-hidden", "false");
      document.body.classList.add("hs-company-modal-open");
      window.setTimeout(function () {
        if (loginField) loginField.focus();
      }, 40);
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.remove("is-open");
      modal.setAttribute("aria-hidden", "true");
      document.body.classList.remove("hs-company-modal-open");
    }

    if (toggle) {
      toggle.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (root.classList.contains("is-open")) closePanel();
        else openPanel();
      });
    }

    document.querySelectorAll("[data-hs-company-add]").forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        openModal();
      });
    });

    document.querySelectorAll("[data-hs-company-close]").forEach(function (button) {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        closeModal();
      });
    });

    document.addEventListener("click", function (event) {
      if (root && root.classList.contains("is-open") && !root.contains(event.target)) {
        closePanel();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        closePanel();
        closeModal();
      }
    });

    if (toast) {
      var closeToast = toast.querySelector("[data-hs-toast-close]");
      if (closeToast) {
        closeToast.addEventListener("click", function () {
          toast.remove();
        });
      }
      window.setTimeout(function () {
        if (toast && toast.parentNode) toast.remove();
      }, 6000);
    }
  });
})();
