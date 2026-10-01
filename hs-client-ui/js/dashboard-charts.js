/* HairSoft Klient V90 – vlastní interaktivní tooltipy grafů pro myš i dotyk. */
(function () {
  "use strict";

  var CHART_IDS = ["canvas", "canvas2", "canvas3", "canvas4"];
  var dashboardTooltip = null;
  var tooltipDismissEventsInstalled = false;

  function translate(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function svgIcon(name) {
    if (name === "previous") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>';
    }
    if (name === "next") {
      return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>';
    }
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"></path><path d="m7 16 4-5 4 3 5-7"></path><path d="M16 7h4v4"></path></svg>';
  }

  function formatNumber(value) {
    var numeric = Number(value);
    var language = String(document.documentElement.lang || "cs").toLowerCase();
    if (!isFinite(numeric)) return String(value == null ? "" : value);
    try {
      return new Intl.NumberFormat(language, { maximumFractionDigits: 2 }).format(numeric);
    } catch (error) {
      return String(numeric).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }
  }

  function colorWithAlpha(color, alpha) {
    var value = String(color || "").trim();
    var match;
    if (/^#[0-9a-f]{3}$/i.test(value)) {
      value = "#" + value.charAt(1) + value.charAt(1) + value.charAt(2) + value.charAt(2) + value.charAt(3) + value.charAt(3);
    }
    match = value.match(/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i);
    if (match) return "rgba(" + parseInt(match[1], 16) + "," + parseInt(match[2], 16) + "," + parseInt(match[3], 16) + "," + alpha + ")";
    match = value.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (match) return "rgba(" + match[1] + "," + match[2] + "," + match[3] + "," + alpha + ")";
    return value;
  }

  function installRoundedBars() {
    var Rectangle;
    var originalDraw;
    if (!window.Chart || !window.Chart.elements || !window.Chart.elements.Rectangle) return;
    Rectangle = window.Chart.elements.Rectangle;
    if (Rectangle.prototype._hsRoundedDashboardBars) return;
    originalDraw = Rectangle.prototype.draw;
    Rectangle.prototype.draw = function () {
      var chart = this._chart;
      var canvas = chart && chart.canvas;
      var view = this._view;
      var context;
      var left;
      var right;
      var top;
      var bottom;
      var radius;

      if (!canvas || CHART_IDS.indexOf(canvas.id) === -1 || !view || !isFinite(view.width)) {
        return originalDraw.apply(this, arguments);
      }
      context = chart.ctx;
      left = view.x - view.width / 2;
      right = view.x + view.width / 2;
      top = Math.min(view.y, view.base);
      bottom = Math.max(view.y, view.base);
      radius = Math.max(0, Math.min(7, Math.abs(right - left) / 2, Math.abs(bottom - top) / 2));

      context.save();
      context.fillStyle = view.backgroundColor;
      context.beginPath();
      context.moveTo(left, bottom);
      context.lineTo(left, top + radius);
      context.quadraticCurveTo(left, top, left + radius, top);
      context.lineTo(right - radius, top);
      context.quadraticCurveTo(right, top, right, top + radius);
      context.lineTo(right, bottom);
      context.closePath();
      context.fill();
      context.restore();
    };
    Rectangle.prototype._hsRoundedDashboardBars = true;
  }

  function setChartDefaults() {
    var defaults;
    if (!window.Chart || !window.Chart.defaults || !window.Chart.defaults.global) return;
    defaults = window.Chart.defaults.global;
    defaults.defaultFontFamily = "Tahoma, Arial, sans-serif";
    defaults.defaultFontColor = "#718197";
    defaults.defaultFontSize = 11;
    defaults.animation.duration = 650;
    defaults.animation.easing = "easeOutQuart";
    if (defaults.hover) defaults.hover.animationDuration = 0;
    if (defaults.tooltips) {
      defaults.tooltips.backgroundColor = "rgba(29, 48, 70, .96)";
      defaults.tooltips.titleFontFamily = "Tahoma, Arial, sans-serif";
      defaults.tooltips.bodyFontFamily = "Tahoma, Arial, sans-serif";
      defaults.tooltips.cornerRadius = 10;
      defaults.tooltips.xPadding = 12;
      defaults.tooltips.yPadding = 10;
      defaults.tooltips.caretPadding = 8;
      defaults.tooltips.displayColors = true;
    }
    installRoundedBars();
  }

  function enhanceTitle(panel) {
    var title = panel.querySelector(".panel-heading .panel-title");
    var text;
    if (!title || title.querySelector(".hs-chart-heading__icon")) return;
    text = String(title.textContent || "").replace(/\s+/g, " ").trim();
    title.textContent = "";
    title.classList.add("hs-chart-heading");
    title.innerHTML = '<span class="hs-chart-heading__icon">' + svgIcon("chart") + '</span>' +
      '<span class="hs-chart-heading__copy"><strong>' + text + '</strong><small>' + translate("Měsíční vývoj podle středisek") + '</small></span>';
  }

  function enhanceActions(panel) {
    var actions = panel.querySelector(".panel-heading .actions");
    var anchors;
    var controls;
    var index;
    var element;
    if (!actions || actions.getAttribute("data-hs-chart-actions") === "true") return;
    actions.classList.add("hs-chart-actions");
    anchors = actions.querySelectorAll("a");
    for (index = 0; index < anchors.length; index += 1) {
      element = anchors[index];
      element.classList.add("hs-chart-year-button");
      if (index === 0) {
        element.innerHTML = svgIcon("previous");
        element.title = translate("Předchozí rok");
        element.setAttribute("aria-label", translate("Předchozí rok"));
      } else {
        element.innerHTML = svgIcon("next");
        element.title = translate("Následující rok");
        element.setAttribute("aria-label", translate("Následující rok"));
      }
    }
    controls = actions.children;
    for (index = 0; index < controls.length; index += 1) {
      element = controls[index];
      if (element.tagName === "SPAN") element.classList.add("hs-chart-year");
      if (element.tagName !== "I") continue;
      element.classList.add("hs-chart-panel-button");
      element.setAttribute("role", "button");
      element.setAttribute("tabindex", "0");
      if (element.classList.contains("fa-expand")) element.title = translate("Zvětšit graf");
      if (element.classList.contains("fa-chevron-down")) element.title = translate("Sbalit graf");
      if (element.classList.contains("fa-times")) element.title = translate("Skrýt graf");
      element.setAttribute("aria-label", element.title || translate("Ovládání grafu"));
      element.addEventListener("keydown", function (event) {
        if (event.key !== "Enter" && event.key !== " ") return;
        event.preventDefault();
        this.click();
      });
    }
    actions.setAttribute("data-hs-chart-actions", "true");
  }

  function enhanceLegend(panel) {
    var containers = panel.querySelectorAll(".col-lg-12[align='center']");
    var container = containers.length ? containers[0] : null;
    var rows;
    var index;
    if (!container || container.getAttribute("data-hs-chart-legend") === "true") return;
    container.classList.add("hs-chart-summary");
    rows = container.querySelectorAll("tr");
    for (index = 0; index < rows.length; index += 1) rows[index].classList.add("hs-chart-summary__item");
    container.setAttribute("data-hs-chart-legend", "true");
  }

  function enhanceBody(panel) {
    var body = panel.querySelector(".panel-body");
    var hint;
    if (!body || body.querySelector(".hs-chart-scroll-hint")) return;
    body.classList.add("hs-chart-body");
    hint = document.createElement("div");
    hint.className = "hs-chart-scroll-hint";
    hint.textContent = translate("Posunutím zobrazíte další měsíce");
    body.insertBefore(hint, body.firstChild);
  }

  function decoratePanel(canvas) {
    var panel = canvas.closest(".panel.panel-default");
    var row;
    if (!panel || panel.getAttribute("data-hs-chart-card") === "true") return;
    panel.classList.add("hs-dashboard-chart-card");
    row = panel.closest(".row");
    if (row) row.classList.add("hs-dashboard-charts-row");
    enhanceTitle(panel);
    enhanceActions(panel);
    enhanceLegend(panel);
    enhanceBody(panel);
    panel.setAttribute("data-hs-chart-card", "true");
  }

  function currencyFromCanvas(canvas) {
    if (canvas && canvas.hasAttribute("data-hs-chart-currency")) return canvas.getAttribute("data-hs-chart-currency");
    var panel = canvas ? canvas.closest(".hs-dashboard-chart-card") : null;
    var valueCell = panel ? panel.querySelector(".hs-chart-summary__item td:last-child") : null;
    var text = valueCell ? String(valueCell.textContent || "").replace(/\u00a0/g, " ").trim() : "";
    var match = text.match(/[\d\s.,-]+\s*(\D.*?)\s*$/);
    return match && match[1] ? match[1].trim() : "";
  }

  function formatAmount(value, currency) {
    var formatted = formatNumber(value);
    return formatted + (currency ? " " + currency : "");
  }

  function formatAxisAmount(value, currency) {
    var numeric = Number(value);
    var absolute = Math.abs(numeric);
    var shortened;
    if (!isFinite(numeric)) return String(value == null ? "" : value);
    if (absolute >= 1000000) {
      shortened = formatNumber(numeric / 1000000) + " " + translate("mil.");
    } else if (absolute >= 1000) {
      shortened = formatNumber(numeric / 1000) + " " + translate("tis.");
    } else {
      shortened = formatNumber(numeric);
    }
    return shortened + (currency ? " " + currency : "");
  }

  function getDashboardTooltip() {
    if (dashboardTooltip && document.body.contains(dashboardTooltip)) return dashboardTooltip;
    dashboardTooltip = document.createElement("div");
    dashboardTooltip.id = "hs-dashboard-chart-tooltip";
    dashboardTooltip.className = "hs-dashboard-chart-tooltip";
    dashboardTooltip.setAttribute("role", "tooltip");
    dashboardTooltip.setAttribute("aria-hidden", "true");
    document.body.appendChild(dashboardTooltip);
    return dashboardTooltip;
  }

  function hideDashboardTooltip() {
    if (!dashboardTooltip) return;
    dashboardTooltip.classList.remove("is-visible");
    dashboardTooltip.setAttribute("aria-hidden", "true");
  }

  function eventPoint(event) {
    var source = event;
    if (event.changedTouches && event.changedTouches.length) source = event.changedTouches[0];
    else if (event.touches && event.touches.length) source = event.touches[0];
    if (!source || source.clientX == null || source.clientY == null) return null;
    return { x: source.clientX, y: source.clientY };
  }

  function nearestMonthIndex(chart, canvas, point) {
    var rect = canvas.getBoundingClientRect();
    var chartArea = chart.chartArea;
    var localX;
    var localY;
    var meta;
    var elements;
    var closestIndex = -1;
    var closestDistance = Infinity;
    var index;
    var model;
    var distance;
    if (!rect.width || !rect.height || !chartArea) return -1;
    localX = (point.x - rect.left) * ((chart.width || rect.width) / rect.width);
    localY = (point.y - rect.top) * ((chart.height || rect.height) / rect.height);
    if (localX < chartArea.left || localX > chartArea.right || localY < chartArea.top || localY > chartArea.bottom) return -1;

    meta = chart.getDatasetMeta && chart.data.datasets.length ? chart.getDatasetMeta(0) : null;
    elements = meta && meta.data ? meta.data : [];
    for (index = 0; index < elements.length; index += 1) {
      model = elements[index]._view || elements[index]._model;
      if (!model || !isFinite(model.x)) continue;
      distance = Math.abs(model.x - localX);
      if (distance < closestDistance) {
        closestDistance = distance;
        closestIndex = index;
      }
    }
    if (closestIndex !== -1) return closestIndex;
    if (!chart.data.labels || !chart.data.labels.length) return -1;
    return Math.max(0, Math.min(chart.data.labels.length - 1,
      Math.floor((localX - chartArea.left) / ((chartArea.right - chartArea.left) / chart.data.labels.length))));
  }

  function datasetValue(dataset, index) {
    var value = dataset && dataset.data ? dataset.data[index] : null;
    if (value && typeof value === "object") {
      if (value.y != null) value = value.y;
      else if (value.value != null) value = value.value;
    }
    return value == null || value === "" ? 0 : value;
  }

  function positionDashboardTooltip(tooltip, point) {
    var gap = 14;
    var edge = 12;
    var left = point.x + gap;
    var top = point.y + gap;
    var width = tooltip.offsetWidth;
    var height = tooltip.offsetHeight;
    if (left + width > window.innerWidth - edge) left = point.x - width - gap;
    if (top + height > window.innerHeight - edge) top = point.y - height - gap;
    left = Math.max(edge, Math.min(left, window.innerWidth - width - edge));
    top = Math.max(edge, Math.min(top, window.innerHeight - height - edge));
    tooltip.style.left = Math.round(left) + "px";
    tooltip.style.top = Math.round(top) + "px";
  }

  function showDashboardTooltip(chart, canvas, index, point) {
    if (chart._hsDashboardHideEmptyTooltip) { hideDashboardTooltip(); return; }
    var tooltip = getDashboardTooltip();
    var title = document.createElement("div");
    var datasets = chart.data.datasets || [];
    var currency = currencyFromCanvas(canvas);
    var datasetIndex;
    var dataset;
    var row;
    var dot;
    var label;
    var value;
    var color;
    tooltip.textContent = "";
    title.className = "hs-dashboard-chart-tooltip__title";
    title.textContent = chart.data.labels[index] || "";
    tooltip.appendChild(title);

    for (datasetIndex = 0; datasetIndex < datasets.length; datasetIndex += 1) {
      dataset = datasets[datasetIndex];
      row = document.createElement("div");
      row.className = "hs-dashboard-chart-tooltip__row";
      dot = document.createElement("span");
      dot.className = "hs-dashboard-chart-tooltip__dot";
      color = dataset._hsDashboardOriginalColor || dataset.hoverBackgroundColor || "#4f6f9d";
      if (typeof color === "string") dot.style.backgroundColor = color;
      label = document.createElement("span");
      label.className = "hs-dashboard-chart-tooltip__label";
      label.textContent = dataset.label || translate("Středisko");
      value = document.createElement("strong");
      value.className = "hs-dashboard-chart-tooltip__value";
      value.textContent = formatAmount(datasetValue(dataset, index), currency);
      row.appendChild(dot);
      row.appendChild(label);
      row.appendChild(value);
      tooltip.appendChild(row);
    }
    tooltip.classList.add("is-visible");
    tooltip.setAttribute("aria-hidden", "false");
    positionDashboardTooltip(tooltip, point);
  }

  function showTooltipFromEvent(chart, canvas, event) {
    var point = eventPoint(event);
    var index;
    if (!point) return;
    index = nearestMonthIndex(chart, canvas, point);
    if (index === -1) {
      hideDashboardTooltip();
      return;
    }
    showDashboardTooltip(chart, canvas, index, point);
  }

  function installTooltipDismissEvents() {
    if (tooltipDismissEventsInstalled) return;
    document.addEventListener("mousedown", function (event) {
      if (!event.target || CHART_IDS.indexOf(event.target.id) === -1) hideDashboardTooltip();
    });
    document.addEventListener("touchstart", function (event) {
      if (!event.target || CHART_IDS.indexOf(event.target.id) === -1) hideDashboardTooltip();
    }, { passive: true });
    window.addEventListener("resize", hideDashboardTooltip);
    window.addEventListener("scroll", hideDashboardTooltip, true);
    tooltipDismissEventsInstalled = true;
  }

  function bindDashboardTooltip(chart, canvas) {
    var touchStart = null;
    var lastTouchAt = 0;
    if (!chart || !canvas || canvas.getAttribute("data-hs-chart-tooltip") === "true") return;
    canvas.addEventListener("mousemove", function (event) {
      if (Date.now() - lastTouchAt < 700) return;
      showTooltipFromEvent(chart, canvas, event);
    });
    canvas.addEventListener("mouseleave", function () {
      // A tap can synthesize a mouseleave immediately after touchend.
      // Keep the touch tooltip until the next tap outside, scroll or resize.
      if (Date.now() - lastTouchAt >= 700) hideDashboardTooltip();
    });
    canvas.addEventListener("touchstart", function (event) {
      touchStart = eventPoint(event);
    }, { passive: true });
    canvas.addEventListener("touchend", function (event) {
      var end = eventPoint(event);
      if (!touchStart || !end || Math.abs(end.x - touchStart.x) > 10 || Math.abs(end.y - touchStart.y) > 10) {
        touchStart = null;
        return;
      }
      lastTouchAt = Date.now();
      showTooltipFromEvent(chart, canvas, event);
      touchStart = null;
    }, { passive: true });
    canvas.addEventListener("click", function (event) {
      if (Date.now() - lastTouchAt >= 700) showTooltipFromEvent(chart, canvas, event);
    });
    canvas.setAttribute("data-hs-chart-tooltip", "true");
    installTooltipDismissEvents();
  }

  function prepareDataset(canvas, dataset) {
    var context = canvas && canvas.getContext ? canvas.getContext("2d") : null;
    var original = dataset._hsDashboardOriginalColor || dataset.backgroundColor || "#4f6f9d";
    var gradient;
    if (Array.isArray(original)) original = original[0];
    dataset._hsDashboardOriginalColor = original;
    dataset.borderWidth = 0;
    dataset.hoverBorderWidth = 0;
    dataset.barPercentage = .72;
    dataset.categoryPercentage = .76;
    dataset.maxBarThickness = 30;
    dataset.hoverBackgroundColor = original;
    if (!context || !context.createLinearGradient) return;
    gradient = context.createLinearGradient(0, 18, 0, 315);
    gradient.addColorStop(0, colorWithAlpha(original, .94));
    gradient.addColorStop(1, colorWithAlpha(original, .48));
    dataset.backgroundColor = gradient;
  }

  function prepareData(canvas, data) {
    var datasets = data && data.datasets ? data.datasets : [];
    var index;
    if (data && data.labels) {
      data.labels = data.labels.map(function (label) { return translate(label); });
    }
    for (index = 0; index < datasets.length; index += 1) prepareDataset(canvas, datasets[index]);
    return data;
  }

  function createChartOptions(canvas) {
    var currency = currencyFromCanvas(canvas);
    return {
      responsive: true,
      maintainAspectRatio: false,
      responsiveAnimationDuration: 0,
      events: ["mousemove", "mouseout", "click", "touchstart", "touchmove"],
      animation: {
        duration: 650,
        easing: "easeOutQuart"
      },
      layout: {
        padding: { top: 16, right: 18, bottom: 10, left: 8 }
      },
      legend: {
        display: false
      },
      title: {
        display: false
      },
      hover: {
        mode: "index",
        intersect: false,
        animationDuration: 0
      },
      tooltips: {
        enabled: false,
        mode: "index",
        intersect: false,
        position: "nearest",
        backgroundColor: "rgba(29, 48, 70, .97)",
        titleFontFamily: "Tahoma, Arial, sans-serif",
        bodyFontFamily: "Tahoma, Arial, sans-serif",
        titleFontSize: 12,
        titleFontStyle: "600",
        bodyFontSize: 12,
        bodySpacing: 5,
        cornerRadius: 10,
        xPadding: 13,
        yPadding: 11,
        caretPadding: 8,
        displayColors: true,
        callbacks: {
          title: function (items, data) {
            if (!items || !items.length) return "";
            return data.labels[items[0].index] || "";
          },
          label: function (tooltipItem, data) {
            var dataset = data.datasets[tooltipItem.datasetIndex] || {};
            var value = tooltipItem.yLabel != null ? tooltipItem.yLabel : tooltipItem.value;
            return (dataset.label ? dataset.label + ": " : "") + formatAmount(value, currency);
          }
        }
      },
      elements: {
        rectangle: {
          borderWidth: 0,
          borderSkipped: "bottom"
        }
      },
      scales: {
        xAxes: [{
          gridLines: {
            display: false,
            drawBorder: false,
            zeroLineColor: "transparent"
          },
          ticks: {
            display: true,
            autoSkip: false,
            fontColor: "#607289",
            fontFamily: "Tahoma, Arial, sans-serif",
            fontSize: 10,
            fontStyle: "600",
            maxRotation: 0,
            minRotation: 0,
            padding: 10
          }
        }],
        yAxes: [{
          gridLines: {
            display: true,
            drawBorder: false,
            color: "rgba(92, 112, 139, .12)",
            zeroLineColor: "rgba(92, 112, 139, .2)",
            borderDash: [3, 4]
          },
          scaleLabel: {
            display: true,
            labelString: translate("Částka"),
            fontColor: "#8391a3",
            fontFamily: "Tahoma, Arial, sans-serif",
            fontSize: 10,
            fontStyle: "600",
            padding: 8
          },
          ticks: {
            display: true,
            beginAtZero: true,
            maxTicksLimit: 7,
            fontColor: "#718197",
            fontFamily: "Tahoma, Arial, sans-serif",
            fontSize: 10,
            padding: 10,
            callback: function (value) { return formatAxisAmount(value, currency); }
          }
        }]
      }
    };
  }

  window.hsCreateDashboardChart = function (canvasId, data) {
    var canvas = document.getElementById(canvasId);
    var chart;
    if (!canvas || !window.Chart) return null;
    installRoundedBars();
    chart = new window.Chart(canvas.getContext("2d"), {
      type: "bar",
      data: prepareData(canvas, data),
      options: createChartOptions(canvas)
    });
    bindDashboardTooltip(chart, canvas);
    return chart;
  };

  function initialize() {
    var dashboard;
    var index;
    var canvas;
    /* V197: nastavit Tahoma do Chart.js globálně ještě před window.onload konstruktory legacy grafů. */
    setChartDefaults();
    dashboard = document.querySelector("#main-content.hs-dashboard-main");
    if (!dashboard || dashboard.getAttribute("data-hs-dashboard-charts") === "true") return;
    for (index = 0; index < CHART_IDS.length; index += 1) {
      canvas = document.getElementById(CHART_IDS[index]);
      if (canvas) decoratePanel(canvas);
    }
    dashboard.setAttribute("data-hs-dashboard-charts", "true");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
}());
