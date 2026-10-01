/* HairSoft Klient – společné rozhraní aplikace, verze 25 */
(function () {
  "use strict";

  var STORAGE_KEY = "hairsoft.sidebar.collapsed.v109";

  function getWrapper() {
    return document.getElementById("main-wrapper");
  }

  function isDesktop() {
    return window.matchMedia ? window.matchMedia("(min-width: 768px)").matches : window.innerWidth >= 768;
  }

  function isCollapsed() {
    var wrapper = getWrapper();
    return !!(wrapper && wrapper.classList.contains("sidebar-mini"));
  }

  function saveState() {
    if (!isDesktop()) {
      return;
    }

    try {
      window.localStorage.setItem(STORAGE_KEY, isCollapsed() ? "1" : "0");
    } catch (error) {
      /* Soukromý režim může localStorage zablokovat. */
    }
  }

  function directSubmenu(item) {
    var children = item ? item.children : [];
    var index;

    for (index = 0; index < children.length; index += 1) {
      if (children[index].classList && children[index].classList.contains("nav-sub")) {
        return children[index];
      }
    }

    return null;
  }

  function closestAnchor(target, boundary) {
    var current = target;

    while (current && current !== boundary) {
      if (current.tagName && current.tagName.toLowerCase() === "a") {
        return current;
      }
      current = current.parentNode;
    }

    return null;
  }

  function closeSubmenus(except) {
    var openItems = document.querySelectorAll("aside.sidebar-left .hs-submenu-open");
    var index;

    for (index = 0; index < openItems.length; index += 1) {
      if (openItems[index] !== except) {
        openItems[index].classList.remove("hs-submenu-open");
      }
    }
  }

  function directChildByClass(element, className) {
    var children = element ? element.children : [];
    var index;

    for (index = 0; index < children.length; index += 1) {
      if (children[index].classList && children[index].classList.contains(className)) {
        return children[index];
      }
    }

    return null;
  }

  function directChildByTag(element, tagName) {
    var children = element ? element.children : [];
    var expectedTag = tagName.toLowerCase();
    var index;

    for (index = 0; index < children.length; index += 1) {
      if (children[index].tagName && children[index].tagName.toLowerCase() === expectedTag) {
        return children[index];
      }
    }

    return null;
  }

  function createMetaIcon() {
    var icon = document.createElement("span");

    icon.className = "hs-dashboard-meta-icon";
    icon.setAttribute("aria-hidden", "true");
    icon.innerHTML = '<svg><use href="#hs-icon-building" xlink:href="#hs-icon-building"></use></svg>';
    return icon;
  }

  function createMetaGroup(labelText, valueClass) {
    var group = document.createElement("span");
    var label = document.createElement("span");
    var value = document.createElement("span");

    group.className = "hs-dashboard-meta-group";
    label.className = "label";
    label.textContent = labelText;
    value.className = valueClass;
    group.appendChild(label);
    group.appendChild(value);

    return {
      group: group,
      value: value
    };
  }

  function getPageHeaderData() {
    var source = window.hsPageHeaderData || {};

    return {
      branchName: source.branchName === undefined || source.branchName === null ? "" : String(source.branchName),
      swId: source.swId === undefined || source.swId === null ? "" : String(source.swId)
    };
  }

  function moveBranchContent(source, target, preferredBranchName) {
    var branchElement = source.querySelector("b");
    var nodes = [];
    var node;
    var index;

    while (source.firstChild) {
      nodes.push(source.removeChild(source.firstChild));
    }

    for (index = 0; index < nodes.length; index += 1) {
      node = nodes[index];
      if (node !== branchElement && (node.nodeType !== 3 || node.nodeValue.replace(/\s/g, "") !== "")) {
        target.appendChild(node);
      }
    }

    if (!branchElement && preferredBranchName) {
      branchElement = document.createElement("b");
    }

    if (branchElement) {
      if (preferredBranchName) {
        branchElement.textContent = preferredBranchName;
      }
      branchElement.setAttribute("data-hs-i18n-ignore", "");
      target.appendChild(branchElement);
    }
  }

  function ensureMetaData(meta, data) {
    var branchValue = meta.querySelector(".hs-dashboard-branch-value");
    var branchName;
    var idValue = meta.querySelector(".hs-dashboard-id-value");
    var idGroup;
    var divider;

    if (branchValue && data.branchName) {
      branchName = branchValue.querySelector("b");
      if (!branchName) {
        branchName = document.createElement("b");
        branchValue.appendChild(branchName);
      }
      branchName.textContent = data.branchName;
      branchName.setAttribute("data-hs-i18n-ignore", "");
    }

    if (!data.swId) {
      return;
    }

    if (idValue) {
      idValue.textContent = data.swId;
      idValue.setAttribute("data-hs-i18n-ignore", "");
      return;
    }

    divider = document.createElement("span");
    divider.className = "hs-dashboard-meta-divider";
    divider.setAttribute("aria-hidden", "true");
    meta.appendChild(divider);

    idGroup = createMetaGroup("SW ID", "hs-dashboard-id-value");
    idGroup.value.setAttribute("data-hs-i18n-ignore", "");
    idGroup.value.textContent = data.swId;
    meta.appendChild(idGroup.group);
  }

  function enhancePageHeader(header) {
    var heading = directChildByClass(header, "hs-dashboard-heading");
    var title;
    var description;
    var meta;
    var oldLabel;
    var activeBranch;
    var oldId;
    var branchGroup;
    var idGroup;
    var divider;
    var labelText;
    var idMatch;
    var idValue = "";
    var pageData = getPageHeaderData();

    header.classList.add("hs-dashboard-header");

    if (!heading) {
      title = directChildByTag(header, "h1");
      description = directChildByClass(header, "description");

      if (title) {
        heading = document.createElement("div");
        heading.className = "hs-dashboard-heading";
        header.insertBefore(heading, title);
        heading.appendChild(title);
        if (description) {
          heading.appendChild(description);
        }
      }
    }

    meta = directChildByClass(header, "breadcrumb-wrapper");
    if (!meta) {
      return;
    }

    if (meta.classList.contains("hs-dashboard-meta")) {
      ensureMetaData(meta, pageData);
      return;
    }

    oldLabel = meta.querySelector(".label");
    activeBranch = meta.querySelector(".breadcrumb li.active");
    oldId = meta.querySelector("font");

    if (!activeBranch) {
      return;
    }

    labelText = oldLabel ? oldLabel.textContent.replace(/\s*:\s*$/, "").replace(/^\s+|\s+$/g, "") : "Pobočka";
    labelText = labelText || "Pobočka";

    if (oldId) {
      idMatch = oldId.textContent.replace(/\u00a0/g, " ").match(/SW\s*ID\s*:\s*(.*)/i);
      if (idMatch) {
        idValue = idMatch[1].replace(/^\s+|\s+$/g, "");
      }
    }

    if (pageData.swId) {
      idValue = pageData.swId;
    }

    while (meta.firstChild) {
      meta.removeChild(meta.firstChild);
    }

    meta.classList.add("hs-dashboard-meta");
    meta.appendChild(createMetaIcon());

    branchGroup = createMetaGroup(labelText, "hs-dashboard-branch-value");
    moveBranchContent(activeBranch, branchGroup.value, pageData.branchName);
    meta.appendChild(branchGroup.group);

    if (idValue) {
      divider = document.createElement("span");
      divider.className = "hs-dashboard-meta-divider";
      divider.setAttribute("aria-hidden", "true");
      meta.appendChild(divider);

      idGroup = createMetaGroup("SW ID", "hs-dashboard-id-value");
      idGroup.value.setAttribute("data-hs-i18n-ignore", "");
      idGroup.value.textContent = idValue;
      meta.appendChild(idGroup.group);
    }


    ensureMetaData(meta, pageData);
  }

  function preparePageHeaders() {
    var headers = document.querySelectorAll("#main-wrapper > section.main-content-wrapper > .pageheader");
    var index;

    for (index = 0; index < headers.length; index += 1) {
      enhancePageHeader(headers[index]);
    }
  }

  function hasColumnClass(element) {
    return !!(element && typeof element.className === "string" && /(^|\s)col-(xs|sm|md|lg)-\d+(\s|$)/.test(element.className));
  }

  function closestRow(element) {
    var current = element ? element.parentNode : null;

    while (current && current.nodeType === 1) {
      if (current.classList.contains("row")) {
        return current;
      }
      current = current.parentNode;
    }

    return null;
  }

  function systemKpiIconMarkup(labelText) {
    var label = String(labelText || "").toLowerCase();

    if (/blacklist|odhl[aá]šen|neodeslan|chyb/.test(label)) {
      return '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/></svg>';
    }
    if (/z[aá]kaz|obsluh|u[zž]iv/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
    }
    if (/voucher|kredit/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 0 2-2h14v4a2 2 0 0 0 0 4v4H5a2 2 0 0 0-2-2V7Z"/><path d="M13 5v12"/></svg>';
    }
    if (/sms|zpr[aá]v/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/><path d="M8 9h8M8 13h5"/></svg>';
    }
    if (/hovor|vol[aá]n/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.2 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z"/></svg>';
    }
    if (/[uú]čten|ucten/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>';
    }
    if (/sklad|z[aá]sob|zbo[zž]/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="m3 8 9 5 9-5M3 12l9 5 9-5M3 16l9 5 9-5"/></svg>';
    }
    if (/hodnoc|odpov[eě]d|procent/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2l-5-4.9 6.9-1L12 2Z"/></svg>';
    }
    if (/prodej/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>';
    }
    if (/slu[zž]/.test(label)) {
      return '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.6 2.6L16.5 9"/></svg>';
    }
    if (/tr[zž]|hodnota|p[rř][ií]jem|z[uů]statek|čerp|cerp|cena/.test(label)) {
      return '<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 16 4-5 4 3 5-7"/><path d="M16 7h4v4"/></svg>';
    }

    return '<svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/></svg>';
  }

  function unwrapLegacyFonts(card) {
    var fonts = card.querySelectorAll("font");
    var font;
    var index;

    for (index = 0; index < fonts.length; index += 1) {
      font = fonts[index];
      while (font.firstChild) {
        font.parentNode.insertBefore(font.firstChild, font);
      }
      font.parentNode.removeChild(font);
    }
  }

  function enhanceSystemKpiCard(card) {
    var body = directChildByClass(card, "panel-body");
    var title = body ? body.querySelector(".title") : null;
    var oldIcon = body ? body.querySelector("i") : null;
    var icon;

    card.classList.add("hs-system-kpi-card");
    unwrapLegacyFonts(card);

    if (!body || body.querySelector(".hs-kpi-icon")) {
      return;
    }

    if (oldIcon) {
      oldIcon.parentNode.removeChild(oldIcon);
    }

    icon = document.createElement("span");
    icon.className = "hs-kpi-icon";
    icon.setAttribute("aria-hidden", "true");
    icon.innerHTML = systemKpiIconMarkup(title ? title.textContent : "");
    body.insertBefore(icon, body.firstChild);
  }

  function isSummaryKpiCard(card) {
    return !!(card && card.querySelector(".total") && card.querySelector(".title"));
  }

  function isSummaryKpiGroup(root) {
    var widgets = root ? root.querySelectorAll(".widget-mini") : [];
    var index;

    if (!widgets.length) {
      return false;
    }

    for (index = 0; index < widgets.length; index += 1) {
      if (!isSummaryKpiCard(widgets[index])) {
        return false;
      }
    }

    return true;
  }

  function systemKpiRoot(card, mainContent) {
    var row = closestRow(card);
    var parentColumn;
    var parentRow;
    var stockRoot = card && card.closest ? card.closest(".hs-stock-kpis") : null;

    /* V199: Sklad ma dve vnorené Bootstrap .row. Vsechny KPI karty musi byt
       spravovany jednim vnejsim gridem, jinak se vnitrni row mohou zalomit
       vedle predchoziho floatu a vykukovat mimo pravy okraj viewportu. */
    if (stockRoot) {
      return stockRoot;
    }

    if (!row) {
      return null;
    }

    parentColumn = row.parentNode;
    parentRow = hasColumnClass(parentColumn) ? closestRow(parentColumn) : null;

    if (parentRow && parentRow.parentNode === mainContent && parentRow.querySelector(".widget-mini")) {
      return parentRow;
    }

    return row;
  }

  function prepareSystemKpis() {
    var mainContent = document.getElementById("main-content");
    var cards = document.querySelectorAll("#main-content .widget-mini");
    var groups = [];
    var card;
    var root;
    var item;
    var current;
    var groupIndex;
    var index;

    if (!mainContent || !cards.length) {
      return;
    }

    for (index = 0; index < cards.length; index += 1) {
      card = cards[index];
      if (card.closest && card.closest(".hs-dashboard-kpis")) {
        continue;
      }
      if (!isSummaryKpiCard(card)) {
        continue;
      }
      root = systemKpiRoot(card, mainContent);
      if (!root || !isSummaryKpiGroup(root)) {
        continue;
      }
      enhanceSystemKpiCard(card);

      groupIndex = groups.indexOf(root);
      if (groupIndex === -1) {
        groups.push(root);
        groupIndex = groups.length - 1;
      }
      item = card.parentNode;
      while (item && item !== root && !hasColumnClass(item)) {
        item = item.parentNode;
      }
      if (!item || item === root) {
        item = card;
      }

      item.classList.add("hs-system-kpi-item");
      current = item.parentNode;
      while (current && current !== root) {
        current.classList.add("hs-system-kpi-flatten");
        current = current.parentNode;
      }
    }

    if (!groups.length) {
      return;
    }

    mainContent.classList.add("hs-system-kpi-content");

    for (index = 0; index < groups.length; index += 1) {
      root = groups[index];
      groupIndex = root.querySelectorAll(".hs-system-kpi-card").length;
      root.classList.add("hs-system-kpi-row");
      root.classList.add("hs-system-kpi-count-" + String(groupIndex));
      root.style.setProperty("--hs-system-kpi-columns", String(Math.min(groupIndex, 4)));
      root.style.setProperty("--hs-system-kpi-tablet-columns", String(Math.min(groupIndex, 2)));
    }
  }

  function prepareMenu() {
    var wrapper = getWrapper();
    var sidebar = document.querySelector("aside.sidebar-left");
    var toggles = document.querySelectorAll("#toggle-left");
    var index;

    preparePageHeaders();
    prepareSystemKpis();
    document.documentElement.classList.remove("hs-ui-preparing");

    if (!wrapper || !sidebar) {
      return;
    }

    for (index = 0; index < toggles.length; index += 1) {
      toggles[index].addEventListener("click", function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        wrapper.classList.toggle(isDesktop() ? "sidebar-mini" : "sidebar-opened");
        var expanded = isDesktop() ? !isCollapsed() : wrapper.classList.contains("sidebar-opened");
        Array.prototype.forEach.call(toggles, function (button) { button.setAttribute("aria-expanded", String(expanded)); });
        saveState();
        if (!isCollapsed()) closeSubmenus();
        window.dispatchEvent(new Event("resize"));
      }, true);
    }

    sidebar.addEventListener("click", function (event) {
      var anchor = closestAnchor(event.target, sidebar);
      var item;
      var submenu;
      var shouldOpen;

      if (!anchor) {
        return;
      }

      item = anchor.parentNode;
      submenu = directSubmenu(item);

      if (!submenu) {
        if (isDesktop() && isCollapsed()) {
          saveState();
        }
        return;
      }

      event.preventDefault();
      event.stopImmediatePropagation();
      shouldOpen = !item.classList.contains("hs-submenu-open");
      closeSubmenus(item);
      item.classList.toggle("hs-submenu-open", shouldOpen);
      anchor.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
    }, true);

    document.addEventListener("click", function (event) {
      if (!event.target.closest || !event.target.closest("aside.sidebar-left")) {
        closeSubmenus();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" || event.keyCode === 27) {
        closeSubmenus();
      }
    });

    window.addEventListener("beforeunload", saveState);
  }

  /* Skript je vložen až za obsahem stránky. Panel proto připravíme ihned,
     bez čekání na DOMContentLoaded a bez krátkého zobrazení původního vzhledu. */
  prepareMenu();
}());
