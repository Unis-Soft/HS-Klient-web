/* HairSoft Klient V173 – Uživatele. Klikací řádky/karty a výchozí sbalený archiv. */
(function () {
  "use strict";

  var route = new URLSearchParams(window.location.search).get("strana") || "";
  if (route !== "Uzivatele") return;

  var main;
  var permissionSources = new WeakMap();

  function tr(value) {
    return window.hsTranslate ? window.hsTranslate(value) : value;
  }

  function svg(paths) {
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' + paths + '</svg>';
  }

  var icons = {
    branch: svg('<path d="M3 21h18"></path><path d="M5 21V8l7-4 7 4v13"></path><path d="M9 21v-6h6v6"></path><path d="M9 10h.01M15 10h.01"></path>'),
    profile: svg('<circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path>'),
    account: svg('<rect x="4" y="10" width="16" height="11" rx="3"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path><path d="M12 14v3"></path>'),
    permissions: svg('<path d="M12 3l7 3v5c0 4.6-2.8 8.1-7 10-4.2-1.9-7-5.4-7-10V6l7-3z"></path><path d="m9 12 2 2 4-4"></path>'),
    users: svg('<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>'),
    archive: svg('<path d="M4 7h16"></path><path d="M5 7l1 14h12l1-14"></path><path d="M3 3h18v4H3z"></path><path d="M9 11h6"></path>'),
    refresh: svg('<path d="M20 7h-5V2"></path><path d="M20 7a8 8 0 1 0 1.2 7"></path>'),
    trash: svg('<path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path>'),
    restore: svg('<path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 4v6h6"></path><path d="M12 7v5l3 2"></path>'),
    edit: svg('<path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path>'),
    chevron: svg('<path d="m9 18 6-6-6-6"></path>')
  };

  function normalize(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
  }

  function addHeadingIcon(panel, iconName) {
    var heading = panel && panel.querySelector(":scope > .panel-heading");
    var title = heading && heading.querySelector(".panel-title");
    if (!heading || !title || heading.querySelector(".hs-users-heading-icon")) return;
    var icon = document.createElement("span");
    icon.className = "hs-users-heading-icon";
    icon.setAttribute("aria-hidden", "true");
    icon.innerHTML = icons[iconName] || icons.users;
    heading.insertBefore(icon, title);
  }

  function personName(panel) {
    var title = panel && panel.querySelector(":scope > .panel-heading .panel-title");
    var italic = title && title.querySelector("i");
    if (italic) return normalize(italic.textContent);
    var text = normalize(title && title.textContent);
    var split = text.indexOf(":");
    return split >= 0 ? normalize(text.slice(split + 1)) : "";
  }

  function setDynamicTitle(panel, sourcePrefix) {
    var title = panel && panel.querySelector(":scope > .panel-heading .panel-title");
    if (!title) return;
    var name = panel.dataset.hsUserName || personName(panel);
    if (name) panel.dataset.hsUserName = name;
    title.textContent = "";
    var label = document.createElement("span");
    label.className = "hs-users-heading-label";
    label.textContent = tr(sourcePrefix) + (name ? ": " : "");
    title.appendChild(label);
    if (name) {
      var person = document.createElement("span");
      person.className = "hs-users-heading-person";
      person.textContent = name;
      title.appendChild(person);
    }
  }

  function classifyPanels() {
    var panels = main.querySelectorAll(".panel");
    Array.prototype.forEach.call(panels, function (panel) {
      if (panel.closest(".hs-page-status")) return;
      var ownTitle = panel.querySelector(":scope > .panel-heading .panel-title");
      var title = normalize(ownTitle && ownTitle.textContent);
      var rating = panel.querySelector("#chck_Hodnoceni, #chck_HodnoceniVsech");
      var permissions = panel.querySelector('input[name="Prava_Dashboard"]');
      var activeTable = panel.querySelector("#table_prehledUzivateluObsluha");
      var archivedTable = panel.querySelector("#table_prehledUzivateluObsluhaArchiv");
      var branch = panel.querySelector("#VybranaPobockaID");
      var password = panel.querySelector("#k_poduzivatele_heslo");
      var importButton = panel.querySelector('input[name="ImportObsluhyZHS"], button[type="submit"]');

      if (panel.classList.contains("hs-users-account-native-v153")) {
        panel.classList.add("hs-users-card", "hs-users-account");
        panel.closest('[class*="col-"]') && panel.closest('[class*="col-"]').classList.add("hs-users-account-col");
        addHeadingIcon(panel, "account");
        setDynamicTitle(panel, "Detail obsluhy");
        return;
      }

      if (rating && rating.closest(".panel") === panel) {
        panel.classList.add("hs-users-rating-settings");
        return;
      }

      if (!title && !panel.querySelector("input, select, button, table, img") && !normalize(panel.textContent)) {
        panel.classList.add("hs-users-empty-panel");
        return;
      }

      panel.classList.add("hs-users-card");

      if (branch && title.indexOf("Výběr pobočky") !== -1) {
        panel.classList.add("hs-users-branch");
        addHeadingIcon(panel, "branch");
      } else if (title.indexOf("Profilovka") === 0) {
        panel.classList.add("hs-users-profile");
        panel.closest('[class*="col-"]') && panel.closest('[class*="col-"]').classList.add("hs-users-profile-col");
        addHeadingIcon(panel, "profile");
        setDynamicTitle(panel, "Profilovka");
      } else if (password || title.indexOf("Akce pro obsluhu") === 0) {
        panel.classList.add("hs-users-account");
        panel.closest('[class*="col-"]') && panel.closest('[class*="col-"]').classList.add("hs-users-account-col");
        addHeadingIcon(panel, "account");
        setDynamicTitle(panel, "Detail obsluhy");
      } else if (permissions || title.indexOf("Práva na menu pro obsluhu") === 0) {
        panel.classList.add("hs-users-permissions");
        panel.closest('[class*="col-"]') && panel.closest('[class*="col-"]').classList.add("hs-users-permissions-col");
        addHeadingIcon(panel, "permissions");
        setDynamicTitle(panel, "Oprávnění obsluhy");
      } else if (activeTable) {
        panel.classList.add("hs-users-list", "hs-users-list--active");
        addHeadingIcon(panel, "users");
      } else if (archivedTable) {
        panel.classList.add("hs-users-list", "hs-users-list--archive");
        addHeadingIcon(panel, "archive");
      } else if (title === "Akce" && importButton) {
        panel.classList.add("hs-users-actions");
        addHeadingIcon(panel, "refresh");
      } else if (title === "Informace") {
        panel.classList.add("hs-users-notice");
        if (panel.classList.contains("panel-danger")) panel.classList.add("hs-users-notice--error");
      }
    });

    var profile = main.querySelector(".hs-users-profile");
    var account = main.querySelector(".hs-users-account");
    var permissions = main.querySelector(".hs-users-permissions");
    if (profile && account && permissions) {
      var row = profile.closest(".row");
      if (row && row.contains(account) && row.contains(permissions)) row.classList.add("hs-users-detail-row");
    }
  }

  function prepareAccountForm(account) {
    if (!account) return;
    var form = account.querySelector('form.form-horizontal');
    if (!form) return;
    form.classList.add("hs-users-account-form");

    function markRow(selector, className) {
      var control = account.querySelector(selector);
      var row = control && control.closest(".form-group");
      if (row) row.classList.add(className);
    }

    markRow("#k_poduzivatele_jmeno", "hs-users-field-login");
    markRow("#k_poduzivatele_heslo", "hs-users-field-password");
    markRow("#btn_pridat_poduzivatele", "hs-users-field-save");
  }

  function mergeProfileIntoAccount() {
    var profile = main.querySelector(".hs-users-profile");
    var account = main.querySelector(".hs-users-account");
    var permissions = main.querySelector(".hs-users-permissions");
    if (!profile || !account) return;

    var body = account.querySelector(":scope > .panel-body");
    var photo = profile.querySelector(".OrizlaKulataFotkaObal");
    if (!body || !photo) return;

    /*
     * V152: detail obsluhy už nespoléhá na původní Bootstrap sloupce 2/6/4.
     * Panely pouze přesuneme do vlastní prezentační obálky. Formuláře, inputy,
     * POST názvy a eventy zůstávají beze změny.
     */
    var shell = main.querySelector(".hs-users-detail-shell");
    if (!shell) {
      var profileCol = profile.closest('[class*="col-"]');
      var accountCol = account.closest('[class*="col-"]');
      var permissionsCol = permissions && permissions.closest('[class*="col-"]');
      var anchorRow = (profileCol && profileCol.closest(".row")) || (accountCol && accountCol.closest(".row"));
      var anchor = anchorRow || profileCol || accountCol;

      shell = document.createElement("section");
      shell.className = "hs-users-detail-shell";
      if (anchor && anchor.parentNode) anchor.parentNode.insertBefore(shell, anchor);
      else main.appendChild(shell);

      shell.appendChild(account);
      if (permissions) shell.appendChild(permissions);

      [profileCol, accountCol, permissionsCol].forEach(function (col) {
        if (col) col.classList.add("hs-users-detail-source-col");
      });
    }

    if (account.dataset.hsProfileMerged !== "1") {
      var aside = document.createElement("aside");
      aside.className = "hs-users-account-profile";

      var photoSlot = document.createElement("div");
      photoSlot.className = "hs-users-account-profile-photo";
      photoSlot.appendChild(photo);
      aside.appendChild(photoSlot);

      var name = document.createElement("strong");
      name.className = "hs-users-account-profile-name";
      name.textContent = account.dataset.hsUserName || profile.dataset.hsUserName || personName(account) || personName(profile);
      aside.appendChild(name);

      var role = document.createElement("span");
      role.className = "hs-users-account-profile-role";
      role.textContent = tr("Obsluha");
      aside.appendChild(role);

      body.classList.add("hs-users-account-body--merged");
      body.insertBefore(aside, body.firstChild);
      account.dataset.hsProfileMerged = "1";
    }

    prepareAccountForm(account);
  }

  function updateImportButtonLabel() {
    var button = main.querySelector(".hs-users-branch-import button.btn-info");
    if (!button) return;
    var label = button.querySelector(".hs-users-import-label");
    if (!label) {
      Array.prototype.forEach.call(button.childNodes, function (node) {
        if (node.nodeType === 3) button.removeChild(node);
      });
      label = document.createElement("span");
      label.className = "hs-users-import-label";
      button.appendChild(label);
    }
    label.textContent = tr("Import obsluh z HairSoft");
  }

  function relocateImportToBranch() {
    var branch = main.querySelector(".hs-users-branch");
    var actions = main.querySelector(".hs-users-actions");
    if (!branch || !actions) return;
    if (branch.querySelector(".hs-users-branch-import")) {
      updateImportButtonLabel();
      return;
    }

    var form = actions.querySelector('form input[name="ImportObsluhyZHS"]');
    form = form && form.closest("form");
    var body = branch.querySelector(":scope > .panel-body");
    if (!form || !body) return;

    var wrap = document.createElement("div");
    wrap.className = "hs-users-branch-import";
    wrap.appendChild(form);
    body.appendChild(wrap);
    updateImportButtonLabel();

    var row = actions.closest(".row");
    if (row) row.classList.add("hs-users-actions-row--moved");
    else actions.style.display = "none";
  }

  function moveTableFilter(table, wrapper) {
    if (!table || !wrapper) return;
    var panel = table.closest(".hs-users-list");
    var body = panel && panel.querySelector(":scope > .panel-body");
    var responsive = table.closest(".table-responsive");
    var filter = wrapper.querySelector(".dataTables_filter");
    if (!panel || !body || !responsive || !filter) return;

    var toolbar = panel.querySelector(".hs-users-table-toolbar");
    if (!toolbar) {
      toolbar = document.createElement("div");
      toolbar.className = "hs-users-table-toolbar";
      body.insertBefore(toolbar, responsive);
    }

    if (filter.parentNode !== toolbar) {
      var oldRow = filter.closest(".row");
      var oldRowIsDataTablesRow = !!(oldRow && wrapper.contains(oldRow));
      toolbar.appendChild(filter);

      /*
       * V149: nikdy neskrývat nejbližší Bootstrap .row mimo DataTables wrapper.
       * Na stránce Uživatelé mohl být .dataTables_filter přímo ve wrapperu bez
       * vlastního DataTables .row; closest(".row") pak našel celý řádek panelu
       * Seznam obsluh a V148 tím schovala kompletní tabulku.
       */
      if (oldRowIsDataTablesRow) {
        var hasRemainingControl = oldRow.querySelector(
          ".dataTables_length, .dataTables_filter, .dt-buttons, input, select, button, a"
        );
        var hasTable = oldRow.querySelector("table");
        if (!hasRemainingControl && !hasTable) oldRow.classList.add("hs-users-dt-top-row-hidden");
      }
    }
  }

  var permissionDisplay = {
    Dashboard: "Dashboard",
    NovyZakaznik: "Nový zákazník",
    "Nový zákazník": "Nový zákazník",
    Zakaznici: "Zákazníci",
    "Zákazníci": "Zákazníci",
    Rezervace: "Rezervace",
    Trzby: "Tržby",
    "Tržby": "Tržby",
    Sklad: "Sklad",
    Voucher: "Voucher",
    Hodnoceni: "Hodnocení",
    "Hodnocení": "Hodnocení",
    SMS: "SMS a hovory",
    Uzivatele: "Uživatelé",
    "Uživatelé": "Uživatelé",
    Cenik: "Ceník",
    "Ceník": "Ceník",
    Kamery: "Kamery",
    Nastaveni: "Nastavení",
    "Nastavení": "Nastavení"
  };

  function enhancePermissionCell(cell) {
    if (!cell) return;
    var sources = permissionSources.get(cell);
    if (!sources) {
      sources = normalize(cell.textContent).split(",").map(function (item) { return normalize(item); }).filter(Boolean).map(function (item) {
        return permissionDisplay[item] || item;
      });
      permissionSources.set(cell, sources);
    }
    cell.textContent = "";
    var wrap = document.createElement("div");
    wrap.className = "hs-users-permission-chips";
    if (!sources.length) {
      var empty = document.createElement("span");
      empty.className = "hs-users-permission-chip";
      empty.textContent = tr("Bez oprávnění");
      wrap.appendChild(empty);
    } else {
      sources.forEach(function (source) {
        var chip = document.createElement("span");
        chip.className = "hs-users-permission-chip";
        chip.textContent = tr(source);
        wrap.appendChild(chip);
      });
    }
    cell.appendChild(wrap);
  }

  function actionClass(link) {
    var href = link.getAttribute("href") || "";
    if (href.indexOf("SmazaniObsluhyZeSystemu=1") !== -1) return "delete";
    if (href.indexOf("PresunObsluhyDoArchivu=1") !== -1) return "archive";
    if (href.indexOf("ObnoveniObsluhyZarchivu=1") !== -1) return "restore";
    return "edit";
  }

  function enhanceActionLink(link) {
    if (!link || link.dataset.hsUsersAction === "1") return;
    var kind = actionClass(link);
    link.dataset.hsUsersAction = "1";
    link.classList.add("hs-users-row-action", "hs-users-row-action--" + kind);
    if (kind === "delete") {
      link.innerHTML = icons.trash;
      link.setAttribute("aria-label", tr("Smazat obsluhu"));
      link.setAttribute("title", tr("Smazat obsluhu"));
      link.setAttribute("data-hs-confirm-title", "Potvrdit smazání");
      link.setAttribute("data-hs-confirm-action", "Smazat");
      link.setAttribute("data-hs-confirm-tone", "delete");
    } else if (kind === "archive") {
      link.innerHTML = icons.archive;
      link.setAttribute("aria-label", tr("Přesunout obsluhu do archivu"));
      link.setAttribute("title", tr("Přesunout obsluhu do archivu"));
      link.setAttribute("data-hs-confirm-title", "Přesunout do archivu");
      link.setAttribute("data-hs-confirm-action", "Archivovat");
      link.setAttribute("data-hs-confirm-tone", "primary");
    } else if (kind === "restore") {
      link.innerHTML = icons.restore;
      link.setAttribute("aria-label", tr("Obnovit obsluhu z archivu"));
      link.setAttribute("title", tr("Obnovit obsluhu z archivu"));
      link.setAttribute("data-hs-confirm-title", "Obnovit z archivu");
      link.setAttribute("data-hs-confirm-action", "Obnovit");
      link.setAttribute("data-hs-confirm-tone", "primary");
    }
  }

  function staffDetailLink(row) {
    if (!row || !row.cells || !row.cells.length) return null;
    return row.cells[0].querySelector('a[href*="Obsluha_GUID="][href*="Obsluha_ID_GET="]');
  }

  function markClickableRows(table) {
    if (!table || !table.tBodies.length) return;
    Array.prototype.forEach.call(table.tBodies[0].rows, function (row) {
      var detail = staffDetailLink(row);
      if (!detail) {
        row.classList.remove("hs-users-row-clickable");
        row.removeAttribute("tabindex");
        row.removeAttribute("role");
        row.removeAttribute("aria-label");
        return;
      }
      row.classList.add("hs-users-row-clickable");
      row.setAttribute("tabindex", "0");
      row.setAttribute("role", "link");
      var name = row.cells.length > 1 ? normalize(row.cells[1].textContent) : "";
      row.setAttribute("aria-label", tr("Detail obsluhy") + (name ? ": " + name : ""));
    });
  }

  function openStaffRow(row) {
    var detail = staffDetailLink(row);
    if (detail && detail.href) window.location.assign(detail.href);
  }

  function bindRowNavigation(table) {
    if (!table || table.dataset.hsUsersRowNavigation === "1") return;
    table.dataset.hsUsersRowNavigation = "1";

    table.addEventListener("click", function (event) {
      var row = event.target.closest("tbody tr");
      if (!row || !table.contains(row)) return;
      if (event.target.closest("a, button, input, select, textarea, label")) return;
      if (!staffDetailLink(row)) return;
      openStaffRow(row);
    });

    table.addEventListener("keydown", function (event) {
      var row = event.target.closest("tbody tr.hs-users-row-clickable");
      if (!row || event.target !== row) return;
      if (event.key !== "Enter" && event.key !== " ") return;
      event.preventDefault();
      openStaffRow(row);
    });

    if (window.jQuery) {
      window.jQuery(table).on("draw.dt.hsUsersRows", function () {
        markClickableRows(table);
      });
    }
    markClickableRows(table);
  }

  function adjustArchiveTable(panel) {
    var table = panel && panel.querySelector("#table_prehledUzivateluObsluhaArchiv");
    if (!table || !window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) return;
    window.setTimeout(function () {
      try {
        if (window.jQuery.fn.dataTable.isDataTable && window.jQuery.fn.dataTable.isDataTable(table)) {
          var api = window.jQuery(table).DataTable();
          api.columns.adjust();
          if (api.responsive && typeof api.responsive.recalc === "function") api.responsive.recalc();
        } else if (window.jQuery(table).dataTable) {
          var legacy = window.jQuery(table).dataTable();
          if (legacy && typeof legacy.fnAdjustColumnSizing === "function") legacy.fnAdjustColumnSizing();
        }
      } catch (error) {}
    }, 0);
  }

  function setupArchiveToggle() {
    var panel = main.querySelector(".hs-users-list--archive");
    if (!panel || panel.dataset.hsUsersArchiveToggle === "1") return;
    var heading = panel.querySelector(":scope > .panel-heading");
    var title = heading && heading.querySelector(".panel-title");
    if (!heading || !title) return;

    panel.dataset.hsUsersArchiveToggle = "1";
    var button = document.createElement("button");
    button.type = "button";
    button.className = "hs-users-archive-toggle";
    button.innerHTML = '<span class="hs-users-archive-toggle-label"></span><span class="hs-users-archive-toggle-icon" aria-hidden="true">' + icons.chevron + "</span>";
    heading.appendChild(button);

    function setExpanded(expanded) {
      panel.classList.toggle("hs-users-list--collapsed", !expanded);
      button.setAttribute("aria-expanded", expanded ? "true" : "false");
      button.setAttribute("aria-label", tr(expanded ? "Skrýt archivované" : "Zobrazit archivované"));
      button.setAttribute("title", tr(expanded ? "Skrýt archivované" : "Zobrazit archivované"));
      var label = button.querySelector(".hs-users-archive-toggle-label");
      if (label) label.textContent = tr(expanded ? "Skrýt archivované" : "Zobrazit archivované");
      if (expanded) adjustArchiveTable(panel);
    }

    function toggle() {
      setExpanded(panel.classList.contains("hs-users-list--collapsed"));
    }

    button.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      toggle();
    });

    heading.addEventListener("click", function (event) {
      if (event.target.closest("button, a, input, select, textarea, label")) return;
      toggle();
    });

    setExpanded(false);
  }

  function refreshArchiveToggleTranslation() {
    var panel = main.querySelector(".hs-users-list--archive");
    var button = panel && panel.querySelector(".hs-users-archive-toggle");
    if (!panel || !button) return;
    var expanded = !panel.classList.contains("hs-users-list--collapsed");
    var source = expanded ? "Skrýt archivované" : "Zobrazit archivované";
    var label = button.querySelector(".hs-users-archive-toggle-label");
    if (label) label.textContent = tr(source);
    button.setAttribute("aria-label", tr(source));
    button.setAttribute("title", tr(source));
  }

  function enhanceTable(table, placeholder) {
    if (!table) return;
    bindRowNavigation(table);
    markClickableRows(table);
    var rows = table.tBodies.length ? table.tBodies[0].rows : [];
    Array.prototype.forEach.call(rows, function (row) {
      if (row.cells.length < 4) return;
      enhancePermissionCell(row.cells[2]);
      row.cells[3].classList.add("hs-users-actions-cell");
      Array.prototype.forEach.call(row.cells[3].querySelectorAll("a"), enhanceActionLink);
    });

    var wrapper = document.getElementById(table.id + "_wrapper");
    moveTableFilter(table, wrapper);
    var filter = wrapper && wrapper.querySelector(".dataTables_filter input");
    if (!filter) {
      var panel = table.closest(".hs-users-list");
      filter = panel && panel.querySelector(".hs-users-table-toolbar .dataTables_filter input");
    }
    if (filter) {
      filter.setAttribute("placeholder", tr(placeholder));
      filter.setAttribute("aria-label", tr(placeholder));
    }
  }

  function enhanceTables() {
    enhanceTable(document.getElementById("table_prehledUzivateluObsluha"), "Hledat v obsluhách…");
    enhanceTable(document.getElementById("table_prehledUzivateluObsluhaArchiv"), "Hledat v archivovaných obsluhách…");
  }

  function refreshTranslations() {
    var branchTitle = main.querySelector(".hs-users-branch > .panel-heading .panel-title");
    if (branchTitle && window.hsSetTranslatedText) window.hsSetTranslatedText(branchTitle, "Výběr pobočky");

    var activeTitle = main.querySelector(".hs-users-list--active > .panel-heading .panel-title");
    if (activeTitle && window.hsSetTranslatedText) window.hsSetTranslatedText(activeTitle, "Seznam obsluh");
    var archiveTitle = main.querySelector(".hs-users-list--archive > .panel-heading .panel-title");
    if (archiveTitle && window.hsSetTranslatedText) window.hsSetTranslatedText(archiveTitle, "Seznam archivovaných obsluh");

    var profile = main.querySelector(".hs-users-profile");
    var account = main.querySelector(".hs-users-account");
    var permissions = main.querySelector(".hs-users-permissions");
    if (profile) setDynamicTitle(profile, "Profilovka");
    if (account) setDynamicTitle(account, "Detail obsluhy");
    if (permissions) setDynamicTitle(permissions, "Oprávnění obsluhy");
    var profileRole = main.querySelector(".hs-users-account-profile-role");
    if (profileRole) profileRole.textContent = tr("Obsluha");
    updateImportButtonLabel();
    refreshArchiveToggleTranslation();

    Array.prototype.forEach.call(main.querySelectorAll(".hs-users-list tbody tr"), function (row) {
      if (row.cells.length >= 3) enhancePermissionCell(row.cells[2]);
      Array.prototype.forEach.call(row.querySelectorAll(".hs-users-row-action"), function (link) {
        link.dataset.hsUsersAction = "";
        enhanceActionLink(link);
      });
    });
    enhanceTables();
    Array.prototype.forEach.call(main.querySelectorAll(".hs-users-list table"), markClickableRows);
  }

  function init() {
    main = document.getElementById("main-content");
    if (!main || !main.classList.contains("hs-users-page")) return;
    classifyPanels();
    setupArchiveToggle();
    mergeProfileIntoAccount();
    relocateImportToBranch();
    enhanceTables();
    document.addEventListener("hs:languagechange", refreshTranslations);
    window.setTimeout(function () {
      setupArchiveToggle();
      mergeProfileIntoAccount();
      relocateImportToBranch();
      enhanceTables();
    }, 80);
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init, { once: true });
  else init();
}());
