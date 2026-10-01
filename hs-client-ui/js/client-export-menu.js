(function () {
  "use strict";

  function hasClass(element, className) {
    return Boolean(element && element.classList && element.classList.contains(className));
  }

  function exportActionFrom(target) {
    var node = target;
    var parent;

    while (node && node !== document) {
      if (hasClass(node, "dt-button")) {
        parent = node.parentNode;
        while (parent && parent !== document) {
          if (hasClass(parent, "dt-button-collection")) {
            if (!hasClass(node, "buttons-collection") && !hasClass(node, "disabled")) return node;
            return null;
          }
          parent = parent.parentNode;
        }
      }
      node = node.parentNode;
    }

    return null;
  }

  function removeNode(node) {
    if (node && node.parentNode) node.parentNode.removeChild(node);
  }

  function closeExportMenu() {
    var backgrounds = document.querySelectorAll("div.dt-button-background");
    var collections;
    var index;

    for (index = 0; index < backgrounds.length; index += 1) {
      if (typeof backgrounds[index].click === "function") backgrounds[index].click();
    }

    window.setTimeout(function () {
      collections = document.querySelectorAll("div.dt-button-collection");
      backgrounds = document.querySelectorAll("div.dt-button-background");

      for (index = 0; index < collections.length; index += 1) removeNode(collections[index]);
      for (index = 0; index < backgrounds.length; index += 1) removeNode(backgrounds[index]);

      collections = document.querySelectorAll(".dt-button.buttons-collection.active");
      for (index = 0; index < collections.length; index += 1) collections[index].classList.remove("active");
    }, 0);
  }

  document.addEventListener("click", function (event) {
    if (!exportActionFrom(event.target)) return;
    window.setTimeout(closeExportMenu, 0);
  }, true);
}());
