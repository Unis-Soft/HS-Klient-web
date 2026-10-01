/* HairSoft Klient V87 – moderní a responzivní Soubory zákazníka. */
(function () {
  "use strict";

  var PANE_SELECTOR = "#Soubory";
  var TABLE_SELECTOR = "#zakaznici_tabulka_soubory";

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function svgIcon(name) {
    if (name === "upload") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"></path></svg>';
    }
    if (name === "trash") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M9 7V4h6v3"></path><path d="m7 7 1 13h8l1-13"></path><path d="M10 11v5M14 11v5"></path></svg>';
    }
    if (name === "empty") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M9 15h6"></path></svg>';
    }
    if (name === "image") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-5-5L5 20"></path></svg>';
    }
    if (name === "archive") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v13H4z"></path><path d="M3 3h18v4H3z"></path><path d="M9 11h6"></path></svg>';
    }
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>';
  }

  function fileIconName(filename) {
    var extension = String(filename || "").split(".").pop().toLowerCase();
    if (/^(jpg|jpeg|png|gif|webp|bmp|svg)$/.test(extension)) return "image";
    if (/^(zip|rar|7z|tar|gz)$/.test(extension)) return "archive";
    return "file";
  }

  function buildHeader(pane, count) {
    var header = document.createElement("header");
    header.className = "hs-files-header";
    header.innerHTML =
      '<span class="hs-files-header__icon">' + svgIcon("file") + '</span>' +
      '<span class="hs-files-header__copy"><strong>' + translate("Soubory zákazníka") + '</strong>' +
      '<small>' + translate("Dokumenty a přílohy uložené u zákazníka") + '</small></span>' +
      '<span class="hs-files-header__count" aria-label="' + translate("Počet souborů") + ': ' + count + '">' + count + '</span>';
    pane.insertBefore(header, pane.firstChild);
  }

  function enhanceUpload(pane) {
    var dropZone = pane.querySelector("#dropZone");
    var fileInput = pane.querySelector("#fileInput");
    var selectButton = pane.querySelector("#btnSelect");
    var form = fileInput ? fileInput.form : null;

    if (!dropZone || !fileInput || !selectButton || !form) return;
    form.classList.add("hs-files-upload-form");
    dropZone.classList.add("hs-files-dropzone");
    dropZone.setAttribute("role", "button");
    dropZone.setAttribute("tabindex", "0");
    dropZone.setAttribute("aria-label", translate("Vybrat soubor k nahrání"));
    dropZone.innerHTML =
      '<span class="hs-files-dropzone__icon">' + svgIcon("upload") + '</span>' +
      '<span class="hs-files-dropzone__copy"><strong>' + translate("Přetáhni soubor sem") + '</strong>' +
      '<small>' + translate("nebo klikněte pro výběr souboru") + '</small></span>' +
      '<span class="hs-files-dropzone__limit">' + translate("Max velikost 30MB") + '</span>';

    dropZone.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" && event.key !== " ") return;
      event.preventDefault();
      selectButton.click();
    });

    function showUploading() {
      dropZone.classList.add("hs-files-dropzone--uploading");
      dropZone.setAttribute("aria-busy", "true");
      var title = dropZone.querySelector("strong");
      var subtitle = dropZone.querySelector("small");
      if (title) title.textContent = translate("Nahrávám soubor…");
      if (subtitle) subtitle.textContent = translate("Počkejte prosím na dokončení nahrávání");
    }

    fileInput.addEventListener("change", function () {
      if (fileInput.files && fileInput.files.length) showUploading();
    }, true);
    dropZone.addEventListener("drop", function (event) {
      if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) showUploading();
    }, true);
  }

  function prepareTable(table) {
    var rows = Array.prototype.slice.call(table.rows || []);
    var headerRow;
    var thead;
    var tbody;

    if (!rows.length) return 0;
    headerRow = rows[0];
    if (!table.tHead) {
      thead = document.createElement("thead");
      table.insertBefore(thead, table.firstChild);
      thead.appendChild(headerRow);
    }
    tbody = table.tBodies.length ? table.tBodies[0] : null;
    if (!tbody) {
      tbody = document.createElement("tbody");
      table.appendChild(tbody);
    }
    table.classList.add("hs-files-table");
    return Array.prototype.filter.call(tbody.rows, function (row) { return row.cells.length >= 5; }).length;
  }

  function decorateNameCell(cell) {
    var filename = String(cell.textContent || "").trim();
    var content = document.createElement("span");
    var icon = document.createElement("span");
    var text = document.createElement("span");

    content.className = "hs-files-name";
    icon.className = "hs-files-name__icon hs-files-name__icon--" + fileIconName(filename);
    icon.innerHTML = svgIcon(fileIconName(filename));
    text.className = "hs-files-name__text";
    while (cell.firstChild) text.appendChild(cell.firstChild);
    content.appendChild(icon);
    content.appendChild(text);
    cell.appendChild(content);
  }

  function decorateStatusCell(cell) {
    var legacyIcon = cell.querySelector("i");
    var label = legacyIcon ? String(legacyIcon.getAttribute("title") || "").trim() : String(cell.textContent || "").trim();
    var status = document.createElement("span");
    var kind = legacyIcon && legacyIcon.classList.contains("fa-check-circle") ? "downloaded" : "pending";

    status.className = "hs-files-status hs-files-status--" + kind;
    if (legacyIcon) {
      legacyIcon.removeAttribute("style");
      legacyIcon.setAttribute("aria-hidden", "true");
      status.appendChild(legacyIcon);
    }
    status.appendChild(document.createTextNode(translate(label || "Nestaženo")));
    cell.textContent = "";
    cell.appendChild(status);
  }

  function decorateActionCell(cell) {
    var button = cell.querySelector("button[type='submit']");
    if (!button) return;
    button.classList.add("hs-files-delete");
    button.innerHTML = svgIcon("trash");
    button.title = translate("Smazat soubor");
    button.setAttribute("aria-label", translate("Smazat soubor"));
  }

  function decorateRows(table) {
    var labels = ["Název", "Velikost", "Datum", "Stav", "Akce"];
    var tbody = table.tBodies.length ? table.tBodies[0] : null;
    var rows = tbody ? tbody.rows : [];
    var rowIndex;
    var cellIndex;
    var row;

    for (rowIndex = 0; rowIndex < rows.length; rowIndex += 1) {
      row = rows[rowIndex];
      if (row.cells.length < 5) continue;
      row.classList.add("hs-files-row");
      for (cellIndex = 0; cellIndex < 5; cellIndex += 1) {
        row.cells[cellIndex].setAttribute("data-label", translate(labels[cellIndex]));
      }
      decorateNameCell(row.cells[0]);
      decorateStatusCell(row.cells[3]);
      decorateActionCell(row.cells[4]);
    }
  }

  function createEmptyState(pane, table) {
    var empty = document.createElement("div");
    empty.className = "hs-files-empty";
    empty.innerHTML = '<span class="hs-files-empty__icon">' + svgIcon("empty") + '</span>' +
      '<strong>' + translate("Zatím zde nejsou žádné soubory.") + '</strong>' +
      '<small>' + translate("První soubor přidáte přetažením nebo kliknutím do pole výše.") + '</small>';
    table.closest(".table-responsive").classList.add("hs-files-list--empty");
    table.closest(".table-responsive").appendChild(empty);
  }

  function initialize() {
    var pane = document.querySelector(PANE_SELECTOR);
    var table;
    var count;
    var legacyTitle;
    var tabs;

    if (!pane || pane.getAttribute("data-hs-files-enhanced") === "true") return;
    table = pane.querySelector(TABLE_SELECTOR);
    if (!table) return;
    count = prepareTable(table);
    pane.classList.add("hs-files-pane");
    legacyTitle = pane.querySelector(":scope > h3");
    if (legacyTitle) {
      legacyTitle.classList.add("hs-files-legacy-title");
      if (legacyTitle.nextElementSibling && legacyTitle.nextElementSibling.tagName === "BR") {
        legacyTitle.nextElementSibling.classList.add("hs-files-legacy-break");
      }
    }
    buildHeader(pane, count);
    enhanceUpload(pane);
    decorateRows(table);
    if (!count) createEmptyState(pane, table);

    tabs = pane.closest(".hs-client-detail-tabs");
    function updateTabState() {
      if (tabs) tabs.classList.toggle("hs-client-detail-tabs--files-active", pane.classList.contains("active"));
    }
    if (window.jQuery) {
      window.jQuery('a[data-toggle="tab"]')
        .off("shown.bs.tab.hsFiles")
        .on("shown.bs.tab.hsFiles", updateTabState);
    }
    updateTabState();
    pane.setAttribute("data-hs-files-enhanced", "true");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
}());
