(function () {
  "use strict";

  var TAB_ICONS = {
    "#Informace": '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>',
    "#Timeline": '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>',
    "#SMSChat": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>',
    "#Galerie": '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-5-5L5 20"></path></svg>',
    "#Programy": '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"></rect><path d="M9 8h6M9 12h6M9 16h4"></path></svg>',
    "#Hodnoceni": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2 2 9.3l6.9-1z"></path></svg>',
    "#Soubory": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>'
  };

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function createElement(tagName, className) {
    var element = document.createElement(tagName);
    if (className) element.className = className;
    return element;
  }

  function makeIcon(className, svgMarkup) {
    var icon = createElement("span", className);
    icon.innerHTML = svgMarkup;
    return icon;
  }

  function buildProfileCard(photo, fullName, profileLabel) {
    var card = createElement("section", "hs-client-profile-card");
    var cover = createElement("div", "hs-client-profile-card__cover");
    var content = createElement("div", "hs-client-profile-card__content");
    var photoStage = createElement("div", "hs-client-profile-card__photo");
    var label = createElement("span", "hs-client-profile-card__label");
    var name = createElement("strong", "hs-client-profile-card__name");

    label.textContent = profileLabel;
    name.textContent = fullName || "—";
    photoStage.appendChild(photo);
    content.appendChild(photoStage);
    content.appendChild(label);
    content.appendChild(name);
    card.appendChild(cover);
    card.appendChild(content);
    return card;
  }

  function buildNoteCard(noteWrapper, noteLabel) {
    var card = createElement("section", "hs-client-note-card");
    var header = createElement("div", "hs-client-note-card__header");
    var title = createElement("strong", "hs-client-note-card__title");
    var preview = createElement("span", "hs-client-note-card__preview");
    var body = createElement("div", "hs-client-note-card__body");
    var textarea = noteWrapper.tagName === "TEXTAREA" ? noteWrapper : noteWrapper.querySelector(".textareaLidi");
    var iconMarkup = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path></svg>';
    var toggleMarkup = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"></path></svg>';

    title.textContent = noteLabel;
    preview.textContent = textarea && textarea.value.trim() ? textarea.value.replace(/\s+/g, " ").trim() : translate("Bez poznámky");
    header.appendChild(makeIcon("hs-client-note-card__icon", iconMarkup));
    header.appendChild(title);
    header.appendChild(preview);
    header.appendChild(makeIcon("hs-client-note-card__toggle", toggleMarkup));
    body.appendChild(noteWrapper);
    var actions = createElement("div", "hs-client-note-card__actions");
    var save = createElement("button", "hs-client-note-card__save");
    save.type = "submit";
    if (window.hsSetTranslatedText) window.hsSetTranslatedText(save, "Uložit");
    else save.textContent = translate("Uložit");
    actions.appendChild(save);
    body.appendChild(actions);

    card.appendChild(header);
    card.appendChild(body);
    bindNoteToggle(card, header);
    return card;
  }

  function bindNoteToggle(card, header) {
    var textarea = card.querySelector(".textareaLidi");
    var preview = card.querySelector(".hs-client-note-card__preview");

    function isMobile() {
      return window.matchMedia ? window.matchMedia("(max-width: 767px)").matches : window.innerWidth <= 767;
    }

    function resizeTextarea() {
      var fullHeight;
      var targetHeight;

      if (!textarea || !isMobile()) return;
      textarea.style.setProperty("height", "0px", "important");
      fullHeight = textarea.scrollHeight;
      targetHeight = Math.max(46, Math.min(fullHeight, 220));
      textarea.style.setProperty("height", targetHeight + "px", "important");
      textarea.style.setProperty("overflow-y", fullHeight > targetHeight ? "auto" : "hidden", "important");
    }

    function resetTextarea() {
      if (!textarea) return;
      textarea.style.removeProperty("height");
      textarea.style.removeProperty("overflow-y");
    }

    function setExpanded(expanded) {
      card.classList.toggle("hs-client-note-card--open", expanded);
      header.setAttribute("aria-expanded", expanded ? "true" : "false");
      if (expanded) window.requestAnimationFrame(resizeTextarea);
    }

    function toggleNote(event) {
      if (event.type === "keydown" && event.key !== "Enter" && event.key !== " ") return;
      if (event.type === "keydown") event.preventDefault();
      setExpanded(!card.classList.contains("hs-client-note-card--open"));
    }

    function updatePreview() {
      var text;
      if (!preview || !textarea) return;
      text = textarea.value.replace(/\s+/g, " ").trim();
      preview.textContent = text || translate("Bez poznámky");
    }

    function updateMode() {
      var mobile = isMobile();
      header.setAttribute("role", "button");
      header.setAttribute("tabindex", "0");
      header.setAttribute("aria-expanded", card.classList.contains("hs-client-note-card--open") ? "true" : "false");
      if (mobile && card.classList.contains("hs-client-note-card--open")) window.requestAnimationFrame(resizeTextarea);
      if (!mobile) resetTextarea();
    }

    header.addEventListener("click", toggleNote);
    header.addEventListener("keydown", toggleNote);
    if (textarea) textarea.addEventListener("input", function () {
      resizeTextarea();
      updatePreview();
    });
    window.addEventListener("resize", updateMode);
    updateMode();
  }

  function addMobileHeaderPhoto(photo) {
    var pageHeader = document.querySelector("#main-wrapper > section.main-content-wrapper > .pageheader");
    var mobilePhoto;
    var photoClone;

    if (!pageHeader || pageHeader.querySelector(".hs-client-mobile-header-photo")) return;

    mobilePhoto = createElement("div", "hs-client-mobile-header-photo");
    mobilePhoto.setAttribute("aria-hidden", "true");
    photoClone = photo.cloneNode(true);
    mobilePhoto.appendChild(photoClone);
    pageHeader.classList.add("hs-client-header-with-photo");
    pageHeader.appendChild(mobilePhoto);
  }

  function enhanceNavigation(nav) {
    nav.classList.add("hs-client-section-nav");
    Array.prototype.forEach.call(nav.querySelectorAll("a[data-toggle=\"tab\"]"), function (link) {
      var target = link.getAttribute("href") || "";
      var iconMarkup = TAB_ICONS[target];
      if (!iconMarkup || link.querySelector(".hs-client-section-nav__icon")) return;

      var labelText = link.textContent.replace(/\s+/g, " ").trim();
      link.textContent = "";
      link.appendChild(makeIcon("hs-client-section-nav__icon", iconMarkup));
      var label = createElement("span", "hs-client-section-nav__label");
      label.textContent = labelText;
      link.appendChild(label);
    });
  }

  function createInformationSection(title, description, index, iconPath) {
    var section = createElement("section", "hs-client-form-section");
    var header = createElement("header", "hs-client-form-section__header");
    header.innerHTML =
      '<span class="hs-client-form-section__icon" aria-hidden="true">' +
        '<svg viewBox="0 0 24 24"><path d="' + iconPath + '"></path></svg>' +
      '</span>' +
      '<span class="hs-client-form-section__copy">' +
        '<strong>' + title + '</strong>' +
        '<small>' + description + '</small>' +
      '</span>' +
      '<span class="hs-client-form-section__step" aria-hidden="true">' + index + '</span>';

    var body = createElement("div", "hs-client-form-section__body");
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

  function buildInformationFieldPairs(group) {
    var labels = visibleLabels(group);
    labels.forEach(function (label) {
      var field = label.nextElementSibling;
      if (!field) return;

      var pair = createElement("div", "hs-new-client-field");
      group.insertBefore(pair, label);
      pair.appendChild(label);
      pair.appendChild(field);
    });
    return labels.length;
  }

  function bindInformationLayout(page, detailColumn, tabWrapper, nav, infoTab) {
    var programsTab = document.getElementById("Programy");

    function updateShell() {
      tabWrapper.classList.toggle("hs-client-detail-tabs--information-active", infoTab.classList.contains("active"));
      tabWrapper.classList.toggle("hs-client-detail-tabs--programs-active", !!programsTab && programsTab.classList.contains("active"));
    }

    function updateWidth(width) {
      page.classList.toggle("hs-new-client-ui--compact", width < 820);
      page.classList.toggle("hs-new-client-ui--mobile", width < 620);
    }

    nav.addEventListener("click", function (event) {
      if (event.target.closest('a[data-toggle="tab"]')) window.setTimeout(updateShell, 0);
    });
    updateShell();

    if (typeof ResizeObserver === "function") {
      var observer = new ResizeObserver(function (entries) {
        if (entries[0]) updateWidth(entries[0].contentRect.width);
      });
      observer.observe(detailColumn);
    } else {
      updateWidth(detailColumn.getBoundingClientRect().width);
      window.addEventListener("resize", function () {
        updateWidth(detailColumn.getBoundingClientRect().width);
      });
    }
  }

  function buildInformationSections(infoTab, detailColumn, tabWrapper, nav) {
    var formBody = infoTab.querySelector(".panel-body");
    if (!formBody || formBody.classList.contains("hs-client-edit-info-body")) return;
    var page = infoTab.closest("section.main-content-wrapper");
    if (!page) return;

    page.classList.add("hs-new-client-ui");
    infoTab.classList.add("hs-client-edit-information");
    formBody.classList.add("hs-client-edit-info-body", "hs-new-client-form__body");

    var sections = {
      personal: createInformationSection("Osobní a kontaktní údaje", "Základní informace a spojení na zákazníka", "01", "M20 21a8 8 0 0 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8"),
      health: createInformationSection("Zdravotní údaje", "Pojišťovna, rodné číslo a důležité poznámky", "02", "M12 21s-7-4.35-9.33-8.5C.82 8.91 3.09 5 7 5c2.05 0 3.2 1.06 5 3 1.8-1.94 2.95-3 5-3 3.91 0 6.18 3.91 4.33 7.5C19 16.65 12 21 12 21"),
      business: createInformationSection("Firma a komunikace", "Firemní, webové a komunikační údaje", "03", "M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-3h4v3"),
      system: createInformationSection("Systémové údaje", "Historie návštěv a údaje doplňované systémem", "04", "M3 3v18h18M7 15l4-4 3 3 5-7")
    };

    var originalChildren = Array.prototype.slice.call(formBody.children);
    var current = sections.personal;
    originalChildren.forEach(function (child) {
      if (child.tagName === "HR") {
        child.remove();
        return;
      }
      if (!child.classList.contains("form-group")) return;

      var name = fieldName(child);
      if (name === "lidi_hs_rc") current = sections.health;
      if (name === "lidi_hs_firm_name") current = sections.business;
      if (name === "lidi_hs_pocet_navstev") current = sections.system;

      var validation = child.querySelector("#MobilExistuje");
      if (validation && validation.previousElementSibling) {
        validation.classList.add("hs-new-client-validation");
        validation.previousElementSibling.appendChild(validation);
      }

      child.classList.add("hs-new-client-row");
      if (child.querySelector("textarea")) child.classList.add("hs-new-client-row--textarea");
      child.classList.add(buildInformationFieldPairs(child) > 1 ? "hs-new-client-row--double" : "hs-new-client-row--single");
      current.body.appendChild(child);
    });

    Object.keys(sections).forEach(function (key) {
      formBody.appendChild(sections[key].element);
    });

    var submit = infoTab.querySelector("#UlozitZmenyKartaOsobyButton");
    if (submit) {
      submit.classList.add("hs-new-client-submit");
      var oldContainer = submit.closest("center");
      var actionBar = createElement("div", "hs-new-client-actions");
      actionBar.appendChild(submit);
      formBody.appendChild(actionBar);
      if (oldContainer && !oldContainer.children.length) oldContainer.remove();
    }

    ["lidi_hs_cell", "lidi_hs_phone"].forEach(function (name) {
      var input = infoTab.querySelector('input[name="' + name + '"]');
      if (input) input.setAttribute("inputmode", "tel");
    });
    ["lidi_hs_zip", "lidi_hs_firm_register_number"].forEach(function (name) {
      var input = infoTab.querySelector('input[name="' + name + '"]');
      if (input) input.setAttribute("inputmode", "numeric");
    });

    bindInformationLayout(page, detailColumn, tabWrapper, nav, infoTab);
  }

  function initializeProfileWorkspace() {
    var infoTab = document.getElementById("Informace");
    if (!infoTab || !infoTab.querySelector('input[name="akce"][value="edit"]')) return;

    var tabWrapper = infoTab.closest(".tab-wrapper");
    var detailColumn = tabWrapper ? tabWrapper.closest('[class*="col-md-"]') : null;
    var nav = tabWrapper ? tabWrapper.querySelector(".nav-tabs") : null;
    var note = document.querySelector('textarea[name="lidi_hs_note"]');
    var noteWrapper = note ? (note.closest(".textareaLidiObal") || note) : null;
    var profileColumn = note ? note.closest(".min350") : null;
    var oldPanel = profileColumn ? profileColumn.querySelector(".panel") : null;
    var photo = oldPanel ? oldPanel.querySelector(".OrizlaKulataFotkaObal") : null;
    var workspace = profileColumn ? profileColumn.parentElement : null;

    if (!tabWrapper || !detailColumn || !nav || !noteWrapper || !profileColumn || !oldPanel || !photo || !workspace) return;

    var firstName = infoTab.querySelector('input[name="lidi_hs_name"]');
    var lastName = infoTab.querySelector('input[name="lidi_hs_surname"]');
    var fullName = [firstName ? firstName.value.trim() : "", lastName ? lastName.value.trim() : ""].filter(Boolean).join(" ");
    var oldHeadings = oldPanel.querySelectorAll(".panel-heading .panel-title");
    var profileLabel = oldHeadings[0] ? oldHeadings[0].textContent.replace(/\s+/g, " ").trim() : "Profilovka";
    var noteLabel = oldHeadings[1] ? oldHeadings[1].textContent.replace(/\s+/g, " ").trim() : "Poznámka";

    document.body.classList.add("hs-client-profile-ui");
    workspace.classList.add("hs-client-detail-workspace");
    profileColumn.classList.add("hs-client-profile-sidebar");
    detailColumn.classList.add("hs-client-detail-tabs-column");
    tabWrapper.classList.add("hs-client-detail-tabs");
    if (workspace.closest("form")) workspace.closest("form").classList.add("hs-client-detail-form");

    enhanceNavigation(nav);
    buildInformationSections(infoTab, detailColumn, tabWrapper, nav);
    var profileCard = buildProfileCard(photo, fullName, profileLabel);
    var noteCard = buildNoteCard(noteWrapper, noteLabel);

    addMobileHeaderPhoto(photo);

    while (profileColumn.firstChild) profileColumn.removeChild(profileColumn.firstChild);
    profileColumn.appendChild(profileCard);
    profileColumn.appendChild(nav);
    profileColumn.appendChild(noteCard);
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initializeProfileWorkspace);
  else initializeProfileWorkspace();
})();
