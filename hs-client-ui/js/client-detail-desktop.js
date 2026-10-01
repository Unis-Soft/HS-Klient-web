(function () {
  "use strict";

  var ACTION_ICONS = {
    call: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z"></path></svg>',
    message: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>',
    points: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"></path></svg>'
  };

  function formatPhoneNumber(value) {
    var original = String(value || "").trim();
    if (!/^\+?[\d\s().-]{7,}$/.test(original)) return original;
    var prefix = original.charAt(0) === "+" ? "+" : "";
    var digits = original.replace(/\D/g, "");
    return prefix + digits.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
  }

  function replaceTextCard(panel, type, label, value) {
    if (!panel) return;
    var body = panel.querySelector(".panel-body");
    if (!body) return;

    panel.classList.add("hs-detail-action-card", "hs-detail-action-card--" + type);
    body.textContent = "";

    var icon = document.createElement("span");
    icon.className = "hs-detail-kpi-icon";
    icon.innerHTML = ACTION_ICONS[type];

    var total = document.createElement("span");
    total.className = "total text-center";
    total.textContent = String(value || "");

    var title = document.createElement("span");
    title.className = "title text-center";
    title.textContent = label;

    body.appendChild(icon);
    body.appendChild(total);
    body.appendChild(title);
  }

  function replacePhotoCard(panel, trigger) {
    if (!panel || !trigger) return;
    var body = panel.querySelector(".panel-body");
    if (!body) return;
    var originalCamera = body.querySelector("#FotoaparatKartaOsoby");

    panel.classList.add("hs-detail-action-card", "hs-detail-action-card--camera");
    trigger.classList.add("hs-detail-photo-trigger");
    trigger.setAttribute("aria-label", "Přidat fotografii");
    body.textContent = "";

    var title = document.createElement("span");
    title.className = "title text-center";
    title.textContent = "Přidat fotografii";
    body.appendChild(title);

    if (originalCamera) {
      originalCamera.alt = "";
      originalCamera.setAttribute("aria-hidden", "true");
      body.appendChild(originalCamera);
    }
  }

  function markQuickActions() {
    var cameraLabel = document.getElementById("cameraFileInputLabelDiv");
    var cameraPanel = cameraLabel ? cameraLabel.querySelector(".widget-mini") : null;
    if (!cameraLabel || !cameraPanel || !cameraLabel.parentElement) return;

    var actionGrid = cameraLabel.parentElement;
    var actionRow = actionGrid.closest(".row");
    actionGrid.classList.add("hs-detail-action-grid");
    if (actionRow) actionRow.classList.add("hs-detail-action-row");

    var callPanel = null;
    var messagePanel = null;
    var pointsPanel = null;
    Array.prototype.forEach.call(actionGrid.querySelectorAll(".widget-mini"), function (panel) {
      var wrapper = panel.closest("a, label") || panel.parentElement;
      var href = wrapper && wrapper.tagName === "A" ? (wrapper.getAttribute("href") || "") : "";
      var originalTotal = panel.querySelector(".total");
      var originalLabel = originalTotal ? originalTotal.textContent.replace(/\s+/g, " ").trim().toLowerCase() : "";
      if (href.indexOf("tel:") === 0) callPanel = panel;
      else if (href.indexOf("sms:") === 0 || href.indexOf("mailto:") === 0 || originalLabel === "sms") messagePanel = panel;
      else if (panel.classList.contains("panel-solid-danger")) pointsPanel = panel;
    });

    var callValue = callPanel && callPanel.querySelector(".title") ? callPanel.querySelector(".title").textContent.trim() : "";
    var messageValue = messagePanel && messagePanel.querySelector(".title") ? messagePanel.querySelector(".title").textContent.trim() : "";
    var pointsValue = pointsPanel && pointsPanel.querySelector(".total") ? pointsPanel.querySelector(".total").textContent.trim() : "0";

    if (/^(není zadáno|číslo nezadáno)$/i.test(messageValue)) messageValue = "Číslo nezadáno";

    replacePhotoCard(cameraPanel, cameraLabel);
    replaceTextCard(callPanel, "call", "Volat", formatPhoneNumber(callValue));
    replaceTextCard(messagePanel, "message", "SMS", formatPhoneNumber(messageValue));
    replaceTextCard(pointsPanel, "points", "Bonusové body", pointsValue);
  }

  function moveOptionsBeforeFooter() {
    var mainContent = document.getElementById("main-content");
    if (!mainContent) return;

    var dataManagement = mainContent.querySelector(".hs-client-data-management");
    var optionsRow = dataManagement ? dataManagement.closest(".row") : null;

    /* Kompatibilita se starším markupem před V200. */
    if (!optionsRow) {
      var headings = mainContent.querySelectorAll(".panel-heading .panel-title");
      var optionsPanel = null;
      for (var i = 0; i < headings.length; i += 1) {
        var headingText = headings[i].textContent.replace(/\s+/g, " ").trim();
        if (headingText === "Možnosti") {
          optionsPanel = headings[i].closest(".panel");
          break;
        }
      }
      if (optionsPanel) optionsRow = optionsPanel.closest(".row");
    }

    if (!optionsRow) return;

    var footerBodies = mainContent.querySelectorAll(".panel-body");
    var footerRow = null;
    for (var j = 0; j < footerBodies.length; j += 1) {
      var footerText = footerBodies[j].textContent.replace(/\s+/g, " ").trim();
      if (footerText.indexOf("Strana načtena za") === 0) {
        footerRow = footerBodies[j].closest(".row");
        break;
      }
    }

    if (!footerRow) return;
    var footerBlock = footerRow;
    while (footerBlock && footerBlock.parentElement !== mainContent) footerBlock = footerBlock.parentElement;
    if (!footerBlock || optionsRow.nextElementSibling === footerBlock) return;

    optionsRow.classList.add("hs-detail-options-before-footer");
    mainContent.insertBefore(optionsRow, footerBlock);
  }

  function initialize() {
    var infoTab = document.getElementById("Informace");
    if (!infoTab || !infoTab.querySelector('input[name="akce"][value="edit"]')) return;
    markQuickActions();
    moveOptionsBeforeFooter();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initialize);
  else initialize();
})();
