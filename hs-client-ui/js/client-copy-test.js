(function () {
  "use strict";

  var modal = document.getElementById("hsCustomerCopyTestModal");
  var dataNode = document.getElementById("hsCustomerCopyTestData");
  var companySelect = document.getElementById("hsCustomerCopyCompany");
  var branchSelect = document.getElementById("hsCustomerCopyBranch");
  var form = document.getElementById("hsCustomerCopyTestForm");
  var submitButton = document.getElementById("hsCustomerCopySubmit");
  var accounts = [];
  var lastFocus = null;
  var submitting = false;

  function t(value) {
    return window.hsTranslate ? window.hsTranslate(value) : value;
  }

  function parseAccounts() {
    if (!dataNode) return [];
    try {
      var parsed = JSON.parse(dataNode.textContent || "[]");
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  }

  function findAccount(selector) {
    var i;
    for (i = 0; i < accounts.length; i += 1) {
      if (String(accounts[i].selector || "") === String(selector || "")) return accounts[i];
    }
    return null;
  }

  function clearBranchSelect(message) {
    if (!branchSelect) return;
    while (branchSelect.firstChild) branchSelect.removeChild(branchSelect.firstChild);
    var option = document.createElement("option");
    option.value = "";
    option.textContent = t(message);
    branchSelect.appendChild(option);
    branchSelect.disabled = true;
    updateSubmitState();
  }

  function populateBranches() {
    var account = companySelect ? findAccount(companySelect.value) : null;
    var i;

    if (!account || !Array.isArray(account.branches) || account.branches.length === 0) {
      clearBranchSelect(companySelect && companySelect.value ? "Cílová firma nemá dostupnou pobočku." : "Nejdříve vyberte firmu...");
      return;
    }

    while (branchSelect.firstChild) branchSelect.removeChild(branchSelect.firstChild);

    var placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = t("Vyberte pobočku...");
    branchSelect.appendChild(placeholder);

    for (i = 0; i < account.branches.length; i += 1) {
      var branch = account.branches[i] || {};
      var option = document.createElement("option");
      option.value = String(branch.id || "");
      option.textContent = String(branch.name || ("SW ID " + String(branch.id || "")));
      option.setAttribute("data-hs-i18n-ignore", "true");
      branchSelect.appendChild(option);
    }

    branchSelect.disabled = false;
    if (account.branches.length === 1) branchSelect.value = String(account.branches[0].id || "");
    updateSubmitState();
  }

  function updateSubmitState() {
    if (!submitButton) return;
    var ready = !!(companySelect && companySelect.value && branchSelect && !branchSelect.disabled && branchSelect.value);
    submitButton.disabled = !ready || submitting;
  }

  function openModal(trigger) {
    if (!modal) return;
    lastFocus = trigger || document.activeElement;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("hs-copy-test-lock");
    window.setTimeout(function () {
      if (companySelect) companySelect.focus();
    }, 20);
  }

  function closeModal() {
    if (!modal || submitting) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("hs-copy-test-lock");
    if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
    lastFocus = null;
  }

  function submitCleanup(formNode) {
    var prompt = "Opravdu chcete odstranit kopii? Pokud ji HairSoft ještě nepřevzal, bude odstraněna okamžitě. Pokud už byla synchronizována, označí se ke smazání standardní synchronizací cílové firmy. Zdrojová firma zůstane beze změny.";
    var doSubmit = function () {
      var button = formNode.querySelector("button[type='submit']");
      if (button) button.disabled = true;
      formNode.submit();
    };

    if (typeof window.hsConfirm === "function") {
      window.hsConfirm(prompt, doSubmit, {
        title: "Odstranit kopii",
        action: "Odstranit kopii",
        cancel: "Zrušit",
        tone: "delete"
      });
    } else if (window.confirm(t(prompt))) {
      doSubmit();
    }
  }

  accounts = parseAccounts();

  document.addEventListener("click", function (event) {
    var openTrigger = event.target.closest ? event.target.closest("[data-hs-copy-test-open]") : null;
    var closeTrigger = event.target.closest ? event.target.closest("[data-hs-copy-test-close]") : null;

    if (openTrigger) {
      event.preventDefault();
      if (!openTrigger.disabled && !openTrigger.classList.contains("is-disabled")) openModal(openTrigger);
      return;
    }
    if (closeTrigger) {
      event.preventDefault();
      closeModal();
    }
  });

  if (companySelect) companySelect.addEventListener("change", populateBranches);
  if (branchSelect) branchSelect.addEventListener("change", updateSubmitState);

  if (form) {
    form.addEventListener("submit", function (event) {
      if (submitting) {
        event.preventDefault();
        return;
      }
      if (!companySelect.value || branchSelect.disabled || !branchSelect.value) {
        event.preventDefault();
        updateSubmitState();
        return;
      }
      submitting = true;
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.setAttribute("aria-busy", "true");
        submitButton.innerHTML = '<span class="hs-copy-test-loader" aria-hidden="true"></span><span>' + t("Vytvářím kopii...") + '</span>';
      }
    });
  }

  Array.prototype.forEach.call(document.querySelectorAll(".hs-copy-test-cleanup-form"), function (cleanupForm) {
    cleanupForm.addEventListener("submit", function (event) {
      event.preventDefault();
      submitCleanup(cleanupForm);
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal && modal.classList.contains("is-open")) {
      event.preventDefault();
      closeModal();
    }
  });

  clearBranchSelect("Nejdříve vyberte firmu...");
}());

/* V205 – modal pro ruční přiřazení skutečného HairSoft ID kopii. */
(function () {
  "use strict";

  var modal = document.getElementById("hsCustomerCopyManualModal");
  if (!modal) return;

  var input = document.getElementById("hsCustomerCopyHairSoftId");
  var form = document.getElementById("hsCustomerCopyManualForm");
  var lastFocus = null;
  var submitting = false;

  function openModal(trigger) {
    lastFocus = trigger || document.activeElement;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("hs-copy-test-lock");
    window.setTimeout(function () {
      if (input) input.focus();
    }, 20);
  }

  function closeModal() {
    if (submitting) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("hs-copy-test-lock");
    if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
    lastFocus = null;
  }

  document.addEventListener("click", function (event) {
    var openTrigger = event.target.closest ? event.target.closest("[data-hs-copy-manual-open]") : null;
    var closeTrigger = event.target.closest ? event.target.closest("[data-hs-copy-manual-close]") : null;
    if (openTrigger) {
      event.preventDefault();
      openModal(openTrigger);
      return;
    }
    if (closeTrigger) {
      event.preventDefault();
      closeModal();
    }
  });

  if (input) {
    input.addEventListener("input", function () {
      input.value = String(input.value || "").replace(/[^0-9]/g, "").slice(0, 10);
    });
  }

  if (form) {
    form.addEventListener("submit", function (event) {
      if (submitting) {
        event.preventDefault();
        return;
      }
      if (!input || !/^[0-9]+$/.test(input.value || "")) {
        event.preventDefault();
        if (input) input.focus();
        return;
      }
      submitting = true;
      var button = form.querySelector("button[type='submit']");
      if (button) {
        button.disabled = true;
        button.setAttribute("aria-busy", "true");
        button.innerHTML = '<span class="hs-copy-test-loader" aria-hidden="true"></span><span>Ukládám ID...</span>';
      }
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      event.preventDefault();
      closeModal();
    }
  });
}());
