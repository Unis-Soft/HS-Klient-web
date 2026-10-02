/* HairSoft Klient – moderní seznam zákazníků, verze 236 */
(function () {
  "use strict";

  var TABLE_SELECTOR = "#zakaznici_tabulka";
  var PROGRAM_COLUMN_INDEX = 9;
  var PROGRAM_NAME_MAX = 24;
  var programMeta = null;
  var programColumnEnabled = true;
  var programColumnLabel = "Programy";
  var selectedProgramId = "";
  var COLUMN_CLASSES = [
    "hs-col-detail",
    "hs-col-name",
    "hs-col-first-name",
    "hs-col-email",
    "hs-col-mobile",
    "hs-col-visits",
    "hs-col-cancelled",
    "hs-col-next",
    "hs-col-last",
    "hs-col-programs",
    "hs-col-points",
    "hs-col-blacklist",
    "hs-col-actions"
  ];
  var COLUMN_LABELS = [
    "Detail",
    "Zákazník",
    "Jméno",
    "Email",
    "Mobil",
    "Počet návštěv",
    "Zrušené",
    "Příští návštěva",
    "Poslední návštěva",
    "Programy",
    "Body",
    "Blacklist",
    "Akce"
  ];

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function svgIcon(name) {
    if (name === "search") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>';
    }
    if (name === "settings") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"></path><circle cx="16" cy="7" r="2"></circle><circle cx="8" cy="17" r="2"></circle></svg>';
    }
    if (name === "phone") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.2 3.5 4.8 5.9c-.8.8-.6 2.6.5 4.8a20 20 0 0 0 8 8c2.2 1.1 4 1.3 4.8.5l2.4-2.4-4-3-2 2c-1.7-.8-3.5-2.6-4.3-4.3l2-2-3-4Z"></path></svg>';
    }
    if (name === "mail") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m4 7 8 6 8-6"></path></svg>';
    }
    if (name === "calendar") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4M16 3v4M3 10h18"></path></svg>';
    }
    if (name === "trash") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg>';
    }
    return "";
  }

  function rawGuid(value) {
    return String(value || "").split("**+-+**")[0];
  }

  function joinName(firstName, surname) {
    var first = String(firstName || "").trim();
    var last = String(surname || "").trim();

    if (!first) return last;
    if (!last) return first;
    if (last === first || last.indexOf(first + " ") === 0) return last;
    return first + " " + last;
  }

  function addClass(element, className) {
    if (element && className) element.classList.add(className);
  }

  function makeHeaderStatic(header) {
    if (!header) return;
    header.classList.add("hs-customer-static-column");
    header.classList.remove("sorting", "sorting_asc", "sorting_desc");
    header.removeAttribute("aria-sort");
    header.removeAttribute("tabindex");
  }

  function disableEdgeSorting(api) {
    var settings = api.settings && api.settings()[0];
    [0, PROGRAM_COLUMN_INDEX, 12].forEach(function (columnIndex) {
      if (settings && settings.aoColumns && settings.aoColumns[columnIndex]) {
        settings.aoColumns[columnIndex].bSortable = false;
      }
      makeHeaderStatic(api.column(columnIndex).header());
    });
  }

  function formatCustomerTotal() {
    var row = customersKpiRow();
    var totals;

    if (!row) return;
    totals = row.querySelectorAll(".widget-mini .total");
    Array.prototype.forEach.call(totals, function (total) {
      var target = total.querySelector("font") || total;
      var raw = String(target.textContent || "").replace(/\s/g, "");
      if (!/^\d+$/.test(raw)) return;
      target.textContent = raw.replace(/\B(?=(\d{3})+(?!\d))/g, "\u00a0");
    });
  }

  function ensureValueWrapper(cell) {
    var wrapper;
    var label;

    if (!cell || cell.querySelector(":scope > .hs-customer-cell-value")) return;

    label = cell.querySelector(":scope > .hs-mobile-label");
    wrapper = document.createElement("span");
    wrapper.className = "hs-customer-cell-value";
    wrapper.setAttribute("data-hs-i18n-ignore", "");

    while (cell.firstChild) {
      if (cell.firstChild === label) {
        cell.removeChild(label);
      } else {
        wrapper.appendChild(cell.firstChild);
      }
    }

    if (label) cell.appendChild(label);
    cell.appendChild(wrapper);
  }

  function ensureMobileLabel(cell, source) {
    var label;
    if (!cell || !source || cell.querySelector(":scope > .hs-mobile-label")) return;
    label = document.createElement("span");
    label.className = "hs-mobile-label";
    label.textContent = source;
    cell.insertBefore(label, cell.firstChild);
  }

  function decorateCell(cell, columnIndex) {
    var label = COLUMN_LABELS[columnIndex];

    if (!cell) return;
    addClass(cell, COLUMN_CLASSES[columnIndex]);

    if ([2, 3, 4, 5, 6, 7, 8, 9, 10].indexOf(columnIndex) !== -1) {
      if (columnIndex !== 2) ensureMobileLabel(cell, label);
      ensureValueWrapper(cell);
    }

    if ([3, 4, 7, 8].indexOf(columnIndex) !== -1) {
      if (!String(cell.textContent || "").replace(label, "").trim()) {
        cell.setAttribute("data-hs-empty", "true");
      } else {
        cell.removeAttribute("data-hs-empty");
      }
    }
  }

  function decorateHeaders(api, wrapper) {
    var visibleIndexes = [];
    var clonedHeaders;
    var index;
    var header;

    for (index = 0; index < COLUMN_CLASSES.length; index += 1) {
      header = api.column(index).header();
      addClass(header, COLUMN_CLASSES[index]);
      if (index === 1 && header) header.textContent = "Zákazník";
      if (index === 0 || index === PROGRAM_COLUMN_INDEX || index === 12) makeHeaderStatic(header);
      if (api.column(index).visible()) visibleIndexes.push(index);
    }

    clonedHeaders = wrapper.querySelectorAll(".dataTables_scrollHead thead tr:first-child > th, .dataTables_scrollHead thead tr:first-child > td");
    for (index = 0; index < clonedHeaders.length; index += 1) {
      COLUMN_CLASSES.forEach(function (className) { clonedHeaders[index].classList.remove(className); });
      addClass(clonedHeaders[index], COLUMN_CLASSES[visibleIndexes[index]] || "");
      if (visibleIndexes[index] === 1) clonedHeaders[index].textContent = "Zákazník";
      if (visibleIndexes[index] === 0 || visibleIndexes[index] === PROGRAM_COLUMN_INDEX || visibleIndexes[index] === 12) makeHeaderStatic(clonedHeaders[index]);
    }
  }


  function normaliseKnownProgramName(value) {
    var text = String(value || "").replace(/\s+/g, " ").trim();
    var repaired;
    var folded;

    if (!text) return text;
    if (typeof window.hsCanonicalProgramName === "function") {
      return window.hsCanonicalProgramName(text);
    }

    // V236: HairSoft data can arrive with legacy Windows-1250/Latin-1 mojibake.
    // Normalise the known built-in program by meaning, not by one exact Unicode glyph.
    repaired = text
      .replace(/Ã“/g, "Ó").replace(/Ã³/g, "ó")
      .replace(/Äš/g, "Ě").replace(/Ä›/g, "ě");
    folded = repaired.normalize ? repaired.normalize("NFD").replace(/[\u0300-\u036f]/g, "") : repaired;
    folded = folded.toUpperCase().replace(/[^A-Z0-9]+/g, " ").replace(/\s+/g, " ").trim();
    if (folded === "ZONY TELA" || folded === "ZONY TILA") return "Zóny těla";
    return text;
  }

  function programDisplayName(value) {
    return translate(normaliseKnownProgramName(value));
  }

  function shortenProgramName(value, maxLength) {
    var text = programDisplayName(value);
    var limit = maxLength || PROGRAM_NAME_MAX;
    if (text.length <= limit) return text;
    return text.slice(0, Math.max(1, limit - 1)).replace(/\s+$/, "") + "…";
  }

  function normaliseProgramMeta(meta) {
    var programs;
    if (!meta || typeof meta !== "object") return null;
    programs = Array.isArray(meta.activePrograms) ? meta.activePrograms : (Array.isArray(meta.programs) ? meta.programs : []);
    programs = programs.map(function (program, index) {
      var id = program && typeof program === "object" ? (program.id ?? program.programId ?? index) : index;
      var name = program && typeof program === "object" ? (program.name ?? program.title ?? "") : String(program || "");
      var remaining = program && typeof program === "object" ? Number(program.remaining ?? 0) : 0;
      if (!Number.isFinite(remaining)) remaining = 0;
      return { id: String(id), name: String(name || "").trim(), remaining: Math.round(remaining) };
    }).filter(function (program) { return program.name !== ""; });
    var totalRemaining = Number(meta.totalRemaining);
    if (!Number.isFinite(totalRemaining)) {
      totalRemaining = programs.reduce(function (sum, program) { return sum + program.remaining; }, 0);
    }
    return {
      programs: programs,
      selectedProgramId: String(meta.selectedProgramId ?? meta.selectedId ?? ""),
      totalRemaining: Math.round(totalRemaining)
    };
  }

  function syncProgramVisibility(api) {
    var normalized = normaliseProgramMeta(programMeta);
    var shouldShow = normalized === null ? true : normalized.programs.length > 0;
    programColumnEnabled = shouldShow;
    if (api.column(PROGRAM_COLUMN_INDEX).visible() !== shouldShow) {
      api.column(PROGRAM_COLUMN_INDEX).visible(shouldShow, false);
      api.columns.adjust();
    }
    return normalized;
  }

  function programHeaderCells(api, wrapper) {
    var cells = [];
    var original = api.column(PROGRAM_COLUMN_INDEX).header();
    var cloned = wrapper.querySelectorAll(".dataTables_scrollHead .hs-col-programs");
    if (original) cells.push(original);
    Array.prototype.forEach.call(cloned, function (cell) {
      if (cells.indexOf(cell) === -1) cells.push(cell);
    });
    return cells;
  }

  function renderProgramHeader(api, wrapper, normalized) {
    var programs = normalized ? normalized.programs : null;
    var cells = programHeaderCells(api, wrapper);
    var selected;

    if (!programColumnEnabled) return;

    if (!programs) {
      programColumnLabel = translate("Programy");
      cells.forEach(function (cell) {
        makeHeaderStatic(cell);
        cell.innerHTML = '<span class="hs-program-column-label">Programy</span>';
        cell.removeAttribute("title");
      });
      return;
    }

    if (programs.length === 1) {
      programColumnLabel = programDisplayName(programs[0].name);
      cells.forEach(function (cell) {
        makeHeaderStatic(cell);
        cell.innerHTML = '<span class="hs-program-column-label"></span>';
        var label = cell.querySelector(".hs-program-column-label");
        label.textContent = shortenProgramName(programs[0].name, PROGRAM_NAME_MAX);
        label.title = programDisplayName(programs[0].name);
        cell.title = programDisplayName(programs[0].name);
      });
      selectedProgramId = programs[0].id;
      return;
    }

    selected = selectedProgramId || normalized.selectedProgramId || programs[0].id;
    if (!programs.some(function (program) { return program.id === selected; })) selected = programs[0].id;
    selectedProgramId = selected;
    var selectedProgram = programs.find(function (program) { return program.id === selectedProgramId; }) || programs[0];
    programColumnLabel = programDisplayName(selectedProgram.name);

    cells.forEach(function (cell) {
      var select = document.createElement("select");
      makeHeaderStatic(cell);
      cell.textContent = "";
      select.className = "hs-program-column-select";
      select.setAttribute("aria-label", translate("Vybrat program"));
      select.title = translate("Vybrat program");
      programs.forEach(function (program) {
        var option = document.createElement("option");
        option.value = program.id;
        option.textContent = shortenProgramName(program.name, 24);
        option.title = programDisplayName(program.name);
        option.selected = program.id === selectedProgramId;
        select.appendChild(option);
      });
      select.addEventListener("click", function (event) { event.stopPropagation(); });
      select.addEventListener("change", function (event) {
        var value = String(event.target.value || "");
        if (!value || value === selectedProgramId) return;
        selectedProgramId = value;
        var selectedMeta = programs.find(function (program) { return program.id === value; });
        programColumnLabel = selectedMeta ? programDisplayName(selectedMeta.name) : translate("Programy");
        api.ajax.reload(null, false);
      });
      cell.appendChild(select);
    });
  }

  window.hsCustomersProgramExportHeader = function () {
    return programColumnLabel || translate("Programy");
  };

  window.hsCustomersProgramColumnVisible = function () {
    return Boolean(programColumnEnabled);
  };

  function customersKpiRow() {
    var main = document.getElementById("main-content");
    var table = document.querySelector(TABLE_SELECTOR);
    var rows;
    var candidates = [];
    var index;

    if (!main || !table) return null;

    // menu.js runs before this file and marks the existing customer KPI row.
    // Pick the nearest KPI group that is physically before the customer table.
    rows = main.querySelectorAll(".hs-system-kpi-row");
    for (index = 0; index < rows.length; index += 1) {
      if (!rows[index].querySelector(".widget-mini, .hs-system-kpi-card")) continue;
      if (rows[index].compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING) {
        candidates.push(rows[index]);
      }
    }
    if (candidates.length) return candidates[candidates.length - 1];

    // Defensive fallback for a page where the global KPI enhancer did not run.
    rows = main.querySelectorAll(".row");
    for (index = 0; index < rows.length; index += 1) {
      if (!rows[index].querySelector(".widget-mini .total, .widget-mini .title")) continue;
      if (rows[index].compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING) {
        candidates.push(rows[index]);
      }
    }
    return candidates.length ? candidates[candidates.length - 1] : null;
  }

  function syncCustomersKpiGrid(row) {
    var main = document.getElementById("main-content");
    var count;
    var classes;
    var index;
    if (!row) return;

    count = row.querySelectorAll(".widget-mini").length;
    classes = Array.prototype.slice.call(row.classList);
    for (index = 0; index < classes.length; index += 1) {
      if (/^hs-system-kpi-count-\d+$/.test(classes[index])) row.classList.remove(classes[index]);
    }

    row.classList.add("hs-system-kpi-row");
    if (count > 0) row.classList.add("hs-system-kpi-count-" + String(count));
    row.style.setProperty("--hs-system-kpi-columns", String(Math.min(Math.max(count, 1), 4)));
    row.style.setProperty("--hs-system-kpi-tablet-columns", String(Math.min(Math.max(count, 1), 2)));
    if (main) main.classList.add("hs-system-kpi-content");
  }

  function removeProgramsKpi() {
    var existing = document.querySelector("#main-content .hs-programs-kpi-column");
    var row = existing ? existing.parentElement : null;
    if (existing && existing.parentNode) existing.parentNode.removeChild(existing);
    if (row) syncCustomersKpiGrid(row);
  }

  function renderProgramsKpi(normalized) {
    var row = customersKpiRow();
    var column;
    var card;
    var label;
    var value;
    var total;
    var breakdown;

    if (!row || !normalized || !normalized.programs.length) {
      removeProgramsKpi();
      return;
    }

    column = row.querySelector(":scope > .hs-programs-kpi-column");
    if (!column) {
      column = document.createElement("div");
      column.className = "hs-system-kpi-item hs-programs-kpi-column";
      card = document.createElement("div");
      card.className = "panel panel-solid-success widget-mini hs-system-kpi-card hs-programs-kpi-card";
      card.innerHTML = '' +
        '<div class="panel-body">' +
          '<span class="hs-kpi-icon" aria-hidden="true">' +
            '<svg viewBox="0 0 24 24"><path d="M5 5h14v14H5z"></path><path d="M8 9h8M8 13h5M8 17h3"></path></svg>' +
          '</span>' +
          '<span class="total hs-programs-kpi-card__value">0</span>' +
          '<span class="title hs-programs-kpi-card__label"></span>' +
        '</div>';
      column.appendChild(card);
      row.appendChild(column);
    }

    card = column.querySelector(".hs-programs-kpi-card");
    label = column.querySelector(".hs-programs-kpi-card__label");
    value = column.querySelector(".hs-programs-kpi-card__value");
    total = Number(normalized.totalRemaining) || 0;

    if (label) {
      if (typeof window.hsSetTranslatedText === "function") window.hsSetTranslatedText(label, "Programy");
      else label.textContent = translate("Programy");
    }
    if (value) value.textContent = String(total);

    breakdown = normalized.programs.map(function (program) {
      return programDisplayName(program.name) + ": " + program.remaining;
    }).join(" • ");

    if (card) {
      card.title = breakdown ? translate("Zbývající vstupy celkem") + " — " + breakdown : translate("Zbývající vstupy celkem");
      card.setAttribute("aria-label", card.title);
    }
    syncCustomersKpiGrid(row);
  }

  function prepareTopScrollbar(wrapper) {
    var scroll = wrapper.querySelector(":scope > .dataTables_scroll");
    var body = scroll ? scroll.querySelector(".dataTables_scrollBody") : null;
    var head = scroll ? scroll.querySelector(".dataTables_scrollHead") : null;
    var top = wrapper.querySelector(":scope > .hs-customers-top-scroll");
    var inner;
    var updating = false;

    if (!scroll || !body) return;
    if (!top) {
      top = document.createElement("div");
      top.className = "hs-customers-top-scroll";
      top.setAttribute("aria-label", "Posun tabulky zákazníků");
      inner = document.createElement("div");
      inner.className = "hs-customers-top-scroll__inner";
      top.appendChild(inner);
      wrapper.insertBefore(top, scroll);
    } else {
      inner = top.querySelector(":scope > .hs-customers-top-scroll__inner");
    }
    if (!inner) return;

    function resize() {
      var width = Math.max(body.scrollWidth || 0, body.querySelector("table") ? body.querySelector("table").scrollWidth : 0);
      inner.style.width = Math.max(width, body.clientWidth || 0) + "px";
      top.classList.toggle("hs-customers-top-scroll--hidden", width <= (body.clientWidth || 0) + 1);
      if (!updating) top.scrollLeft = body.scrollLeft;
    }

    if (top.getAttribute("data-hs-bound") !== "1") {
      top.setAttribute("data-hs-bound", "1");
      top.addEventListener("scroll", function () {
        if (updating) return;
        updating = true;
        body.scrollLeft = top.scrollLeft;
        if (head) head.scrollLeft = top.scrollLeft;
        updating = false;
      }, { passive: true });
      body.addEventListener("scroll", function () {
        if (updating) return;
        updating = true;
        top.scrollLeft = body.scrollLeft;
        updating = false;
      }, { passive: true });
      window.addEventListener("resize", function () {
        if (top.isConnected) window.requestAnimationFrame(resize);
      });
    }
    window.requestAnimationFrame(resize);
  }

  function mobileText(element, value) {
    element.textContent = String(value || "");
    element.setAttribute("data-hs-i18n-ignore", "");
    return element;
  }

  function buildMobileContact(iconName, value, href) {
    var element = document.createElement(href ? "a" : "span");
    var icon = document.createElement("span");
    var textValue = document.createElement("span");

    element.className = "hs-mobile-customer-contact";
    if (href) element.href = href;
    icon.className = "hs-mobile-customer-contact__icon";
    icon.innerHTML = svgIcon(iconName);
    textValue.className = "hs-mobile-customer-contact__value";
    mobileText(textValue, value);
    element.appendChild(icon);
    element.appendChild(textValue);
    return element;
  }

  function buildMobileDate(label, value) {
    var item = document.createElement("div");
    var icon = document.createElement("span");
    var content = document.createElement("span");
    var labelNode = document.createElement("span");
    var valueNode = document.createElement("span");

    item.className = "hs-mobile-customer-date";
    icon.className = "hs-mobile-customer-date__icon";
    icon.innerHTML = svgIcon("calendar");
    content.className = "hs-mobile-customer-date__content";
    labelNode.className = "hs-mobile-customer-date__label";
    labelNode.textContent = translate(label);
    valueNode.className = "hs-mobile-customer-date__value";
    mobileText(valueNode, value);
    content.appendChild(labelNode);
    content.appendChild(valueNode);
    item.appendChild(icon);
    item.appendChild(content);
    return item;
  }

  function buildMobileMetric(label, value) {
    var item = document.createElement("div");
    var valueNode = document.createElement("strong");
    var labelNode = document.createElement("span");

    item.className = "hs-mobile-customer-metric";
    valueNode.className = "hs-mobile-customer-metric__value";
    mobileText(valueNode, value === "" || value === null || typeof value === "undefined" ? "0" : value);
    labelNode.className = "hs-mobile-customer-metric__label";
    labelNode.textContent = translate(label);
    item.appendChild(valueNode);
    item.appendChild(labelNode);
    return item;
  }

  function buildMobileCard(api, rowIndex, data, guid, fullName) {
    var targetCell = api.cell(rowIndex, 0).node();
    var detailCell = api.cell(rowIndex, 0).node();
    var emailCell = api.cell(rowIndex, 3).node();
    var mobileCell = api.cell(rowIndex, 4).node();
    var actionCell = api.cell(rowIndex, 12).node();
    var existing = targetCell ? targetCell.querySelector(":scope > .hs-mobile-customer-card") : null;
    var card;
    var header;
    var avatarSource;
    var avatar;
    var identity;
    var nameLine;
    var nameLink;
    var badge;
    var contacts;
    var phone = String(data[4] || "").trim();
    var email = String(data[3] || "").trim();
    var phoneHref = mobileCell && mobileCell.querySelector("a") ? "tel:" + phone : "";
    var emailHref = emailCell && emailCell.querySelector("a") ? "mailto:" + email : "";
    var sourceAction;
    var deleteAction;
    var dates;
    var nextVisit = String(data[7] || "").trim();
    var lastVisit = String(data[8] || "").trim();
    var metrics;

    if (!targetCell) return;
    if (existing) existing.remove();

    card = document.createElement("article");
    card.className = "hs-mobile-customer-card";
    if (String(data[11]) === "1") card.classList.add("hs-mobile-customer-card--blacklisted");

    header = document.createElement("div");
    header.className = "hs-mobile-customer-card__header";

    avatarSource = detailCell ? detailCell.querySelector("a") : null;
    if (avatarSource) {
      avatar = avatarSource.cloneNode(true);
      avatar.className = "hs-mobile-customer-avatar";
    } else {
      avatar = document.createElement("a");
      avatar.className = "hs-mobile-customer-avatar hs-mobile-customer-avatar--empty";
      avatar.href = "index.php?strana=KartaOsoby&osoba_guid=" + encodeURIComponent(guid);
    }
    avatar.setAttribute("aria-label", translate("Detail zákazníka"));

    identity = document.createElement("div");
    identity.className = "hs-mobile-customer-identity";
    nameLine = document.createElement("div");
    nameLine.className = "hs-mobile-customer-name-line";
    nameLink = document.createElement("a");
    nameLink.className = "hs-mobile-customer-name";
    nameLink.href = "index.php?strana=KartaOsoby&osoba_guid=" + encodeURIComponent(guid);
    mobileText(nameLink, fullName || "—");
    nameLine.appendChild(nameLink);
    if (String(data[11]) === "1") {
      badge = document.createElement("span");
      badge.className = "hs-mobile-customer-blacklist";
      badge.textContent = "Blacklist";
      nameLine.appendChild(badge);
    }
    identity.appendChild(nameLine);

    contacts = document.createElement("div");
    contacts.className = "hs-mobile-customer-contacts";
    if (phone) contacts.appendChild(buildMobileContact("phone", phone, phoneHref));
    if (email) contacts.appendChild(buildMobileContact("mail", email, emailHref));
    if (contacts.childNodes.length) identity.appendChild(contacts);

    sourceAction = actionCell ? actionCell.querySelector("a") : null;
    if (sourceAction) {
      deleteAction = sourceAction.cloneNode(true);
      deleteAction.className = "hs-mobile-customer-delete";
      deleteAction.innerHTML = svgIcon("trash");
      deleteAction.title = translate("Smazat zákazníka");
      deleteAction.setAttribute("aria-label", translate("Smazat zákazníka"));
    }

    header.appendChild(avatar);
    header.appendChild(identity);
    if (deleteAction) header.appendChild(deleteAction);
    card.appendChild(header);

    if (nextVisit || lastVisit) {
      dates = document.createElement("div");
      dates.className = "hs-mobile-customer-dates";
      if (nextVisit) dates.appendChild(buildMobileDate("Příští návštěva", nextVisit));
      if (lastVisit) dates.appendChild(buildMobileDate("Poslední návštěva", lastVisit));
      card.appendChild(dates);
    }

    metrics = document.createElement("div");
    metrics.className = "hs-mobile-customer-metrics";
    metrics.appendChild(buildMobileMetric("Počet návštěv", data[5]));
    metrics.appendChild(buildMobileMetric("Zrušené", data[6]));
    if (programColumnEnabled) metrics.appendChild(buildMobileMetric(programColumnLabel || "Programy", data[9]));
    metrics.appendChild(buildMobileMetric("Body", data[10]));
    card.appendChild(metrics);
    targetCell.appendChild(card);
  }

  function decorateRows(api) {
    api.rows({ page: "current" }).every(function () {
      var row = this.node();
      var data = this.data() || [];
      var rowIndex = this.index();
      var nameDataCell = api.cell(rowIndex, 1);
      var surnameCell = nameDataCell.node();
      var actionCell = api.cell(rowIndex, 12).node();
      var fullName = joinName(data[2], data[1]);
      var guid = rawGuid(data[0]);
      var link;
      var value;
      var badge;
      var action;
      var columnIndex;

      if (!row) return;
      row.classList.toggle("hs-customer-blacklisted", String(data[11]) === "1");
      row.classList.toggle("hs-customer-no-email", !String(data[3] || "").trim());
      row.classList.toggle("hs-customer-no-next", !String(data[7] || "").trim());
      row.classList.toggle("hs-customer-no-last", !String(data[8] || "").trim());
      row.classList.toggle("hs-customer-no-dates", !String(data[7] || "").trim() && !String(data[8] || "").trim());

      if (String(data[1] || "") !== fullName) {
        nameDataCell.data(fullName);
        surnameCell = nameDataCell.node();
      }

      for (columnIndex = 0; columnIndex < COLUMN_CLASSES.length; columnIndex += 1) {
        decorateCell(api.cell(rowIndex, columnIndex).node(), columnIndex);
      }

      if (surnameCell) {
        surnameCell.textContent = "";
        value = document.createElement("span");
        value.className = "hs-customer-cell-value";
        value.setAttribute("data-hs-i18n-ignore", "");
        link = document.createElement("a");
        link.className = "hs-customer-name-link";
        link.href = "index.php?strana=KartaOsoby&osoba_guid=" + encodeURIComponent(guid);
        link.title = "Detail zákazníka";
        link.textContent = fullName || "—";
        value.appendChild(link);
        surnameCell.appendChild(value);

        if (String(data[11]) === "1") {
          badge = document.createElement("span");
          badge.className = "hs-blacklist-badge";
          badge.textContent = "Blacklist";
          surnameCell.appendChild(badge);
        }
      }

      if (actionCell) {
        action = actionCell.querySelector("a");
        if (action) {
          action.classList.add("hs-customer-delete");
          action.title = "Smazat zákazníka";
          action.setAttribute("aria-label", "Smazat zákazníka");
        }
      }

      buildMobileCard(api, rowIndex, data, guid, fullName);
    });
  }

  function prepareFilter(filter) {
    var label;
    var input;
    var icon;
    var nodes;
    var index;

    if (!filter) return;
    filter.classList.add("hs-customers-search");
    label = filter.querySelector("label");
    input = filter.querySelector("input");
    if (!label || !input) return;

    nodes = Array.prototype.slice.call(label.childNodes);
    for (index = 0; index < nodes.length; index += 1) {
      if (nodes[index].nodeType === 3) nodes[index].nodeValue = "";
    }

    if (!label.querySelector(".hs-customers-search-icon")) {
      icon = document.createElement("span");
      icon.className = "hs-customers-search-icon";
      icon.innerHTML = svgIcon("search");
      label.insertBefore(icon, label.firstChild);
    }

    input.placeholder = "Hledat podle jména, e-mailu nebo telefonu…";
    input.setAttribute("aria-label", "Hledat zákazníka");
    input.setAttribute("autocomplete", "off");
    label.setAttribute("aria-label", "Hledat zákazníka");
  }

  function closestRow(element) {
    while (element && element.parentNode) {
      if (element.classList && element.classList.contains("rada")) return element;
      element = element.parentNode;
    }
    return null;
  }

  function prepareToolbar(table, wrapper, panelBody) {
    var toolbar = wrapper.querySelector(":scope > .hs-customers-toolbar");
    var left;
    var right;
    var filter = wrapper.querySelector(":scope > .dataTables_filter");
    var buttons = wrapper.querySelector(":scope > .dt-buttons");
    var pagingInput = panelBody.querySelector('input[name="zakaznici_tabulka_vse"]');
    var pagingRow = pagingInput ? closestRow(pagingInput) : null;
    var footer;
    var info;
    var paginate;

    if (!toolbar) {
      toolbar = document.createElement("div");
      toolbar.className = "hs-customers-toolbar";
      left = document.createElement("div");
      left.className = "hs-customers-toolbar-primary";
      right = document.createElement("div");
      right.className = "hs-customers-toolbar-actions";
      toolbar.appendChild(left);
      toolbar.appendChild(right);
      wrapper.insertBefore(toolbar, wrapper.firstChild);
    } else {
      left = toolbar.querySelector(".hs-customers-toolbar-primary");
      right = toolbar.querySelector(".hs-customers-toolbar-actions");
    }

    prepareFilter(filter);
    if (filter && filter.parentNode !== left) left.appendChild(filter);
    if (buttons && buttons.parentNode !== right) right.appendChild(buttons);
    if (pagingRow && pagingRow.parentNode !== right) right.appendChild(pagingRow);

    footer = wrapper.querySelector(":scope > .hs-customers-footer");
    if (!footer) {
      footer = document.createElement("div");
      footer.className = "hs-customers-footer";
      wrapper.appendChild(footer);
    }
    info = wrapper.querySelector(":scope > .dataTables_info");
    paginate = wrapper.querySelector(":scope > .dataTables_paginate");
    if (info && info.parentNode !== footer) footer.appendChild(info);
    if (paginate && paginate.parentNode !== footer) footer.appendChild(paginate);

    table.setAttribute("aria-label", "Seznam zákazníků");
  }

  function prepareDataManagement(panelBody, wrapper) {
    var deleteInput = panelBody.querySelector('input[name="smazat_seznam_zakazniku"]');
    var refreshInput = panelBody.querySelector('input[name="znovunahrat_seznam_zakazniku"]');
    var deleteRow = deleteInput ? closestRow(deleteInput) : null;
    var refreshRow = refreshInput ? closestRow(refreshInput) : null;
    var details;
    var summary;
    var actions;
    var icon;

    if (!deleteRow && !refreshRow) return;
    details = panelBody.querySelector(":scope > .hs-customer-data-management");
    if (!details) {
      details = document.createElement("details");
      details.className = "hs-customer-data-management";
      summary = document.createElement("summary");
      icon = document.createElement("span");
      icon.className = "hs-data-management-icon";
      icon.innerHTML = svgIcon("settings");
      summary.appendChild(icon);
      summary.appendChild(document.createTextNode("Správa dat zákazníků"));
      details.appendChild(summary);
      actions = document.createElement("div");
      actions.className = "hs-data-management-actions";
      details.appendChild(actions);
      panelBody.appendChild(details);
    } else {
      actions = details.querySelector(".hs-data-management-actions");
    }

    if (refreshRow && refreshRow.parentNode !== actions) actions.appendChild(refreshRow);
    if (deleteRow && deleteRow.parentNode !== actions) actions.appendChild(deleteRow);
    if (wrapper.nextSibling !== details) panelBody.appendChild(details);
  }

  function preparePanel(table) {
    var panel = table.closest(".panel.panel-default");
    var panelBody = table.closest(".panel-body");
    var heading;

    if (panel) {
      panel.classList.add("hs-customers-panel");
      heading = panel.querySelector(":scope > .panel-heading");
      if (heading) heading.classList.add("hs-customers-legacy-heading");
    }
    if (panelBody) panelBody.classList.add("hs-customers-panel-body");
    return panelBody;
  }

  function prepareSyncStatus(panel) {
    var row;
    var syncPanel;
    if (!panel) return;
    row = panel.closest(".row");
    if (!row) return;
    syncPanel = row.nextElementSibling ? row.nextElementSibling.querySelector(".panel.panel-default") : null;
    if (syncPanel) syncPanel.classList.add("hs-customers-sync-panel");
  }

  function enhance(table, api) {
    var wrapper = document.getElementById("zakaznici_tabulka_wrapper");
    var panelBody = preparePanel(table);
    var panel = table.closest(".hs-customers-panel");

    if (!wrapper || !panelBody) return;
    document.documentElement.classList.add("hs-customers-page");
    wrapper.classList.add("hs-customers-table-wrapper");
    formatCustomerTotal();
    disableEdgeSorting(api);

    if (table.getAttribute("data-hs-first-name-hidden") !== "true") {
      api.column(2).visible(false, false);
      table.setAttribute("data-hs-first-name-hidden", "true");
      api.columns.adjust();
    }

    if (api.responsive && typeof api.responsive.disable === "function") {
      api.responsive.disable();
    }

    var normalizedProgramMeta = syncProgramVisibility(api);
    decorateHeaders(api, wrapper);
    renderProgramHeader(api, wrapper, normalizedProgramMeta);
    renderProgramsKpi(normalizedProgramMeta);
    decorateRows(api);
    prepareToolbar(table, wrapper, panelBody);
    prepareTopScrollbar(wrapper);
    prepareDataManagement(panelBody, wrapper);
    prepareSyncStatus(panel);
  }

  function initialize(attempt) {
    var table = document.querySelector(TABLE_SELECTOR);
    var jq = window.jQuery;
    var api;

    if (!table) return;
    formatCustomerTotal();
    if (!jq || !jq.fn || !jq.fn.dataTable || !jq.fn.dataTable.isDataTable(table)) {
      if (attempt < 80) window.setTimeout(function () { initialize(attempt + 1); }, 75);
      return;
    }

    api = jq(table).DataTable();
    if (api.ajax && typeof api.ajax.json === "function") {
      var initialJson = api.ajax.json();
      if (initialJson && Object.prototype.hasOwnProperty.call(initialJson, "hsProgramMeta")) programMeta = initialJson.hsProgramMeta;
      else if (initialJson && Object.prototype.hasOwnProperty.call(initialJson, "programMeta")) programMeta = initialJson.programMeta;
    }
    jq(table)
      .off("draw.dt.hsCustomers preXhr.dt.hsCustomers xhr.dt.hsCustomers error.dt.hsCustomers")
      .on("draw.dt.hsCustomers", function () { enhance(table, api); })
      .on("preXhr.dt.hsCustomers", function (event, settings, data) {
        if (selectedProgramId && data && typeof data === "object") data.hs_program_id = selectedProgramId;
      })
      .on("xhr.dt.hsCustomers", function (event, settings, json) {
        if (json && Object.prototype.hasOwnProperty.call(json, "hsProgramMeta")) programMeta = json.hsProgramMeta;
        else if (json && Object.prototype.hasOwnProperty.call(json, "programMeta")) programMeta = json.programMeta;
      });

    enhance(table, api);
    window.setTimeout(function () { enhance(table, api); }, 0);

    if (document.documentElement.getAttribute("data-hs-program-language-bound") !== "1") {
      document.documentElement.setAttribute("data-hs-program-language-bound", "1");
      document.addEventListener("hs:languagechange", function () {
        window.setTimeout(function () { enhance(table, api); }, 0);
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { initialize(0); }, { once: true });
  } else {
    initialize(0);
  }
}());