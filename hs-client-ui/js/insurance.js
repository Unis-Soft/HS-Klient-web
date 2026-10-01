/* HairSoft Klient V175 – číselník pojišťoven */
(function () {
  "use strict";

  var route = new URLSearchParams(window.location.search).get("strana") || "";
  if (route !== "NastaveniCiselnikPojistoven") return;

  var main;
  var table;
  var api = null;

  function tr(value) {
    return window.hsTranslate ? window.hsTranslate(value) : value;
  }

  function dataTableLanguage() {
    return {
      zeroRecords: tr("Nic nenalezeno"),
      emptyTable: tr("Žádné pojišťovny"),
      info: tr("Strana _PAGE_ z _PAGES_"),
      infoEmpty: "",
      infoFiltered: tr("(Celkem _MAX_ záznamů)"),
      search: "",
      paginate: {
        first: tr("První"),
        last: tr("Poslední"),
        next: tr("Další"),
        previous: tr("Předešlá")
      }
    };
  }

  function moveFilter() {
    if (!table) return;
    var wrapper = document.getElementById(table.id + "_wrapper");
    var filter = wrapper && wrapper.querySelector(".dataTables_filter");
    var toolbar = main && main.querySelector(".hs-insurance-table-toolbar");
    if (filter && toolbar && filter.parentNode !== toolbar) toolbar.appendChild(filter);
    var input = filter && filter.querySelector("input");
    if (input) {
      input.setAttribute("placeholder", tr("Hledat v pojišťovnách…"));
      input.setAttribute("aria-label", tr("Hledat v pojišťovnách…"));
    }
  }

  function cloneAction(source, extraClass) {
    if (!source) return null;
    var a = source.cloneNode(true);
    a.classList.add(extraClass || "");
    return a;
  }

  function buildMobileCards() {
    var target = main && main.querySelector(".hs-insurance-mobile-cards");
    if (!target || !table) return;
    target.textContent = "";

    var rows = [];
    var hasApi = !!(api && typeof api.rows === "function");
    if (hasApi) {
      try {
        api.rows({ page: "current", search: "applied" }).every(function () {
          if (this.node()) rows.push(this.node());
        });
      } catch (error) {}
    } else if (table.tBodies.length) {
      rows = Array.prototype.slice.call(table.tBodies[0].rows);
    }

    rows.forEach(function (row) {
      if (!row.cells || row.cells.length < 6) return;
      var card = document.createElement("article");
      card.className = "hs-insurance-mobile-card";

      var name = document.createElement("strong");
      name.className = "hs-insurance-mobile-card-name";
      name.textContent = row.cells[1].textContent.trim();
      card.appendChild(name);

      var country = document.createElement("div");
      country.className = "hs-insurance-mobile-card-country";
      var countrySource = row.cells[4].querySelector(".hs-insurance-country");
      country.appendChild(countrySource ? countrySource.cloneNode(true) : document.createTextNode(row.cells[4].textContent.trim()));
      card.appendChild(country);

      var meta = document.createElement("div");
      meta.className = "hs-insurance-mobile-card-meta";
      [["Kód pojišťovny", 2], ["Zkratka", 3]].forEach(function (item) {
        var box = document.createElement("div");
        var label = document.createElement("span");
        var value = document.createElement("strong");
        label.textContent = tr(item[0]);
        value.textContent = row.cells[item[1]].textContent.trim() || "—";
        box.appendChild(label);
        box.appendChild(value);
        meta.appendChild(box);
      });
      card.appendChild(meta);

      var actions = document.createElement("div");
      actions.className = "hs-insurance-mobile-card-actions";
      var edit = cloneAction(row.cells[0].querySelector("a"), "hs-insurance-mobile-edit");
      var del = cloneAction(row.cells[5].querySelector("a"), "hs-insurance-mobile-delete");
      if (edit) actions.appendChild(edit);
      if (del) actions.appendChild(del);
      card.appendChild(actions);
      target.appendChild(card);
    });
  }

  function initDataTable() {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable || !table) {
      buildMobileCards();
      return;
    }
    var jq = window.jQuery;
    try {
      if (jq.fn.dataTable.isDataTable && jq.fn.dataTable.isDataTable(table)) {
        api = jq(table).DataTable();
      } else {
        api = jq(table).DataTable({
          searching: true,
          paging: true,
          pageLength: 10,
          lengthChange: false,
          info: true,
          ordering: true,
          responsive: false,
          order: [[4, "asc"], [1, "asc"]],
          columnDefs: [
            { orderable: false, targets: [0, 5] },
            { className: "text-center", targets: [0, 2, 3, 4, 5] }
          ],
          language: dataTableLanguage()
        });
      }
      jq(table).off("draw.dt.hsInsurance").on("draw.dt.hsInsurance", function () {
        moveFilter();
        buildMobileCards();
      });
    } catch (error) {}
    moveFilter();
    buildMobileCards();
  }

  function refreshTranslations() {
    moveFilter();
    buildMobileCards();
    if (!api) return;
    try {
      var settings = api.settings()[0];
      var lang = dataTableLanguage();
      settings.oLanguage.sZeroRecords = lang.zeroRecords;
      settings.oLanguage.sEmptyTable = lang.emptyTable;
      settings.oLanguage.sInfo = lang.info;
      settings.oLanguage.sInfoEmpty = lang.infoEmpty;
      settings.oLanguage.sInfoFiltered = lang.infoFiltered;
      if (settings.oLanguage.oPaginate) {
        settings.oLanguage.oPaginate.sFirst = lang.paginate.first;
        settings.oLanguage.oPaginate.sLast = lang.paginate.last;
        settings.oLanguage.oPaginate.sNext = lang.paginate.next;
        settings.oLanguage.oPaginate.sPrevious = lang.paginate.previous;
      }
      api.draw(false);
    } catch (error) {}
  }

  function init() {
    main = document.getElementById("main-content");
    if (!main || !main.classList.contains("hs-insurance-page")) return;
    table = document.getElementById("hs_insurance_table");
    initDataTable();
    document.addEventListener("hs:languagechange", refreshTranslations);
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
}());
