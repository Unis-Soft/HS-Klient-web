/* HairSoft Klient V77 – moderní a stránkovaný SMS Chat. */
(function () {
  "use strict";

  var TABLE_SELECTOR = "#zakaznici_tabulka_chat";

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function infoText() {
    var language = String(document.documentElement.lang || "cs").toLowerCase();
    if (language.indexOf("en") === 0) return "Page _PAGE_ of _PAGES_";
    if (language.indexOf("de") === 0) return "Seite _PAGE_ von _PAGES_";
    return "Strana _PAGE_ z _PAGES_";
  }

  function prepareStructure(table) {
    var rows = Array.prototype.slice.call(table.rows || []);
    var headerRow;
    var thead;
    var index;

    if (!rows.length) return false;
    headerRow = rows[0];
    if (!table.tHead) {
      thead = document.createElement("thead");
      table.insertBefore(thead, table.firstChild);
      thead.appendChild(headerRow);
    }

    headerRow.classList.add("hs-sms-chat-header");
    for (index = 0; index < headerRow.cells.length; index += 1) {
      headerRow.cells[index].setAttribute("scope", "col");
    }
    table.classList.add("hs-sms-chat-table");
    return true;
  }

  function decorateRows(table) {
    var rows = table.tBodies.length ? table.tBodies[0].rows : [];
    var index;
    var row;
    var salon;
    var customer;

    for (index = 0; index < rows.length; index += 1) {
      row = rows[index];
      if (row.cells.length < 3 || row.cells[0].classList.contains("dataTables_empty")) continue;
      salon = String(row.cells[1].textContent || "").trim();
      customer = String(row.cells[2].textContent || "").trim();
      row.classList.toggle("hs-sms-chat-row--salon", salon !== "");
      row.classList.toggle("hs-sms-chat-row--customer", customer !== "");
      row.cells[1].classList.toggle("hs-sms-chat-empty", salon === "");
      row.cells[2].classList.toggle("hs-sms-chat-empty", customer === "");
    }
  }

  function initialize(attempt) {
    var table = document.querySelector(TABLE_SELECTOR);
    var jq = window.jQuery;
    var api;
    var pane;
    var tabs;

    function updateTabState() {
      if (tabs && pane) tabs.classList.toggle("hs-client-detail-tabs--sms-chat-active", pane.classList.contains("active"));
    }

    if (!table || !prepareStructure(table)) return;
    if (!jq || !jq.fn || !jq.fn.DataTable) {
      if (attempt < 80) window.setTimeout(function () { initialize(attempt + 1); }, 75);
      return;
    }

    if (jq.fn.dataTable.isDataTable(table)) {
      api = jq(table).DataTable();
    } else {
      api = jq(table).DataTable({
        autoWidth: false,
        responsive: false,
        searching: false,
        ordering: false,
        paging: true,
        bPaginate: true,
        pageLength: 25,
        lengthChange: false,
        info: true,
        dom: "tip",
        language: {
          zeroRecords: translate("Nic nenalezeno"),
          emptyTable: translate("Nic nenalezeno"),
          info: infoText(),
          infoEmpty: "",
          infoFiltered: "",
          paginate: {
            first: translate("První"),
            last: translate("Poslední"),
            next: translate("Další"),
            previous: translate("Předešlá")
          }
        }
      });
    }

    jq(table)
      .off("draw.dt.hsSmsChat")
      .on("draw.dt.hsSmsChat", function () { decorateRows(table); });
    pane = table.closest("#SMSChat");
    tabs = table.closest(".hs-client-detail-tabs");
    jq('a[data-toggle="tab"]')
      .off("shown.bs.tab.hsSmsChat")
      .on("shown.bs.tab.hsSmsChat", function () {
        updateTabState();
        if (this.getAttribute("href") === "#SMSChat") {
          api.columns.adjust();
          decorateRows(table);
        }
      });

    decorateRows(table);
    updateTabState();
    table.setAttribute("data-hs-sms-chat-enhanced", "true");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () { initialize(0); }, { once: true });
  } else {
    initialize(0);
  }
}());
