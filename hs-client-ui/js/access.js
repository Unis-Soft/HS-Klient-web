/* HairSoft Klient V180 – Nastavení > Přístupy */
(function () {
  "use strict";

  var route = new URLSearchParams(window.location.search).get("strana") || "";
  if (route !== "NastaveniPristupy" && route !== "NastaveniDetailUzivatele" && route !== "NastaveniDetailObsluhy") return;

  var main;
  var table;
  var api = null;

  function tr(value) { return window.hsTranslate ? window.hsTranslate(value) : value; }

  function dataTableLanguage() {
    return {
      zeroRecords: tr("Nic nenalezeno"),
      emptyTable: tr("Žádní uživatelé"),
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
    if (!table || !main) return;
    var wrapper = document.getElementById(table.id + "_wrapper");
    var filter = wrapper && wrapper.querySelector(".dataTables_filter");
    var toolbar = main.querySelector(".hs-access-table-toolbar");
    if (filter && toolbar && filter.parentNode !== toolbar) toolbar.appendChild(filter);
    var input = filter && filter.querySelector("input");
    if (input) {
      input.setAttribute("placeholder", tr("Hledat v přístupech…"));
      input.setAttribute("aria-label", tr("Hledat v přístupech…"));
    }
  }

  function rowsForCurrentPage() {
    var rows = [];
    if (api) {
      try {
        api.rows({ page: "current", search: "applied" }).every(function () {
          if (this.node()) rows.push(this.node());
        });
      } catch (error) {}
    } else if (table && table.tBodies.length) {
      rows = Array.prototype.slice.call(table.tBodies[0].rows);
    }
    return rows;
  }

  function cloneAction(source) {
    if (!source) return null;
    var clone = source.cloneNode(true);
    clone.addEventListener("click", function (event) { event.stopPropagation(); });
    return clone;
  }

  function buildMobileCards() {
    if (!main || !table) return;
    var target = main.querySelector(".hs-access-mobile-cards");
    if (!target) return;
    target.textContent = "";

    rowsForCurrentPage().forEach(function (row) {
      if (!row.cells || row.cells.length < 5) return;
      var card = document.createElement("article");
      card.className = "hs-access-mobile-card";
      card.tabIndex = 0;
      card.dataset.href = row.dataset.href || "";

      var avatar = row.cells[0].querySelector(".hs-access-avatar");
      if (avatar) card.appendChild(avatar.cloneNode(true));

      var copy = document.createElement("div");
      copy.className = "hs-access-mobile-card-copy";
      var name = document.createElement("strong");
      name.textContent = row.cells[2].textContent.trim();
      var email = document.createElement("span");
      email.textContent = row.cells[3].textContent.trim();
      copy.appendChild(name);
      copy.appendChild(email);
      card.appendChild(copy);

      var role = row.cells[1].querySelector(".hs-access-role");
      if (role) {
        var roleClone = role.cloneNode(true);
        roleClone.classList.add("hs-access-mobile-card-role");
        card.appendChild(roleClone);
      }

      var actions = document.createElement("div");
      actions.className = "hs-access-mobile-card-actions";
      row.cells[4].querySelectorAll("a").forEach(function (link) {
        var clone = cloneAction(link);
        if (clone) actions.appendChild(clone);
      });
      card.appendChild(actions);
      target.appendChild(card);
    });
  }

  function activateCard(element) {
    var href = element && element.dataset ? element.dataset.href : "";
    if (href) window.location.href = href;
  }

  function bindClickableRows() {
    if (!main) return;
    main.addEventListener("click", function (event) {
      var action = event.target.closest("a,button,input,select,label");
      if (action) return;
      var row = event.target.closest(".hs-access-clickable-row,.hs-access-mobile-card");
      if (row) activateCard(row);
    });
    main.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" && event.key !== " ") return;
      var row = event.target.closest(".hs-access-clickable-row,.hs-access-mobile-card");
      if (!row || event.target.closest("a,button,input,select,label")) return;
      event.preventDefault();
      activateCard(row);
    });
  }

  function initDataTable() {
    if (!table) return;
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
      buildMobileCards();
      return;
    }
    var jq = window.jQuery;
    try {
      api = jq(table).DataTable({
        searching: true,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        info: true,
        ordering: true,
        responsive: false,
        order: [[1, "asc"], [2, "asc"]],
        columnDefs: [
          { orderable: false, targets: [0, 4] },
          { className: "text-center", targets: [0, 1, 4] }
        ],
        language: dataTableLanguage()
      });
      jq(table).off("draw.dt.hsAccess").on("draw.dt.hsAccess", function () {
        moveFilter();
        buildMobileCards();
      });
    } catch (error) {}
    moveFilter();
    buildMobileCards();
  }

  function initProfileControls() {
    var select = document.getElementById("hs_access_profile_manager");
    if (select) select.addEventListener("change", function () { select.form.submit(); });

    var button = document.getElementById("hs_access_profile_upload");
    var file = document.getElementById("hs_access_profile_file");
    var form = document.getElementById("hs_access_profile_upload_form");
    if (button && file && form) {
      button.addEventListener("click", function () { file.click(); });
      file.addEventListener("change", function () {
        if (file.files && file.files.length) {
          button.disabled = true;
          form.submit();
        }
      });
    }
  }

  function validEmail(value) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value); }

  function initNewUserValidation() {
    var name = document.getElementById("hs_access_new_name");
    var email = document.getElementById("hs_access_new_email");
    var password = document.getElementById("hs_access_new_password");
    var submit = document.getElementById("hs_access_new_submit");
    if (!name || !email || !password || !submit) return;
    function update() {
      submit.disabled = name.value.trim().length < 2 || !validEmail(email.value.trim()) || password.value.length < 4;
    }
    [name, email, password].forEach(function (input) {
      input.addEventListener("input", update);
      input.addEventListener("change", update);
    });
    update();
  }

  function initDetailForms() {
    var branch = document.getElementById("hs_access_branch_select");
    if (branch) branch.addEventListener("change", function () { branch.form.submit(); });

    var password = document.getElementById("hs_access_password");
    var passwordSubmit = document.getElementById("hs_access_password_submit");
    if (password && passwordSubmit) {
      function updatePassword() { passwordSubmit.disabled = password.value.length < 4; }
      password.addEventListener("input", updatePassword);
      updatePassword();
    }

    var adminPassword = document.getElementById("hs_access_admin_password");
    var adminPasswordSubmit = document.getElementById("hs_access_admin_password_submit");
    if (adminPassword && adminPasswordSubmit) {
      function updateAdminPassword() { adminPasswordSubmit.disabled = adminPassword.value.length < 4; }
      adminPassword.addEventListener("input", updateAdminPassword);
      updateAdminPassword();
    }

    var permissions = document.getElementById("hs_access_permissions_form");
    if (permissions) {
      permissions.addEventListener("change", function (event) {
        if (event.target && event.target.matches('.hs-access-switch input[type="checkbox"]')) {
          var item = event.target.closest(".hs-access-permission-item");
          var status = item && item.querySelector("small");
          if (status) status.textContent = event.target.checked ? tr("Povoleno") : tr("Zakázáno");
          permissions.submit();
        }
      });
    }
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
    if (!main || !main.classList.contains("hs-access-page")) return;
    table = document.getElementById("hs_access_users_table");
    bindClickableRows();
    initDataTable();
    initProfileControls();
    initNewUserValidation();
    initDetailForms();
    document.addEventListener("hs:languagechange", refreshTranslations);
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
}());
