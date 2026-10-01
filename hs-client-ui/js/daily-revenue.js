/* HairSoft Klient V101 – kompletní jazykové mutace Denních tržeb bez změny dat a formulářů. */
(function () {
  "use strict";

  var TABLE_SELECTOR = [
    "#table_DenniSumarZaStrediska",
    "#table_DenniSumarZaJednotlivce",
    "#table_CelkovySumarZaStrediska",
    "#table_CelkoveTrzbyZaObsluhu"
  ].join(",");
  var attempts = 0;
  var chartCenterPluginRegistered = false;
  var chartColorCursor = 0;
  var chartColorByLabel = {};
  var CHART_PALETTE = [
    "#17A7A2",
    "#526B91",
    "#E3A13A",
    "#887BD5",
    "#D76083",
    "#4D98C5",
    "#67AF7A",
    "#A95069",
    "#C58E3C",
    "#7389A4"
  ];

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function setTranslatedText(element, source) {
    if (!element) return;
    if (typeof window.hsSetTranslatedText === "function") window.hsSetTranslatedText(element, source);
    else element.textContent = translate(source);
  }

  function setTranslatedAttribute(element, attribute, source) {
    if (!element) return;
    if (typeof window.hsSetTranslatedAttribute === "function") window.hsSetTranslatedAttribute(element, attribute, source);
    else element.setAttribute(attribute, translate(source));
  }

  function cleanText(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
  }

  function folded(value) {
    var text = cleanText(value).toLowerCase();
    if (typeof text.normalize === "function") {
      text = text.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }
    return text;
  }

  function svgIcon(name) {
    var icons = {
      calendar: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"></rect><path d="M8 3v4M16 3v4M3 10h18"></path></svg>',
      table: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 9h18M8 9v11M15 9v11"></path></svg>',
      chart: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9h-9V3Z"></path><path d="M15 3.6A9 9 0 0 1 20.4 9H15V3.6Z"></path></svg>',
      settings: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"></path><circle cx="16" cy="7" r="2"></circle><circle cx="8" cy="17" r="2"></circle></svg>',
      download: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M4 20h16"></path></svg>',
      refresh: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5"></path><path d="M19 12a7 7 0 1 0-2 5"></path></svg>',
      trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg>',
      export: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 20h14"></path></svg>',
      services: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="6" cy="7" r="3"></circle><circle cx="6" cy="17" r="3"></circle><path d="m8.6 8.5 11 6.5M8.6 15.5l11-6.5"></path></svg>',
      sales: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Z"></path><path d="M9 10V6a3 3 0 0 1 6 0v4"></path></svg>',
      receipt: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path><path d="M9 8h6M9 12h6M9 16h3"></path></svg>',
      left: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>',
      right: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>'
    };
    return icons[name] || icons.table;
  }

  function isDailyRevenuePage() {
    var query = String(window.location.search || "");
    if (/[?&]strana=CelkoveTrzby(?:&|$)/i.test(query)) return true;
    return Boolean(document.querySelector("#table_DenniSumarZaStrediska, #table_DenniSumarZaJednotlivce"));
  }

  function topLevelChild(node, parent) {
    var current = node;
    while (current && current.parentElement !== parent) current = current.parentElement;
    return current && current.parentElement === parent ? current : null;
  }

  function enhanceHeading(panel, type, subtitle, titleSource) {
    var heading = null;
    var nestedHeading = null;
    var title = heading && heading.querySelector(".panel-title");
    var original;
    var icon;
    var copy;
    var strong;
    var small;
    if (panel) {
      Array.prototype.some.call(panel.children, function (child) {
        if (child.classList && child.classList.contains("panel-heading")) {
          heading = child;
          return true;
        }
        return false;
      });
    }
    if (heading) {
      Array.prototype.some.call(heading.children, function (child) {
        if (child.classList && child.classList.contains("panel-heading")) {
          nestedHeading = child;
          return true;
        }
        return false;
      });
    }
    if (nestedHeading) {
      while (nestedHeading.firstChild) heading.insertBefore(nestedHeading.firstChild, nestedHeading);
      nestedHeading.parentNode.removeChild(nestedHeading);
    }
    title = heading && heading.querySelector(".panel-title");
    if (!heading || !title || title.getAttribute("data-hs-daily-heading") === "true") return;

    original = titleSource || cleanText(title.textContent);
    if (!original) return;
    title.textContent = "";
    title.classList.add("hs-daily-heading");

    icon = document.createElement("span");
    icon.className = "hs-daily-heading__icon";
    icon.innerHTML = svgIcon(type);
    copy = document.createElement("span");
    copy.className = "hs-daily-heading__copy";
    strong = document.createElement("strong");
    setTranslatedText(strong, original);
    copy.appendChild(strong);
    if (subtitle) {
      small = document.createElement("small");
      setTranslatedText(small, subtitle);
      copy.appendChild(small);
    }
    title.appendChild(icon);
    title.appendChild(copy);
    title.setAttribute("data-hs-daily-heading", "true");
  }

  function buttonText(element) {
    return cleanText(element && (element.value || element.textContent));
  }

  function actionKind(text, element) {
    var value = folded(text);
    var form = element && element.closest ? element.closest("form") : null;
    if (form && form.querySelector("input[name='nacist_starsi_data']")) return "older";
    if (form && form.querySelector("input[name='refresh_pro_den']")) return "refresh";
    if (form && form.querySelector("input[name='smazat_dnesni_data']")) return "delete";
    if (value.indexOf("nacist starsi data") !== -1 || value.indexOf("load older data") !== -1) return "older";
    if (value.indexOf("refresh dat z pc") !== -1 || value.indexOf("refresh data from pc") !== -1) return "refresh";
    if (value.indexOf("smazat zobrazena data") !== -1 || value.indexOf("delete displayed data") !== -1) return "delete";
    return "";
  }

  function originalActionBlock(element) {
    var row = element.closest(".rada");
    var form = element.closest("form");
    if (row && row.querySelectorAll("button, input[type='submit'], a.btn").length === 1) return row;
    if (form) return form;
    return element;
  }

  function decorateActionButton(element, kind) {
    var oldIcons;
    var iconName = kind === "delete" ? "trash" : kind === "refresh" ? "refresh" : "download";
    if (!element || element.getAttribute("data-hs-daily-action")) return;
    element.setAttribute("data-hs-daily-action", kind);
    element.classList.add("hs-daily-option-button", "hs-daily-option-button--" + kind);
    if (element.tagName !== "INPUT") {
      oldIcons = element.querySelectorAll("i.fa, i[class*='icon-']");
      Array.prototype.forEach.call(oldIcons, function (oldIcon) {
        oldIcon.setAttribute("aria-hidden", "true");
      });
      element.insertAdjacentHTML("afterbegin", '<span class="hs-daily-option-button__icon">' + svgIcon(iconName) + "</span>");
    }
  }

  function enhanceSummaryWidgets(main) {
    // Shared menu.js owns the same KPI markup and styling on daily and monthly pages.
    Array.prototype.forEach.call(main.querySelectorAll(".widget-mini"), function (widget, index) {
      var title = widget.querySelector(".title"), total = widget.querySelector(".total");
      var titles = ["TOP obsluha za služby", "TOP obsluha za prodej", "Počet účtenek"];
      if (title && titles[index]) setTranslatedText(title, titles[index]);
      if (total) total.setAttribute("title", cleanText(total.textContent));
    });
  }

  function findFooterBlock(main) {
    var bodies = main.querySelectorAll(".panel-body");
    var index;
    var text;
    for (index = bodies.length - 1; index >= 0; index -= 1) {
      text = folded(bodies[index].textContent);
      if (text.indexOf("strana nactena za") !== -1 || text.indexOf("page loaded in") !== -1) {
        return topLevelChild(bodies[index], main);
      }
    }
    return null;
  }

  function moveDataActions(main) {
    var controls = main.querySelectorAll("button, input[type='submit'], a.btn");
    var found = [];
    var usedBlocks = [];
    var index;
    var kind;
    var block;
    var row;
    var column;
    var panel;
    var heading;
    var title;
    var body;
    var intro;
    var actions;
    var footer;

    for (index = 0; index < controls.length; index += 1) {
      kind = actionKind(buttonText(controls[index]), controls[index]);
      if (!kind) continue;
      block = originalActionBlock(controls[index]);
      if (usedBlocks.indexOf(block) !== -1) continue;
      decorateActionButton(controls[index], kind);
      usedBlocks.push(block);
      found.push({ element: controls[index], block: block, kind: kind });
    }
    if (!found.length || main.querySelector(".hs-daily-options-row")) return;

    row = document.createElement("div");
    row.className = "row hs-daily-options-row";
    column = document.createElement("div");
    column.className = "col-md-12";
    panel = document.createElement("section");
    panel.className = "panel panel-default hs-daily-options-card";
    heading = document.createElement("div");
    heading.className = "panel-heading";
    title = document.createElement("h3");
    title.className = "panel-title hs-daily-heading";
    title.setAttribute("data-hs-daily-heading", "true");
    var titleIcon = document.createElement("span");
    var titleCopy = document.createElement("span");
    var titleStrong = document.createElement("strong");
    var titleSmall = document.createElement("small");
    titleIcon.className = "hs-daily-heading__icon";
    titleIcon.innerHTML = svgIcon("settings");
    titleCopy.className = "hs-daily-heading__copy";
    setTranslatedText(titleStrong, "Správa dat");
    setTranslatedText(titleSmall, "Načtení, synchronizace a odstranění zobrazených dat");
    titleCopy.appendChild(titleStrong);
    titleCopy.appendChild(titleSmall);
    title.appendChild(titleIcon);
    title.appendChild(titleCopy);
    heading.appendChild(title);
    body = document.createElement("div");
    body.className = "panel-body";
    intro = document.createElement("p");
    intro.className = "hs-daily-options-intro";
    setTranslatedText(intro, "Tyto akce ovlivňují data zobrazená na této stránce.");
    actions = document.createElement("div");
    actions.className = "hs-daily-options-actions";
    found.forEach(function (item) {
      item.block.classList.add("hs-daily-option");
      item.block.setAttribute("data-hs-daily-option", item.kind);
      actions.appendChild(item.block);
    });
    body.appendChild(intro);
    body.appendChild(actions);
    panel.appendChild(heading);
    panel.appendChild(body);
    column.appendChild(panel);
    row.appendChild(column);

    footer = findFooterBlock(main);
    if (footer) main.insertBefore(row, footer);
    else main.appendChild(row);
  }

  function enhanceDatePanel(main) {
    var titles = main.querySelectorAll(".panel-heading .panel-title");
    var panel = null;
    var index;
    var text;
    var input;
    var wrapper;
    var block;
    var body;
    var controls;
    var controlText;
    input = main.querySelector("#datepicker1, input[name='datum'][type='text']");
    if (input) panel = input.closest(".panel");
    for (index = 0; !panel && index < titles.length; index += 1) {
      text = folded(titles[index].textContent);
      if (text.indexOf("trzby pro datum") !== -1 || text.indexOf("revenue for date") !== -1) {
        panel = titles[index].closest(".panel");
        break;
      }
    }
    if (!panel) return;
    panel.classList.add("hs-daily-date-card");
    if (!panel.querySelector(".panel-heading")) {
      body = panel.querySelector(".panel-body");
      var originalTitle = body && body.querySelector("p");
      var newHeading = document.createElement("div");
      var newTitle = document.createElement("h3");
      var visibleTitle = "Tržby pro datum";
      newHeading.className = "panel-heading";
      newTitle.className = "panel-title";
      setTranslatedText(newTitle, visibleTitle);
      newHeading.appendChild(newTitle);
      panel.insertBefore(newHeading, body || panel.firstChild);
      if (originalTitle) originalTitle.parentNode.removeChild(originalTitle);
    }
    enhanceHeading(panel, "calendar", "Vyberte den pro zobrazení tržeb", "Tržby pro datum");
    body = panel.querySelector(".panel-body");
    if (body) body.classList.add("hs-daily-date-body");

    input = panel.querySelector('#datepicker1, input[name="datum"], input[id*="datum" i], input[type="date"], input[type="text"]');
    if (input) {
      input.classList.add("hs-daily-date-input");
      input.setAttribute("autocomplete", "off");
      if (!input.closest(".hs-daily-date-input-wrap")) {
        wrapper = document.createElement("span");
        wrapper.className = "hs-daily-date-input-wrap";
        input.parentNode.insertBefore(wrapper, input);
        wrapper.innerHTML = '<span class="hs-daily-date-input-icon">' + svgIcon("calendar") + "</span>";
        wrapper.appendChild(input);
      }
      block = body ? topLevelChild(input, body) : null;
      if (!block) block = input.closest("form, .rada, .form-group") || input.parentElement;
      if (block) block.classList.add("hs-daily-date-input-group");
    }

    controls = panel.querySelectorAll("button, input[type='submit'], a.btn");
    Array.prototype.forEach.call(controls, function (control) {
      controlText = folded(buttonText(control));
      if (/^(zpet|spat|predchozi|predchadzajuca|previous|back|zuruck)/.test(controlText)) {
        control.classList.add("hs-daily-date-nav", "hs-daily-date-nav--previous");
        setTranslatedAttribute(control, "aria-label", "Zpět");
        block = body ? topLevelChild(control, body) : null;
        if (!block) block = control.closest("form, .rada, .form-group") || control.parentElement;
        if (block) block.classList.add("hs-daily-date-nav-group", "hs-daily-date-nav-group--previous");
        if (control.tagName !== "INPUT" && !control.querySelector(".hs-daily-date-nav__icon")) {
          control.insertAdjacentHTML("afterbegin", '<span class="hs-daily-date-nav__icon">' + svgIcon("left") + "</span>");
        }
      } else if (/^(dalsi|next|weiter)/.test(controlText)) {
        control.classList.add("hs-daily-date-nav", "hs-daily-date-nav--next");
        setTranslatedAttribute(control, "aria-label", "Další");
        block = body ? topLevelChild(control, body) : null;
        if (!block) block = control.closest("form, .rada, .form-group") || control.parentElement;
        if (block) block.classList.add("hs-daily-date-nav-group", "hs-daily-date-nav-group--next");
        if (control.tagName !== "INPUT" && !control.querySelector(".hs-daily-date-nav__icon")) {
          control.insertAdjacentHTML("beforeend", '<span class="hs-daily-date-nav__icon">' + svgIcon("right") + "</span>");
        }
      }
    });
    // Keep the original forms and datepicker handlers; only arrange their containers.
    if (body && !body.querySelector(".hs-daily-date-controls")) {
      var previous = body.querySelector(".hs-daily-date-nav-group--previous");
      var selected = body.querySelector(".hs-daily-date-input-group");
      var next = body.querySelector(".hs-daily-date-nav-group--next");
      if (previous && selected && next && previous !== selected && next !== selected && previous !== next) {
        var group = document.createElement("div");
        group.className = "hs-daily-date-controls";
        body.insertBefore(group, body.firstChild);
        group.appendChild(previous); group.appendChild(selected); group.appendChild(next);
      }
    }
    Array.prototype.forEach.call(panel.querySelectorAll("button.hs-daily-date-nav, a.hs-daily-date-nav"), function (control) {
      var previous = control.classList.contains("hs-daily-date-nav--previous");
      control.textContent = "";
      var label = document.createElement("span"); label.className = "sr-only";
      setTranslatedText(label, previous ? "Zpět" : "Další");
      control.appendChild(label);
      control.insertAdjacentHTML("beforeend", '<span class="hs-daily-date-nav__icon" aria-hidden="true">' + svgIcon(previous ? "left" : "right") + '</span>');
    });
  }

  function headerLabels(table) {
    var labels = [];
    var row;
    var cells;
    var index;
    if (!table.tHead || !table.tHead.rows.length) return labels;
    row = table.tHead.rows[table.tHead.rows.length - 1];
    cells = row.cells;
    for (index = 0; index < cells.length; index += 1) labels.push(cleanText(cells[index].textContent));
    return labels;
  }

  function isTotalText(value) {
    return /\b(celkem|celkom|total|gesamt|summe)\b/.test(folded(value));
  }

  function labelTableCells(table) {
    var labels = headerLabels(table);
    var groups = [table.tBodies, table.tFoot ? [table.tFoot] : []];
    groups.forEach(function (sections) {
      Array.prototype.forEach.call(sections, function (section) {
        Array.prototype.forEach.call(section.rows, function (row) {
          Array.prototype.forEach.call(row.cells, function (cell, cellIndex) {
            if (labels[cellIndex]) cell.setAttribute("data-hs-label", labels[cellIndex]);
          });
          if (isTotalText(row.textContent)) row.classList.add("hs-daily-total-row");
        });
      });
    });
  }

  function createElement(tagName, className, textContent) {
    var element = document.createElement(tagName);
    if (className) element.className = className;
    if (textContent != null) element.textContent = textContent;
    return element;
  }

  function mobileMoney(value) {
    var text = cleanText(value);
    if (!text || text === "-" || text === "—") return "—";
    return /Kč|CZK|EUR|USD|€|\$|£/i.test(text) ? text : text + " Kč";
  }

  function tableRows(table) {
    var rows = [];
    Array.prototype.forEach.call(table.tBodies || [], function (body) {
      Array.prototype.forEach.call(body.rows || [], function (row) { rows.push(row); });
    });
    if (table.tFoot) {
      Array.prototype.forEach.call(table.tFoot.rows || [], function (row) { rows.push(row); });
    }
    return rows;
  }

  function buildMobileCards(table, wrapper) {
    var labels = headerLabels(table);
    var isIndividual = table.id === "table_DenniSumarZaJednotlivce" || table.id === "table_CelkoveTrzbyZaObsluhu";
    var metricStart = isIndividual ? 3 : 2;
    var metricSources = ["Tržby celkem", "Za služby", "Za prodej", "Ceniny", "Kredit"];
    var container = table._hsDailyMobileCards;
    var toolbar;
    var rendered = 0;
    if (!wrapper || labels.length < metricStart + 2) return;
    if (!container || !wrapper.contains(container)) {
      if (container && container.parentNode) container.parentNode.removeChild(container);
      container = createElement("div", "hs-daily-mobile-cards");
      container.setAttribute("aria-live", "polite");
      toolbar = wrapper.querySelector(".hs-daily-table-toolbar");
      if (toolbar && toolbar.parentNode === wrapper) wrapper.insertBefore(container, toolbar.nextSibling);
      else wrapper.insertBefore(container, wrapper.firstChild);
      table._hsDailyMobileCards = container;
    }
    while (container.firstChild) container.removeChild(container.firstChild);

    tableRows(table).forEach(function (row) {
      var cells = Array.prototype.map.call(row.cells || [], function (cell) { return cleanText(cell.textContent); });
      var total;
      var card;
      var head;
      var identity;
      var kicker;
      var title;
      var subtitle;
      var badge;
      var metrics;
      var index;
      if (cells.length < metricStart + 2 || (row.classList && row.classList.contains("child"))) return;
      total = row.classList.contains("hs-daily-total-row") || isTotalText(cells.join(" "));
      card = createElement("article", "hs-daily-mobile-card" + (total ? " hs-daily-mobile-card--total" : ""));
      head = createElement("header", "hs-daily-mobile-card__head");
      identity = createElement("div", "hs-daily-mobile-card__identity");
      kicker = createElement("span", "hs-daily-mobile-card__kicker");
      setTranslatedText(kicker, total ? "Součet" : (isIndividual ? "Obsluha" : "Středisko | firma"));
      title = createElement("strong", "hs-daily-mobile-card__title");
      if (total) setTranslatedText(title, "Celkem");
      else title.textContent = cells[isIndividual ? 2 : 1] || "—";
      identity.appendChild(kicker);
      identity.appendChild(title);
      if (isIndividual && cells[1] && !total) {
        subtitle = createElement("span", "hs-daily-mobile-card__subtitle", cells[1]);
        identity.appendChild(subtitle);
      }
      badge = createElement("span", "hs-daily-mobile-card__badge", total ? "Σ" : (cells[0] || String(rendered + 1)));
      head.appendChild(identity);
      head.appendChild(badge);
      card.appendChild(head);

      metrics = createElement("div", "hs-daily-mobile-card__metrics");
      for (index = metricStart; index < cells.length; index += 2) {
        var metric = createElement("div", "hs-daily-mobile-metric");
        var metricSource = metricSources[Math.floor((index - metricStart) / 2)] || "Hodnota";
        var metricLabel = createElement("span", "hs-daily-mobile-metric__label");
        var gross = createElement("strong", "hs-daily-mobile-metric__value", mobileMoney(cells[index]));
        var net = createElement("span", "hs-daily-mobile-metric__net");
        var netLabel = createElement("span", "hs-daily-mobile-metric__net-label");
        setTranslatedText(metricLabel, metricSource);
        setTranslatedText(netLabel, "bez DPH");
        net.appendChild(netLabel);
        net.appendChild(document.createTextNode(" · " + mobileMoney(cells[index + 1])));
        metric.appendChild(metricLabel);
        metric.appendChild(gross);
        metric.appendChild(net);
        metrics.appendChild(metric);
      }
      card.appendChild(metrics);
      container.appendChild(card);
      rendered += 1;
    });

    if (!rendered) {
      var empty = createElement("p", "hs-daily-mobile-empty");
      setTranslatedText(empty, "Nic nenalezeno");
      container.appendChild(empty);
    }
  }

  function bindMobileCards(table) {
    var body;
    if (!table || table.getAttribute("data-hs-daily-mobile-bound") === "true") return;
    table.setAttribute("data-hs-daily-mobile-bound", "true");
    body = table.tBodies && table.tBodies[0];
    if (body && window.MutationObserver) {
      table._hsDailyMobileObserver = new MutationObserver(function () {
        buildMobileCards(table, table.closest(".dataTables_wrapper") || table.parentElement);
      });
      table._hsDailyMobileObserver.observe(body, { childList: true, subtree: true, characterData: true });
    }
    if (window.jQuery) {
      window.jQuery(table).on("draw.dt.hsDailyMobile", function () {
        buildMobileCards(table, table.closest(".dataTables_wrapper") || table.parentElement);
      });
    }
  }

  function normalizeTableStructure(table) {
    var dataTableWrapper = table && table.closest(".dataTables_wrapper");
    var legacyScroll = dataTableWrapper && dataTableWrapper.closest(".hs-daily-table-scroll");
    if (legacyScroll && legacyScroll !== dataTableWrapper && legacyScroll.parentNode) {
      legacyScroll.parentNode.insertBefore(dataTableWrapper, legacyScroll);
      if (!legacyScroll.children.length) legacyScroll.parentNode.removeChild(legacyScroll);
    }
    return dataTableWrapper;
  }

  function normalizeExport(wrapper) {
    var buttons = wrapper.querySelectorAll(".dt-button.buttons-collection");
    Array.prototype.forEach.call(buttons, function (button) {
      var icon;
      var label;
      if (button.getAttribute("data-hs-daily-export") === "true") return;
      button.textContent = "";
      icon = document.createElement("span");
      icon.className = "hs-daily-export-icon";
      icon.innerHTML = svgIcon("export");
      label = document.createElement("span");
      setTranslatedText(label, "Export");
      button.appendChild(icon);
      button.appendChild(label);
      button.setAttribute("data-hs-daily-export", "true");
      setTranslatedAttribute(button, "aria-label", "Export");
    });
  }

  function prepareTableToolbar(wrapper) {
    var toolbar;
    var buttons;
    var filter;
    var filterInput;
    var filterLabel;
    if (!wrapper) return;
    toolbar = wrapper.querySelector(".hs-daily-table-toolbar");
    buttons = wrapper.querySelector(".dt-buttons");
    filter = wrapper.querySelector(".dataTables_filter");
    if (!buttons && !filter) return;
    if (!toolbar) {
      toolbar = document.createElement("div");
      toolbar.className = "hs-daily-table-toolbar";
      wrapper.insertBefore(toolbar, wrapper.firstChild);
    }
    if (filter) {
      filterInput = filter.querySelector("input");
      filterLabel = filter.querySelector("label");
      filter.classList.add("hs-daily-table-search");
      if (filterInput) {
        setTranslatedAttribute(filterInput, "placeholder", "Hledat");
        setTranslatedAttribute(filterInput, "aria-label", "Hledat");
      }
      if (filterLabel) setTranslatedAttribute(filterLabel, "aria-label", "Hledat");
      if (filter.parentNode !== toolbar) toolbar.appendChild(filter);
    }
    if (buttons && buttons.parentNode !== toolbar) toolbar.appendChild(buttons);
    normalizeExport(wrapper);
  }

  function enhanceTable(table) {
    var panel;
    var wrapper;
    var mobileHost;
    var scroll;
    var hint;
    if (!table) return;
    if (table.getAttribute("data-hs-daily-table") !== "true") {
      table.setAttribute("data-hs-daily-table", "true");
      table.classList.add("hs-daily-table");
    }
    panel = table.closest(".panel");
    if (panel) {
      panel.classList.add("hs-daily-card", "hs-daily-table-card");
      enhanceHeading(
        panel,
        "table",
        "Přehled tržeb za vybraný den",
        table.id === "table_DenniSumarZaJednotlivce" || table.id === "table_CelkoveTrzbyZaObsluhu" ?
          "Denní sumář za jednotlivce" : "Denní sumář za střediska | firmy"
      );
    }
    wrapper = normalizeTableStructure(table);
    if (wrapper) {
      wrapper.classList.add("hs-daily-table-wrapper");
      prepareTableToolbar(wrapper);
    }
    if (wrapper && !table.closest(".hs-daily-table-scroll")) {
      scroll = document.createElement("div");
      scroll.className = "hs-daily-table-scroll";
      table.parentNode.insertBefore(scroll, table);
      scroll.appendChild(table);
    }
    mobileHost = wrapper || table.closest(".table-responsive") || table.parentElement;
    labelTableCells(table);
    buildMobileCards(table, mobileHost);
    bindMobileCards(table);
    if (mobileHost && !mobileHost.querySelector(".hs-daily-table-hint")) {
      hint = document.createElement("p");
      hint.className = "hs-daily-table-hint";
      setTranslatedText(hint, "Na telefonu jsou jednotlivé záznamy zobrazené jako přehledné karty.");
      mobileHost.appendChild(hint);
    }
  }

  function chartInstanceFor(canvas) {
    var instances;
    var key;
    if (!window.Chart || !window.Chart.instances) return null;
    instances = window.Chart.instances;
    for (key in instances) {
      if (Object.prototype.hasOwnProperty.call(instances, key) && instances[key] && instances[key].chart && instances[key].chart.canvas === canvas) return instances[key];
      if (Object.prototype.hasOwnProperty.call(instances, key) && instances[key] && instances[key].canvas === canvas) return instances[key];
    }
    return null;
  }

  function chartCanvas(instance) {
    return instance && (instance.canvas || (instance.chart && instance.chart.canvas));
  }

  function chartContext(instance) {
    return instance && (instance.ctx || (instance.chart && instance.chart.ctx));
  }

  function numberValue(value) {
    var parsed = Number(String(value == null ? "" : value).replace(/\s/g, "").replace(",", "."));
    return isFinite(parsed) ? parsed : 0;
  }

  function datasetTotal(dataset) {
    var total = 0;
    var values = dataset && dataset.data ? dataset.data : [];
    values.forEach(function (value) { total += numberValue(value); });
    return total;
  }

  function chartUsesCurrency(canvas) {
    return !canvas || canvas.id !== "chart-area3";
  }

  function compactNumber(value) {
    var absolute = Math.abs(value);
    var rounded;
    if (absolute >= 1000000) {
      rounded = Math.round(value / 100000) / 10;
      return String(rounded).replace(".", ",") + " mil.";
    }
    try {
      return new Intl.NumberFormat("cs-CZ", { maximumFractionDigits: 0 }).format(value);
    } catch (ignore) {
      return String(Math.round(value)).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }
  }

  function detailedNumber(value, currency) {
    var formatted;
    try {
      formatted = new Intl.NumberFormat("cs-CZ", {
        minimumFractionDigits: currency ? 2 : 0,
        maximumFractionDigits: currency ? 2 : 0
      }).format(value);
    } catch (ignore) {
      formatted = currency ? numberValue(value).toFixed(2).replace(".", ",") : String(Math.round(value));
    }
    return formatted + (currency ? " Kč" : "");
  }

  function paletteColor(label) {
    var key = folded(label) || "polozka-" + chartColorCursor;
    if (!chartColorByLabel[key]) {
      chartColorByLabel[key] = CHART_PALETTE[chartColorCursor % CHART_PALETTE.length];
      chartColorCursor += 1;
    }
    return chartColorByLabel[key];
  }

  function arcModel(chart) {
    var meta;
    var arc;
    if (!chart || typeof chart.getDatasetMeta !== "function") return null;
    meta = chart.getDatasetMeta(0);
    arc = meta && meta.data && meta.data[0];
    if (!arc) return null;
    if (arc._model) return arc._model;
    if (arc._view) return arc._view;
    if (typeof arc.getProps === "function") return arc.getProps(["x", "y", "innerRadius", "outerRadius"], true);
    return null;
  }

  function fitCenterFont(context, text, maximumWidth, preferredSize) {
    var size = preferredSize;
    while (size > 12) {
      context.font = "700 " + size + "px Tahoma, Arial, sans-serif";
      if (context.measureText(text).width <= maximumWidth) break;
      size -= 1;
    }
    return size;
  }

  function registerChartCenterPlugin() {
    var service;
    if (chartCenterPluginRegistered || !window.Chart) return;
    service = window.Chart.pluginService || window.Chart.plugins;
    if (!service || typeof service.register !== "function") return;
    service.register({
      id: "hsDailyRevenueCenter",
      beforeDatasetsDraw: function (chart) {
        var center = chart && chart._hsDailyCenter;
        var model = arcModel(chart);
        var context = chartContext(chart);
        if (!center || !model || !context || !model.outerRadius || !model.innerRadius) return;
        context.save();
        context.beginPath();
        context.arc(model.x, model.y, (model.innerRadius + model.outerRadius) / 2, 0, Math.PI * 2);
        context.lineWidth = Math.max(1, model.outerRadius - model.innerRadius);
        context.strokeStyle = "#EEF3F6";
        context.stroke();
        context.restore();
      },
      afterDatasetsDraw: function (chart) {
        var center = chart && chart._hsDailyCenter;
        var model = arcModel(chart);
        var context = chartContext(chart);
        var valueText;
        var fontSize;
        if (!center || !model || !context || !model.innerRadius) return;
        valueText = compactNumber(center.total) + (center.currency ? " Kč" : "");
        context.save();
        context.textAlign = "center";
        context.textBaseline = "middle";
        fontSize = fitCenterFont(context, valueText, model.innerRadius * 1.55, Math.min(22, model.innerRadius * 0.32));
        context.font = "700 " + fontSize + "px Tahoma, Arial, sans-serif";
        context.fillStyle = "#203852";
        context.fillText(valueText, model.x, model.y - 5);
        context.font = "600 " + Math.max(9, Math.min(11, model.innerRadius * 0.16)) + "px Tahoma, Arial, sans-serif";
        context.fillStyle = "#8190A2";
        context.fillText(translate(center.captionSource || "Celkem").toUpperCase(), model.x, model.y + 16);
        context.restore();
      }
    });
    chartCenterPluginRegistered = true;
  }

  function syncChartLegend(instance, colors) {
    var canvas = chartCanvas(instance);
    var panel = canvas && canvas.closest ? canvas.closest(".panel") : null;
    var markers = panel ? panel.querySelectorAll(".hs-daily-chart-legend__color > div") : [];
    Array.prototype.forEach.call(markers, function (marker, index) {
      if (colors[index]) marker.style.backgroundColor = colors[index];
    });
  }

  function modernizeChartInstance(instance) {
    var datasets;
    var canvas;
    var currency;
    var labels;
    if (!instance || instance._hsDailyModernized) return;
    instance._hsDailyModernized = true;
    canvas = chartCanvas(instance);
    currency = chartUsesCurrency(canvas);
    labels = instance.data && instance.data.labels ? instance.data.labels : [];
    if (!instance._hsDailyLabelSources) instance._hsDailyLabelSources = labels.slice();
    labels = instance._hsDailyLabelSources.map(function (label) { return translate(cleanText(label)); });
    if (instance.data) instance.data.labels = labels;
    registerChartCenterPlugin();
    instance.options = instance.options || {};
    instance.options.responsive = true;
    instance.options.maintainAspectRatio = true;
    instance.options.cutoutPercentage = 72;
    instance.options.layout = instance.options.layout || {};
    instance.options.layout.padding = 8;
    instance.options.legend = instance.options.legend || {};
    instance.options.legend.position = "bottom";
    instance.options.legend.labels = instance.options.legend.labels || {};
    instance.options.legend.labels.fontFamily = "Tahoma, Arial, sans-serif";
    instance.options.legend.labels.fontColor = "#50647c";
    instance.options.legend.labels.fontSize = 12;
    instance.options.legend.labels.usePointStyle = true;
    instance.options.legend.labels.boxWidth = 10;
    instance.options.legend.labels.padding = 18;
    instance.options.tooltips = instance.options.tooltips || {};
    instance.options.tooltips.enabled = true;
    instance.options.tooltips.backgroundColor = "rgba(32, 51, 73, .96)";
    instance.options.tooltips.titleFontFamily = "Tahoma, Arial, sans-serif";
    instance.options.tooltips.bodyFontFamily = "Tahoma, Arial, sans-serif";
    instance.options.tooltips.titleFontSize = 13;
    instance.options.tooltips.bodyFontSize = 12;
    instance.options.tooltips.cornerRadius = 9;
    instance.options.tooltips.xPadding = 12;
    instance.options.tooltips.yPadding = 10;
    instance.options.tooltips.displayColors = true;
    instance.options.tooltips.callbacks = instance.options.tooltips.callbacks || {};
    instance.options.tooltips.callbacks.title = function () { return ""; };
    instance.options.tooltips.callbacks.label = function (tooltipItem, data) {
      var dataset = data.datasets[tooltipItem.datasetIndex || 0] || {};
      var value = numberValue(dataset.data && dataset.data[tooltipItem.index]);
      var total = datasetTotal(dataset);
      var label = cleanText(data.labels && data.labels[tooltipItem.index]) || translate("Hodnota");
      var percentage = total ? Math.round(value / total * 100) : 0;
      return label + ": " + detailedNumber(value, currency) + " (" + percentage + " %)";
    };
    instance.options.animation = instance.options.animation || {};
    instance.options.animation.duration = 650;
    instance.options.animation.easing = "easeOutQuart";
    instance.options.hover = instance.options.hover || {};
    instance.options.hover.animationDuration = 0;
    datasets = instance.data && instance.data.datasets ? instance.data.datasets : [];
    datasets.forEach(function (dataset) {
      var colors = instance._hsDailyLabelSources.map(function (label) { return paletteColor(label); });
      dataset.backgroundColor = colors;
      dataset.hoverBackgroundColor = colors;
      dataset.borderColor = "#ffffff";
      dataset.borderWidth = 4;
      dataset.hoverBorderColor = "#ffffff";
      dataset.hoverBorderWidth = 7;
      instance._hsDailyCenter = {
        total: datasetTotal(dataset),
        currency: currency,
        captionSource: currency ? "Celkem" : "Účtenek"
      };
      syncChartLegend(instance, colors);
    });
    if (typeof instance.resize === "function") instance.resize();
    if (typeof instance.update === "function") instance.update(0);
  }

  function chartTitleSource(element) {
    var id = element && element.id ? element.id : "";
    if (id === "chart-area") return "Služby dnes dle uživatelů";
    if (id === "chart-area2") return "Prodej dnes dle uživatelů";
    if (id === "chart-area3") return "Účtenky dle obsluhy";
    if (/^chart-area(?:5|6|7|8)$/.test(id)) return "Tržby dle typu platby";
    return "";
  }

  function refreshChartTranslations(main) {
    Array.prototype.forEach.call(main.querySelectorAll("canvas"), function (canvas) {
      var instance = chartInstanceFor(canvas);
      var sourceTitle = chartTitleSource(canvas);
      if (sourceTitle) setTranslatedAttribute(canvas, "aria-label", sourceTitle);
      if (!instance || !instance._hsDailyLabelSources || !instance.data) return;
      instance.data.labels = instance._hsDailyLabelSources.map(function (label) { return translate(cleanText(label)); });
      if (instance._hsDailyCenter) instance._hsDailyCenter.captionSource = chartUsesCurrency(canvas) ? "Celkem" : "Účtenek";
      if (typeof instance.update === "function") instance.update(0);
    });
  }

  function markChartLayout(canvas, panel) {
    var row;
    var plot;
    var wrapper;
    if (!canvas || !panel) return;
    plot = canvas.parentElement;
    if (plot) plot.classList.add("hs-daily-chart-plot");
    row = canvas.closest(".panel-body .row");
    wrapper = row ? topLevelChild(canvas, row) : null;
    if (wrapper) wrapper.classList.add("hs-daily-chart-plot-wrap");
  }

  function enhanceChartLegend(panel) {
    var tables;
    if (!panel) return;
    tables = panel.querySelectorAll(".panel-body table:not([id])");
    Array.prototype.forEach.call(tables, function (table) {
      if (table.getAttribute("data-hs-daily-legend") === "true") return;
      table.classList.add("hs-daily-chart-legend");
      table.setAttribute("data-hs-daily-legend", "true");
      Array.prototype.forEach.call(table.rows, function (row) {
        row.classList.add("hs-daily-chart-legend__item");
        if (row.cells[0]) row.cells[0].classList.add("hs-daily-chart-legend__color");
        if (row.cells[1]) row.cells[1].classList.add("hs-daily-chart-legend__label");
        if (row.cells[2]) row.cells[2].classList.add("hs-daily-chart-legend__value");
      });
    });
  }

  function enhanceCharts(main) {
    var canvases = main.querySelectorAll("canvas");
    var c3Charts = main.querySelectorAll(".c3");
    var vectorCharts = main.querySelectorAll("[id*='donut' i], [id*='pie' i], [id*='graf' i], [id*='chart' i]");
    Array.prototype.forEach.call(canvases, function (canvas) {
      var panel = canvas.closest(".panel");
      var headingLabel;
      canvas.classList.add("hs-daily-chart-canvas");
      canvas.setAttribute("role", "img");
      if (panel && !panel.classList.contains("hs-daily-table-card") && !panel.classList.contains("hs-daily-date-card")) {
        panel.classList.add("hs-daily-card", "hs-daily-chart-card");
        markChartLayout(canvas, panel);
        enhanceHeading(panel, "chart", "Podíl jednotlivých středisek a obsluh", chartTitleSource(canvas));
        enhanceChartLegend(panel);
        headingLabel = panel.querySelector(".hs-daily-heading__copy strong");
        if (headingLabel) setTranslatedAttribute(canvas, "aria-label", chartTitleSource(canvas) || cleanText(headingLabel.textContent));
      }
      modernizeChartInstance(chartInstanceFor(canvas));
    });
    Array.prototype.forEach.call(c3Charts, function (chart) {
      var panel = chart.closest(".panel");
      chart.classList.add("hs-daily-c3-chart");
      if (panel && !panel.classList.contains("hs-daily-table-card") && !panel.classList.contains("hs-daily-date-card")) {
        panel.classList.add("hs-daily-card", "hs-daily-chart-card");
        enhanceHeading(panel, "chart", "Podíl jednotlivých středisek a obsluh", chartTitleSource(chart));
        enhanceChartLegend(panel);
      }
    });
    Array.prototype.forEach.call(vectorCharts, function (chart) {
      var panel;
      if (!chart.querySelector("svg") || chart.closest(".hs-daily-heading, .hs-daily-options-actions")) return;
      panel = chart.closest(".panel");
      chart.classList.add("hs-daily-vector-chart");
      if (panel && !panel.classList.contains("hs-daily-table-card") && !panel.classList.contains("hs-daily-date-card")) {
        panel.classList.add("hs-daily-card", "hs-daily-chart-card");
        enhanceHeading(panel, "chart", "Podíl jednotlivých středisek a obsluh", chartTitleSource(chart));
        enhanceChartLegend(panel);
      }
    });
  }

  function refreshDynamicParts(main) {
    Array.prototype.forEach.call(main.querySelectorAll(TABLE_SELECTOR), enhanceTable);
    Array.prototype.forEach.call(main.querySelectorAll(".dataTables_wrapper"), function (wrapper) {
      if (wrapper.querySelector(TABLE_SELECTOR)) {
        wrapper.classList.add("hs-daily-table-wrapper");
        prepareTableToolbar(wrapper);
      }
    });
    enhanceCharts(main);
  }

  function initialize() {
    var main;
    var page;
    if (!isDailyRevenuePage()) return;
    main = document.getElementById("main-content");
    if (!main) return;
    main.classList.add("hs-daily-revenue");
    page = main.closest("section.main-content-wrapper");
    if (page) page.classList.add("hs-daily-revenue-page");
    enhanceSummaryWidgets(main);
    moveDataActions(main);
    enhanceDatePanel(main);
    refreshDynamicParts(main);

    window.setTimeout(function poll() {
      attempts += 1;
      refreshDynamicParts(main);
      if (attempts < 12) window.setTimeout(poll, 350);
    }, 350);
  }

  document.addEventListener("hs:languagechange", function () {
    window.setTimeout(function () {
      var main = document.getElementById("main-content");
      if (main && isDailyRevenuePage()) refreshChartTranslations(main);
    }, 0);
  });

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initialize);
  else initialize();
}());
