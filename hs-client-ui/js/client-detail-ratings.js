/* HairSoft Klient V86 – moderní Hodnocení na PC a mobilu. */
(function () {
  "use strict";

  var TABLE_SELECTOR = "#TabulkaHodnoceni";
  var activePopoverTrigger = null;

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function svgIcon(name) {
    if (name === "star") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3z"></path></svg>';
    }
    if (name === "person") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5.5 20c.5-4 2.7-6 6.5-6s6 2 6.5 6"></path></svg>';
    }
    if (name === "message") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5.5h14v10H9l-4 3v-13z"></path></svg>';
    }
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"></path></svg>';
  }

  function plainCellText(cell) {
    var clone = cell.cloneNode(true);
    var comments = clone.querySelectorAll(".CellComment");
    var index;
    for (index = 0; index < comments.length; index += 1) comments[index].remove();
    return String(clone.textContent || "").replace(/\s+/g, " ").trim();
  }

  function commentText(comment) {
    var holder;
    var text;
    if (!comment) return "";

    holder = document.createElement("div");
    holder.innerHTML = String(comment.innerHTML || "")
      .replace(/<br\s*\/?\s*>/gi, "\n")
      .replace(/<hr\b[^>]*>/gi, "\n");
    text = String(holder.textContent || "")
      .replace(/\r/g, "")
      .replace(/[ \t]+/g, " ")
      .replace(/\n+/g, "\n")
      .trim();
    return text;
  }

  function commentDetails(comment) {
    var text = commentText(comment);
    var question = "";
    var answer = "";
    var questionMatch;
    var answerMatch;
    if (!text) return { question: question, answer: answer };

    questionMatch = text.match(/Otázka\s*:\s*([^\n]*?)(?=\n|Odpověď\s*:|$)/i);
    answerMatch = text.match(/Odpověď\s*:\s*([\s\S]*)$/i);
    if (questionMatch) question = questionMatch[1].trim();
    if (answerMatch) answer = answerMatch[1].trim();
    return { question: question, answer: answer };
  }

  function clientDetails(comment) {
    var text = commentText(comment);
    var lines = text ? text.split("\n") : [];
    var phoneMatch = text.match(/Tel\s*:\s*([^\n]*)/i);
    var emailMatch = text.match(/Email\s*:\s*([^\n]*)/i);
    return {
      name: lines.length ? lines[0].trim() : "",
      phone: phoneMatch ? phoneMatch[1].trim() : "",
      email: emailMatch ? emailMatch[1].trim() : ""
    };
  }

  function setPopoverData(trigger, type, title, question, answer, phone, email) {
    if (!trigger) return;
    trigger.setAttribute("data-hs-rating-popover", type);
    trigger.setAttribute("data-hs-popover-title", title || "");
    trigger.setAttribute("data-hs-popover-question", question || "");
    trigger.setAttribute("data-hs-popover-answer", answer || "");
    trigger.setAttribute("data-hs-popover-phone", phone || "");
    trigger.setAttribute("data-hs-popover-email", email || "");
    trigger.removeAttribute("title");
  }

  function appendMobileContact(meta, label, value) {
    var item;
    var labelElement;
    var valueElement;
    if (!value) return;
    item = document.createElement("span");
    item.className = "hs-rating-mobile-client-contact";
    labelElement = document.createElement("span");
    labelElement.className = "hs-rating-mobile-client-contact__label";
    labelElement.textContent = translate(label);
    valueElement = document.createElement("span");
    valueElement.className = "hs-rating-mobile-client-contact__value";
    valueElement.textContent = value;
    item.appendChild(labelElement);
    item.appendChild(valueElement);
    meta.appendChild(item);
  }

  function decorateClientCell(cell) {
    var link;
    var comment;
    var details;
    var meta;
    if (!cell || cell.getAttribute("data-hs-rating-client") === "true") return;
    link = cell.querySelector("a");
    comment = cell.querySelector(".CellComment");
    details = clientDetails(comment);
    if (link) {
      link.classList.add("hs-rating-client-link");
      setPopoverData(link, "client", details.name || String(link.textContent || "").trim(), "", "", details.phone, details.email);
      link.setAttribute("aria-label", String(link.textContent || "").trim() + " – " + translate("Kontaktní údaje"));
    }
    meta = document.createElement("span");
    meta.className = "hs-rating-mobile-client-meta";
    appendMobileContact(meta, "Telefon", details.phone);
    appendMobileContact(meta, "Email", details.email);
    if (meta.children.length) cell.insertBefore(meta, comment || null);
    cell.setAttribute("data-hs-rating-client", "true");
  }

  function decorateQuestionCell(cell, fallbackLabel) {
    var visible;
    var comment;
    var details;
    var desktopValue;
    var mobileQuestion;
    var mobileAnswer;
    var score;
    var isText;
    if (!cell || cell.getAttribute("data-hs-rating-cell") === "true") return;

    visible = plainCellText(cell);
    comment = cell.querySelector(".CellComment");
    details = commentDetails(comment);
    score = /^\d+(?:[.,]\d+)?$/.test(visible);
    isText = visible === "Text";

    desktopValue = document.createElement(details.question ? "button" : "span");
    if (desktopValue.tagName === "BUTTON") desktopValue.type = "button";
    desktopValue.className = "hs-rating-desktop-value" + (score ? " hs-rating-value--score" : isText ? " hs-rating-value--text" : "");
    desktopValue.textContent = visible || "-";
    if (details.question) {
      desktopValue.classList.add("hs-rating-popover-trigger");
      setPopoverData(
        desktopValue,
        "question",
        isText ? translate("Textová odpověď") : fallbackLabel,
        details.question,
        details.answer || (score ? visible : ""),
        "",
        ""
      );
      desktopValue.setAttribute("aria-label", isText ? translate("Zobrazit textovou odpověď") : details.question);
    }

    mobileQuestion = document.createElement("span");
    mobileQuestion.className = "hs-rating-mobile-question";
    mobileQuestion.textContent = details.question || fallbackLabel;

    mobileAnswer = document.createElement("span");
    mobileAnswer.className = "hs-rating-mobile-answer" + (score ? " hs-rating-value--score" : "");
    mobileAnswer.textContent = details.answer || visible || "-";

    while (cell.firstChild) cell.removeChild(cell.firstChild);
    cell.appendChild(desktopValue);
    cell.appendChild(mobileQuestion);
    cell.appendChild(mobileAnswer);
    if (comment) cell.appendChild(comment);
    cell.classList.add("hs-rating-question");
    if (isText) cell.classList.add("hs-rating-question--text");
    if (!visible || visible === "-") cell.classList.add("hs-rating-question--empty");
    cell.removeAttribute("title");
    cell.setAttribute("data-hs-rating-cell", "true");
  }

  function decorateRows(table) {
    var rows = table.tBodies.length ? table.tBodies[0].rows : [];
    var questionHeaders = table.tHead && table.tHead.rows.length > 1 ? table.tHead.rows[1].cells : [];
    var rowIndex;
    var cellIndex;
    var row;
    var labels = ["", "Termín objednávky", "Kdy bylo hodnoceno", "Jméno zákazníka", "Obsluha"];

    for (rowIndex = 0; rowIndex < rows.length; rowIndex += 1) {
      row = rows[rowIndex];
      if (!row.cells.length || row.cells[0].classList.contains("dataTables_empty")) continue;
      row.classList.add("hs-rating-row");
      for (cellIndex = 0; cellIndex < Math.min(5, row.cells.length); cellIndex += 1) {
        row.cells[cellIndex].classList.add("hs-rating-base", "hs-rating-base--" + cellIndex);
        if (labels[cellIndex]) row.cells[cellIndex].setAttribute("data-hs-label", translate(labels[cellIndex]));
      }
      decorateClientCell(row.cells[3]);
      for (cellIndex = 5; cellIndex < row.cells.length; cellIndex += 1) {
        decorateQuestionCell(
          row.cells[cellIndex],
          questionHeaders[cellIndex - 5] ? String(questionHeaders[cellIndex - 5].textContent || "").trim() : translate("Otázka") + " " + (cellIndex - 4)
        );
      }
    }
  }

  function ensurePopover() {
    var popover = document.getElementById("hs-rating-popover");
    if (popover) return popover;
    popover = document.createElement("div");
    popover.id = "hs-rating-popover";
    popover.className = "hs-rating-popover";
    popover.setAttribute("role", "tooltip");
    popover.hidden = true;
    document.body.appendChild(popover);
    return popover;
  }

  function appendPopoverRow(container, label, value) {
    var row;
    var labelElement;
    var valueElement;
    if (!value) return;
    row = document.createElement("div");
    row.className = "hs-rating-popover__row";
    labelElement = document.createElement("span");
    labelElement.className = "hs-rating-popover__label";
    labelElement.textContent = translate(label);
    valueElement = document.createElement("strong");
    valueElement.className = "hs-rating-popover__value";
    valueElement.textContent = value;
    row.appendChild(labelElement);
    row.appendChild(valueElement);
    container.appendChild(row);
  }

  function positionPopover(popover, trigger) {
    var rect = trigger.getBoundingClientRect();
    var popoverRect = popover.getBoundingClientRect();
    var margin = 12;
    var left = rect.left + (rect.width - popoverRect.width) / 2;
    var top = rect.bottom + 9;
    if (left < margin) left = margin;
    if (left + popoverRect.width > window.innerWidth - margin) left = window.innerWidth - popoverRect.width - margin;
    if (top + popoverRect.height > window.innerHeight - margin) top = rect.top - popoverRect.height - 9;
    if (top < margin) top = margin;
    popover.style.left = Math.round(left) + "px";
    popover.style.top = Math.round(top) + "px";
  }

  function showPopover(trigger) {
    var popover;
    var type;
    var header;
    var icon;
    var copy;
    var title;
    var subtitle;
    var body;
    var question;
    var answer;
    if (!trigger) return;
    popover = ensurePopover();
    if (activePopoverTrigger === trigger && !popover.hidden) return;
    popover.hidden = true;
    activePopoverTrigger = null;
    type = trigger.getAttribute("data-hs-rating-popover");
    while (popover.firstChild) popover.removeChild(popover.firstChild);

    header = document.createElement("div");
    header.className = "hs-rating-popover__header";
    icon = document.createElement("span");
    icon.className = "hs-rating-popover__icon";
    icon.innerHTML = svgIcon(type === "client" ? "person" : "message");
    copy = document.createElement("span");
    copy.className = "hs-rating-popover__copy";
    title = document.createElement("strong");
    title.textContent = trigger.getAttribute("data-hs-popover-title") || "";
    subtitle = document.createElement("small");
    subtitle.textContent = type === "client" ? translate("Kontaktní údaje") : translate("Hodnocení otázky");
    copy.appendChild(title);
    copy.appendChild(subtitle);
    header.appendChild(icon);
    header.appendChild(copy);
    popover.appendChild(header);

    body = document.createElement("div");
    body.className = "hs-rating-popover__body";
    if (type === "client") {
      appendPopoverRow(body, "Telefon", trigger.getAttribute("data-hs-popover-phone"));
      appendPopoverRow(body, "Email", trigger.getAttribute("data-hs-popover-email"));
    } else {
      question = document.createElement("p");
      question.className = "hs-rating-popover__question";
      question.textContent = trigger.getAttribute("data-hs-popover-question") || "";
      body.appendChild(question);
      answer = trigger.getAttribute("data-hs-popover-answer") || "";
      if (answer) appendPopoverRow(body, "Odpověď", answer);
    }
    if (!body.children.length) return;
    popover.appendChild(body);
    popover.hidden = false;
    activePopoverTrigger = trigger;
    window.requestAnimationFrame(function () { positionPopover(popover, trigger); });
  }

  function hidePopover() {
    var popover = document.getElementById("hs-rating-popover");
    if (popover) popover.hidden = true;
    activePopoverTrigger = null;
  }

  function setupPopovers(table) {
    if (table.getAttribute("data-hs-ratings-popovers") === "true") return;
    table.addEventListener("mouseover", function (event) {
      var trigger = event.target.closest("[data-hs-rating-popover]");
      if (trigger && table.contains(trigger)) showPopover(trigger);
    });
    table.addEventListener("mouseout", function (event) {
      var trigger = event.target.closest("[data-hs-rating-popover]");
      if (trigger && !trigger.contains(event.relatedTarget)) hidePopover();
    });
    table.addEventListener("focusin", function (event) {
      var trigger = event.target.closest("[data-hs-rating-popover]");
      if (trigger) showPopover(trigger);
    });
    table.addEventListener("focusout", function (event) {
      var trigger = event.target.closest("[data-hs-rating-popover]");
      if (trigger && !trigger.contains(event.relatedTarget)) hidePopover();
    });
    table.addEventListener("click", function (event) {
      var trigger = event.target.closest("button[data-hs-rating-popover]");
      if (!trigger) return;
      event.preventDefault();
      if (activePopoverTrigger === trigger) hidePopover();
      else showPopover(trigger);
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") hidePopover();
    });
    window.addEventListener("resize", hidePopover);
    window.addEventListener("scroll", hidePopover, true);
    table.setAttribute("data-hs-ratings-popovers", "true");
  }

  function decorateHeader(table) {
    var firstRow;
    var secondRow;
    var index;
    if (!table.tHead || table.tHead.rows.length < 2) return;
    firstRow = table.tHead.rows[0];
    secondRow = table.tHead.rows[1];
    firstRow.classList.add("hs-ratings-header-main");
    secondRow.classList.add("hs-ratings-header-questions");
    for (index = 0; index < firstRow.cells.length; index += 1) {
      firstRow.cells[index].classList.add(index < 5 ? "hs-ratings-header-base" : "hs-ratings-header-group");
    }
    for (index = 0; index < secondRow.cells.length; index += 1) {
      secondRow.cells[index].classList.add("hs-ratings-header-question");
    }
  }

  function buildHeading(panel) {
    var heading = panel ? panel.querySelector(":scope > .panel-heading") : null;
    var title = heading ? heading.querySelector(".panel-title") : null;
    var actions = heading ? heading.querySelector(".actions") : null;
    if (!heading || !title || heading.getAttribute("data-hs-ratings-heading") === "true") return;

    title.innerHTML = '<span class="hs-ratings-heading__icon">' + svgIcon("star") + '</span><span class="hs-ratings-heading__copy"><strong>' + translate("Přehled hodnocení") + '</strong><small>' + translate("Všechny otázky jsou dostupné v přehledu hodnocení.") + '</small></span>';
    if (actions) actions.hidden = true;
    heading.setAttribute("data-hs-ratings-heading", "true");
  }

  function normalizeControls(wrapper) {
    var exportButton = wrapper.querySelector(".dt-buttons .buttons-collection");
    var filterInput = wrapper.querySelector(".dataTables_filter input");
    if (exportButton) {
      exportButton.innerHTML = '<span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"></path></svg><b>' + translate("Export") + '</b></span>';
      exportButton.setAttribute("aria-label", translate("Export"));
    }
    if (filterInput) {
      filterInput.setAttribute("placeholder", translate("Hledat"));
      filterInput.setAttribute("aria-label", translate("Hledat"));
    }
  }

  function prepareLayout(table, wrapper) {
    var responsive = table.closest(".table-responsive");
    var scroll = wrapper.querySelector(".hs-ratings-table-scroll");
    var breaks;
    var index;
    if (!scroll) {
      scroll = document.createElement("div");
      scroll.className = "hs-ratings-table-scroll";
      table.parentNode.insertBefore(scroll, table);
      scroll.appendChild(table);
    }
    if (responsive) {
      responsive.classList.add("hs-ratings-table-panel");
      breaks = responsive.querySelectorAll(":scope > br");
      for (index = 0; index < breaks.length; index += 1) breaks[index].remove();
    }
  }

  function initialize(attempt) {
    var table = document.querySelector(TABLE_SELECTOR);
    var jq = window.jQuery;
    var api;
    var wrapper;
    var pane;
    var tabs;
    var panel;

    if (!table) return;
    if (!jq || !jq.fn || !jq.fn.DataTable || !jq.fn.dataTable.isDataTable(table)) {
      if (attempt < 80) window.setTimeout(function () { initialize(attempt + 1); }, 75);
      return;
    }
    if (table.getAttribute("data-hs-ratings-enhanced") === "true") return;

    api = jq(table).DataTable();
    wrapper = table.closest(".dataTables_wrapper");
    pane = table.closest("#Hodnoceni") || table.closest(".hs-feedback-stats");
    tabs = table.closest(".hs-client-detail-tabs");
    panel = table.closest(".panel");
    if (!wrapper || !pane || !panel) return;

    pane.classList.add("hs-ratings-pane");
    panel.classList.add("hs-ratings-panel");
    decorateHeader(table);
    buildHeading(panel);
    prepareLayout(table, wrapper);
    normalizeControls(wrapper);
    setupPopovers(table);

    function updateTabState() {
      if (tabs) tabs.classList.toggle("hs-client-detail-tabs--ratings-active", pane.classList.contains("active"));
    }

    jq(table)
      .off("draw.dt.hsRatings")
      .on("draw.dt.hsRatings", function () {
        decorateRows(table);
        normalizeControls(wrapper);
      });
    jq('a[data-toggle="tab"]')
      .off("shown.bs.tab.hsRatings")
      .on("shown.bs.tab.hsRatings", function () {
        updateTabState();
        if (this.getAttribute("href") === "#Hodnoceni") {
          api.columns.adjust();
          decorateRows(table);
        }
      });

    decorateRows(table);
    updateTabState();
    table.setAttribute("data-hs-ratings-enhanced", "true");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { initialize(0); }, { once: true });
  } else {
    initialize(0);
  }
}());
