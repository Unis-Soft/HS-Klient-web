(function () {
  "use strict";

  function createSection(title, description, index, iconPath) {
    var section = document.createElement("section");
    section.className = "hs-client-form-section";

    var header = document.createElement("header");
    header.className = "hs-client-form-section__header";
    header.innerHTML =
      '<span class="hs-client-form-section__icon" aria-hidden="true">' +
        '<svg viewBox="0 0 24 24"><path d="' + iconPath + '"></path></svg>' +
      '</span>' +
      '<span class="hs-client-form-section__copy">' +
        '<strong>' + title + '</strong>' +
        '<small>' + description + '</small>' +
      '</span>' +
      '<span class="hs-client-form-section__step" aria-hidden="true">' + index + '</span>';

    var body = document.createElement("div");
    body.className = "hs-client-form-section__body";
    section.appendChild(header);
    section.appendChild(body);

    return { element: section, body: body };
  }

  function visibleLabels(group) {
    return Array.prototype.filter.call(group.children, function (child) {
      return child.tagName === "LABEL" && child.style.display !== "none";
    });
  }

  function fieldName(group) {
    var field = group.querySelector("[name]");
    return field ? field.getAttribute("name") || "" : "";
  }

  function buildFieldPairs(group) {
    var labels = visibleLabels(group);

    labels.forEach(function (label) {
      var field = label.nextElementSibling;
      if (!field) return;

      var pair = document.createElement("div");
      pair.className = "hs-new-client-field";
      group.insertBefore(pair, label);
      pair.appendChild(label);
      pair.appendChild(field);
    });

    return labels.length;
  }

  function enhanceNewClientForm() {
    var infoTab = document.getElementById("Informace");
    if (!infoTab) return;

    var form = infoTab.closest("form.form-horizontal.form-border");
    if (!form) return;

    var action = form.querySelector('input[name="akce"]');
    if (!action || action.value !== "novy") return;

    var page = infoTab.closest("section.main-content-wrapper");
    var mainContent = document.getElementById("main-content");
    var formBody = infoTab.querySelector(".panel-body");
    var panel = infoTab.closest(".panel.panel-default");
    var tabWrapper = infoTab.closest(".tab-wrapper");
    if (!page || !mainContent || !formBody || !panel || !tabWrapper) return;

    page.classList.add("hs-new-client-ui");
    mainContent.classList.add("hs-new-client-main");
    form.classList.add("hs-new-client-form");
    formBody.classList.add("hs-new-client-form__body");
    panel.classList.add("hs-new-client-panel");
    tabWrapper.classList.add("hs-new-client-tabs");

    var pageTitle = page.querySelector(".pageheader h1");
    var pageDescription = page.querySelector(".pageheader .description");
    if (pageTitle) pageTitle.textContent = "Nový zákazník";
    if (pageDescription) pageDescription.textContent = "Založení nové karty zákazníka";

    var sections = {
      personal: createSection("Osobní a kontaktní údaje", "Základní informace a spojení na zákazníka", "01", "M20 21a8 8 0 0 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8"),
      health: createSection("Zdravotní údaje", "Pojišťovna, rodné číslo a důležité poznámky", "02", "M12 21s-7-4.35-9.33-8.5C.82 8.91 3.09 5 7 5c2.05 0 3.2 1.06 5 3 1.8-1.94 2.95-3 5-3 3.91 0 6.18 3.91 4.33 7.5C19 16.65 12 21 12 21"),
      business: createSection("Firma a komunikace", "Firemní, webové a komunikační údaje", "03", "M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-3h4v3"),
      system: createSection("Systémové údaje", "Historie návštěv a údaje doplňované systémem", "04", "M3 3v18h18M7 15l4-4 3 3 5-7")
    };

    var fragment = document.createDocumentFragment();
    fragment.appendChild(sections.personal.element);
    fragment.appendChild(sections.health.element);
    fragment.appendChild(sections.business.element);
    fragment.appendChild(sections.system.element);
    formBody.appendChild(fragment);

    var originalChildren = Array.prototype.slice.call(formBody.children).filter(function (child) {
      return !child.classList.contains("hs-client-form-section");
    });
    var current = sections.personal;

    originalChildren.forEach(function (child) {
      if (child.tagName === "HR") {
        child.remove();
        return;
      }
      if (!child.classList.contains("form-group")) return;

      var name = fieldName(child);
      if (name === "lidi_zmena") {
        child.remove();
        return;
      }
      if (name === "lidi_hs_rc") current = sections.health;
      if (name === "lidi_hs_firm_name") current = sections.business;
      if (name === "lidi_hs_pocet_navstev") current = sections.system;

      child.classList.add("hs-new-client-row");
      if (child.querySelector("textarea")) child.classList.add("hs-new-client-row--textarea");

      var validation = child.querySelector("#MobilExistuje");
      if (validation && validation.previousElementSibling) {
        validation.classList.add("hs-new-client-validation");
        validation.previousElementSibling.appendChild(validation);
      }

      child.classList.add(buildFieldPairs(child) > 1 ? "hs-new-client-row--double" : "hs-new-client-row--single");

      current.body.appendChild(child);
    });

    var submit = form.querySelector("#UlozitZmenyKartaOsobyButton");
    if (submit) {
      submit.textContent = "Založit zákazníka";
      submit.classList.add("hs-new-client-submit");
      var oldContainer = submit.closest("center");
      var actionBar = document.createElement("div");
      actionBar.className = "hs-new-client-actions";
      actionBar.appendChild(submit);
      formBody.appendChild(actionBar);
      if (oldContainer && !oldContainer.children.length) oldContainer.remove();
    }

    ["lidi_hs_cell", "lidi_hs_phone"].forEach(function (name) {
      var input = form.querySelector('input[name="' + name + '"]');
      if (input) input.setAttribute("inputmode", "tel");
    });
    ["lidi_hs_zip", "lidi_hs_firm_register_number"].forEach(function (name) {
      var input = form.querySelector('input[name="' + name + '"]');
      if (input) input.setAttribute("inputmode", "numeric");
    });

    var firstName = form.querySelector('input[name="lidi_hs_name"]');
    if (firstName && !firstName.disabled && !firstName.readOnly) {
      window.requestAnimationFrame(function () {
        try {
          firstName.focus({ preventScroll: true });
        } catch (error) {
          firstName.focus();
        }
      });
    }

    function updateLayout(width) {
      page.classList.toggle("hs-new-client-ui--compact", width < 820);
      page.classList.toggle("hs-new-client-ui--mobile", width < 620);
    }

    if (typeof ResizeObserver === "function") {
      var observer = new ResizeObserver(function (entries) {
        if (entries[0]) updateLayout(entries[0].contentRect.width);
      });
      observer.observe(mainContent);
    } else {
      updateLayout(mainContent.getBoundingClientRect().width);
      window.addEventListener("resize", function () {
        updateLayout(mainContent.getBoundingClientRect().width);
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", enhanceNewClientForm);
  } else {
    enhanceNewClientForm();
  }
})();
