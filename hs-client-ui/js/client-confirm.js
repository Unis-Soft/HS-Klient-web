(function () {
  "use strict";

  var root;
  var dialog;
  var title;
  var message;
  var iconNode;
  var cancelButton;
  var deleteButton;
  var activeCallback = null;
  var lastFocus = null;
  var closeTimer = null;
  var bypassTrigger = null;
  var bypassForm = null;

  function translate(value) {
    return window.hsTranslate ? window.hsTranslate(value) : value;
  }

  function createElement(tagName, className) {
    var element = document.createElement(tagName);
    if (className) element.className = className;
    return element;
  }

  function buildDialog() {
    var backdrop;
    var content;
    var icon;
    var actions;

    if (root) return;

    root = createElement("div", "hs-confirm-root");
    root.hidden = true;
    root.setAttribute("data-hs-i18n-ignore", "true");

    backdrop = createElement("div", "hs-confirm-backdrop");
    backdrop.setAttribute("aria-hidden", "true");
    root.appendChild(backdrop);

    dialog = createElement("section", "hs-confirm-dialog");
    dialog.setAttribute("role", "alertdialog");
    dialog.setAttribute("aria-modal", "true");
    dialog.setAttribute("aria-labelledby", "hs-confirm-title");
    dialog.setAttribute("aria-describedby", "hs-confirm-message");
    dialog.setAttribute("tabindex", "-1");

    content = createElement("div", "hs-confirm-content");
    icon = createElement("span", "hs-confirm-icon");
    icon.setAttribute("aria-hidden", "true");
    iconNode = icon;
    icon.innerHTML = '<svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>';

    title = createElement("h2", "hs-confirm-title");
    title.id = "hs-confirm-title";
    message = createElement("p", "hs-confirm-message");
    message.id = "hs-confirm-message";
    content.appendChild(icon);
    content.appendChild(title);
    content.appendChild(message);
    dialog.appendChild(content);

    actions = createElement("div", "hs-confirm-actions");
    cancelButton = createElement("button", "hs-confirm-button hs-confirm-button--cancel");
    cancelButton.type = "button";
    deleteButton = createElement("button", "hs-confirm-button hs-confirm-button--delete");
    deleteButton.type = "button";
    actions.appendChild(cancelButton);
    actions.appendChild(deleteButton);
    dialog.appendChild(actions);
    root.appendChild(dialog);
    document.body.appendChild(root);

    backdrop.addEventListener("click", function () { finish(false); });
    cancelButton.addEventListener("click", function () { finish(false); });
    deleteButton.addEventListener("click", function () { finish(true); });
    root.addEventListener("keydown", handleKeydown);
  }

  function handleKeydown(event) {
    if (event.key === "Escape") {
      event.preventDefault();
      finish(false);
      return;
    }

    if (event.key !== "Tab") return;
    if (event.shiftKey && document.activeElement === cancelButton) {
      event.preventDefault();
      deleteButton.focus();
    } else if (!event.shiftKey && document.activeElement === deleteButton) {
      event.preventDefault();
      cancelButton.focus();
    }
  }

  function finish(confirmed) {
    var callback = activeCallback;
    var focusTarget = lastFocus;

    if (!root || root.hidden) return;
    activeCallback = null;
    lastFocus = null;
    root.classList.remove("hs-confirm-root--open");
    document.body.classList.remove("hs-confirm-open");
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(function () { root.hidden = true; }, 180);

    if (!confirmed && focusTarget && typeof focusTarget.focus === "function") focusTarget.focus();
    if (confirmed && typeof callback === "function") callback();
  }

  window.hsConfirm = function (prompt, onConfirm, options) {
    var settings = options || {};
    var tone = settings.tone === "primary" ? "primary" : "delete";
    buildDialog();
    window.clearTimeout(closeTimer);
    root.hidden = false;
    title.textContent = translate(settings.title || "Potvrdit smazání");
    message.textContent = translate(String(prompt || ""));
    cancelButton.textContent = translate(settings.cancel || "Zrušit");
    deleteButton.textContent = translate(settings.action || "Smazat");
    deleteButton.className = "hs-confirm-button hs-confirm-button--" + tone;
    iconNode.className = "hs-confirm-icon" + (tone === "primary" ? " hs-confirm-icon--primary" : "");
    iconNode.innerHTML = tone === "primary"
      ? '<svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 4v6h6"></path><path d="M12 7v5l3 2"></path></svg>'
      : '<svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>';
    activeCallback = typeof onConfirm === "function" ? onConfirm : null;
    lastFocus = document.activeElement;
    document.body.classList.add("hs-confirm-open");

    window.requestAnimationFrame(function () {
      root.classList.add("hs-confirm-root--open");
      cancelButton.focus();
    });
  };

  function inlineConfirmMessage(element, attributeName) {
    var source = element && element.getAttribute ? element.getAttribute(attributeName) : "";
    var match;

    if (!source || source.indexOf("confirm") === -1) return "";
    match = source.match(/\bconfirm\s*\(\s*(['"])([\s\S]*?)\1\s*\)/i);
    if (!match) return "";
    return match[2].replace(/\\(['"\\])/g, "$1");
  }

  function confirmTrigger(target) {
    var node = target;
    while (node && node !== document) {
      if (inlineConfirmMessage(node, "onclick")) return node;
      node = node.parentNode;
    }
    return null;
  }

  function replayClick(trigger) {
    var currentConfirm = window.confirm;
    bypassTrigger = trigger;
    window.confirm = function () { return true; };
    try {
      trigger.click();
    } finally {
      window.confirm = currentConfirm;
      bypassTrigger = null;
    }
  }

  function replaySubmit(form, submitter) {
    var currentConfirm = window.confirm;
    bypassForm = form;
    window.confirm = function () { return true; };
    try {
      if (typeof form.requestSubmit === "function") form.requestSubmit(submitter || undefined);
      else form.submit();
    } finally {
      window.confirm = currentConfirm;
      bypassForm = null;
    }
  }

  document.addEventListener("click", function (event) {
    var trigger = confirmTrigger(event.target);
    var prompt;

    if (!trigger || trigger === bypassTrigger) return;
    prompt = inlineConfirmMessage(trigger, "onclick");
    event.preventDefault();
    event.stopImmediatePropagation();
    window.hsConfirm(prompt, function () { replayClick(trigger); }, {
      title: trigger.getAttribute("data-hs-confirm-title") || "",
      action: trigger.getAttribute("data-hs-confirm-action") || "",
      tone: trigger.getAttribute("data-hs-confirm-tone") || ""
    });
  }, true);

  document.addEventListener("submit", function (event) {
    var form = event.target;
    var prompt;

    if (!form || form === bypassForm) return;
    prompt = inlineConfirmMessage(form, "onsubmit");
    if (!prompt) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    window.hsConfirm(prompt, function () { replaySubmit(form, event.submitter); });
  }, true);
}());
