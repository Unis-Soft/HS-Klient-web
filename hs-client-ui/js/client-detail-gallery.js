/* HairSoft Klient V80 – moderní galerie s procházením zvětšených fotografií. */
(function () {
  "use strict";

  var PANE_SELECTOR = "#Galerie";
  var menu;
  var activeCard;
  var photoUrls = [];
  var activePhotoIndex = -1;

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function svgIcon(name) {
    if (name === "gallery") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-5-5L5 20"></path></svg>';
    }
    if (name === "more") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle></svg>';
    }
    if (name === "profile") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="8" r="4"></circle><path d="M3 21a7 7 0 0 1 12.5-4.3M19 14v6M16 17h6"></path></svg>';
    }
    if (name === "right") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11a8 8 0 1 0-2.3 5.7"></path><path d="M20 4v7h-7"></path></svg>';
    }
    if (name === "left") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11a8 8 0 1 1 2.3 5.7"></path><path d="M4 4v7h7"></path></svg>';
    }
    if (name === "previous") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>';
    }
    if (name === "next") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>';
    }
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg>';
  }

  function filenameFromImage(image) {
    var handler = String(image.getAttribute("oncontextmenu") || "");
    var match = handler.match(/showContextMenu\s*\(\s*event\s*,\s*['\"]([^'\"]+)['\"]/i);
    return match ? match[1] : "";
  }

  function photoUrlFromImage(image) {
    var handler = String(image.getAttribute("onclick") || "");
    var match = handler.match(/openModalGalerie\s*\(\s*(['"])(.*?)\1\s*\)/i);
    return match ? match[2] : (image.currentSrc || image.src || "");
  }

  function modalIsOpen() {
    var modal = document.getElementById("myModal");
    return Boolean(modal && modal.style.display === "block");
  }

  function openPhoto(photoIndex) {
    var modal = document.getElementById("myModal");
    var modalImage = document.getElementById("modalImg");
    var normalizedIndex;
    if (!modal || !modalImage || !photoUrls.length) return;

    normalizedIndex = (photoIndex + photoUrls.length) % photoUrls.length;
    if (!photoUrls[normalizedIndex]) return;

    closeMenu();
    activePhotoIndex = normalizedIndex;
    modalImage.src = photoUrls[activePhotoIndex];
    modalImage.alt = translate("Fotografie zákazníka") + " " + (activePhotoIndex + 1) + " / " + photoUrls.length;
    modal.style.display = "block";
    modal.scrollTop = 0;
  }

  function changePhoto(direction) {
    if (photoUrls.length < 2 || activePhotoIndex < 0) return;
    openPhoto(activePhotoIndex + direction);
  }

  function buildModalNavigation(pane) {
    var modal = pane.querySelector("#myModal");
    var previous;
    var next;
    if (!modal || modal.getAttribute("data-hs-gallery-navigation") === "true") return;

    previous = document.createElement("button");
    previous.type = "button";
    previous.className = "hs-gallery-modal-nav hs-gallery-modal-nav--previous";
    previous.innerHTML = svgIcon("previous");
    previous.setAttribute("aria-label", translate("Předchozí fotografie"));
    previous.title = translate("Předchozí fotografie");
    previous.hidden = photoUrls.length < 2;
    previous.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      changePhoto(-1);
    });

    next = document.createElement("button");
    next.type = "button";
    next.className = "hs-gallery-modal-nav hs-gallery-modal-nav--next";
    next.innerHTML = svgIcon("next");
    next.setAttribute("aria-label", translate("Další fotografie"));
    next.title = translate("Další fotografie");
    next.hidden = photoUrls.length < 2;
    next.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      changePhoto(1);
    });

    modal.appendChild(previous);
    modal.appendChild(next);
    modal.setAttribute("data-hs-gallery-navigation", "true");
  }

  function closeMenu() {
    if (!menu) return;
    menu.hidden = true;
    menu.style.display = "none";
    if (activeCard) activeCard.classList.remove("hs-gallery-card--menu-open");
    activeCard = null;
  }

  function runAction(action) {
    var handler = {
      profile: "NastavitJakoProfilovku",
      right: "OtocitDoprava",
      left: "OtocitDoleva",
      delete: "SmazatSoubor"
    }[action];

    closeMenu();
    if (handler && typeof window[handler] === "function") window[handler]();
  }

  function addMenuButton(container, action, label, iconName) {
    var button = document.createElement("button");
    button.type = "button";
    button.className = "hs-gallery-menu-item hs-gallery-menu-item--" + action;
    button.setAttribute("data-gallery-action", action);
    button.innerHTML = '<span class="hs-gallery-menu-item__icon">' + svgIcon(iconName) + '</span><span>' + translate(label) + '</span>';
    container.appendChild(button);
  }

  function buildMenu() {
    var heading;
    var list;

    menu = document.createElement("div");
    menu.className = "hs-gallery-context-menu";
    menu.hidden = true;
    menu.style.display = "none";
    menu.setAttribute("role", "menu");

    heading = document.createElement("div");
    heading.className = "hs-gallery-context-menu__heading";
    heading.textContent = translate("Možnosti fotografie");
    menu.appendChild(heading);

    list = document.createElement("div");
    list.className = "hs-gallery-context-menu__list";
    addMenuButton(list, "profile", "Nastavit jako profilovku", "profile");
    addMenuButton(list, "right", "Otočit doprava", "right");
    addMenuButton(list, "left", "Otočit doleva", "left");
    addMenuButton(list, "delete", "Smazat obrázek", "delete");
    menu.appendChild(list);

    menu.addEventListener("click", function (event) {
      var button = event.target.closest("[data-gallery-action]");
      if (!button) return;
      event.preventDefault();
      event.stopPropagation();
      runAction(button.getAttribute("data-gallery-action"));
    });
    document.body.appendChild(menu);
  }

  function openMenu(card, filename, x, y) {
    var width;
    var height;
    var left;
    var top;

    if (!menu) buildMenu();
    closeMenu();
    window.JmenoObrazkuProPraci = filename;
    activeCard = card;
    activeCard.classList.add("hs-gallery-card--menu-open");
    menu.hidden = false;
    menu.style.display = "block";
    menu.style.left = "0px";
    menu.style.top = "0px";

    width = menu.offsetWidth;
    height = menu.offsetHeight;
    left = Math.max(12, Math.min(x, window.innerWidth - width - 12));
    top = Math.max(12, Math.min(y, window.innerHeight - height - 12));
    menu.style.left = left + "px";
    menu.style.top = top + "px";
    window.setTimeout(function () {
      var first = menu.querySelector("button");
      if (first) first.focus();
    }, 0);
  }

  function buildToolbar(pane, photoCount) {
    var toolbar = document.createElement("header");
    var icon = document.createElement("span");
    var copy = document.createElement("span");
    var title = document.createElement("strong");
    var subtitle = document.createElement("small");
    var count = document.createElement("span");

    toolbar.className = "hs-gallery-toolbar";
    icon.className = "hs-gallery-toolbar__icon";
    icon.innerHTML = svgIcon("gallery");
    copy.className = "hs-gallery-toolbar__copy";
    title.textContent = translate("Fotografie zákazníka");
    subtitle.textContent = translate("Kliknutím fotografii otevřete. Další možnosti najdete v nabídce u snímku.");
    count.className = "hs-gallery-toolbar__count";
    count.textContent = String(photoCount);
    count.setAttribute("aria-label", translate("Počet fotografií") + ": " + photoCount);
    copy.appendChild(title);
    copy.appendChild(subtitle);
    toolbar.appendChild(icon);
    toolbar.appendChild(copy);
    toolbar.appendChild(count);
    pane.insertBefore(toolbar, pane.firstChild);
  }

  function createEmptyState(pane) {
    var empty = document.createElement("div");
    empty.className = "hs-gallery-empty";
    empty.innerHTML = '<span class="hs-gallery-empty__icon">' + svgIcon("gallery") + '</span><strong>' + translate("Zatím zde nejsou žádné fotografie.") + '</strong>';
    pane.appendChild(empty);
  }

  function decorateCard(card, image, index) {
    var filename = filenameFromImage(image);
    var button = document.createElement("button");

    card.classList.add("hs-gallery-card");
    if (image.classList.contains("thumbnail-nestazen")) card.classList.add("hs-gallery-card--pending");
    image.classList.add("hs-gallery-photo");
    image.removeAttribute("id");
    image.removeAttribute("oncontextmenu");
    image.removeAttribute("onclick");
    image.setAttribute("tabindex", "0");
    image.setAttribute("role", "button");
    image.setAttribute("loading", "lazy");
    image.setAttribute("decoding", "async");
    image.setAttribute("alt", translate("Fotografie zákazníka") + " " + (index + 1));

    button.type = "button";
    button.className = "hs-gallery-card__menu-button";
    button.innerHTML = svgIcon("more");
    button.title = translate("Další možnosti fotografie");
    button.setAttribute("aria-label", translate("Další možnosti fotografie"));
    button.addEventListener("click", function (event) {
      var rect = button.getBoundingClientRect();
      event.preventDefault();
      event.stopPropagation();
      openMenu(card, filename, rect.right - 8, rect.bottom + 7);
    });
    image.addEventListener("contextmenu", function (event) {
      event.preventDefault();
      openMenu(card, filename, event.clientX, event.clientY);
    });
    image.addEventListener("click", function () {
      openPhoto(index);
    });
    image.addEventListener("keydown", function (event) {
      if (event.key !== "Enter" && event.key !== " ") return;
      event.preventDefault();
      image.click();
    });
    card.appendChild(button);
  }

  function initialize() {
    var pane = document.querySelector(PANE_SELECTOR);
    var grid;
    var cards;
    var images;
    var legacyMenus;
    var tabs;
    var index;

    if (!pane || pane.getAttribute("data-hs-gallery-enhanced") === "true") return;
    grid = pane.querySelector(":scope > .row");
    if (!grid) return;
    cards = grid.querySelectorAll(":scope > .col-sm-3");
    images = grid.querySelectorAll(".gallery > img");
    photoUrls = Array.prototype.map.call(images, photoUrlFromImage);
    legacyMenus = grid.querySelectorAll(".context_menu_galerie_styl");
    for (index = 0; index < legacyMenus.length; index += 1) legacyMenus[index].remove();

    pane.classList.add("hs-gallery-pane");
    grid.classList.add("hs-gallery-grid");
    buildToolbar(pane, images.length);
    for (index = 0; index < cards.length; index += 1) {
      if (images[index]) decorateCard(cards[index].querySelector(".gallery"), images[index], index);
    }
    buildModalNavigation(pane);
    if (!images.length) createEmptyState(pane);

    tabs = pane.closest(".hs-client-detail-tabs");
    function updateTabState() {
      if (tabs) tabs.classList.toggle("hs-client-detail-tabs--gallery-active", pane.classList.contains("active"));
    }
    if (window.jQuery) {
      window.jQuery('a[data-toggle="tab"]')
        .off("shown.bs.tab.hsGallery")
        .on("shown.bs.tab.hsGallery", updateTabState);
    }
    updateTabState();

    document.addEventListener("pointerdown", function (event) {
      if (menu && !menu.hidden && !menu.contains(event.target) && !event.target.closest(".hs-gallery-card__menu-button")) closeMenu();
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "ArrowLeft" && modalIsOpen()) {
        event.preventDefault();
        changePhoto(-1);
      } else if (event.key === "ArrowRight" && modalIsOpen()) {
        event.preventDefault();
        changePhoto(1);
      } else if (event.key === "Escape") {
        closeMenu();
        if (modalIsOpen() && typeof window.closeModalGalerie === "function") {
          window.closeModalGalerie();
          activePhotoIndex = -1;
        }
      }
    });
    window.addEventListener("resize", closeMenu);
    window.addEventListener("scroll", closeMenu, true);
    pane.setAttribute("data-hs-gallery-enhanced", "true");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
}());
