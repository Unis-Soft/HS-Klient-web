/* HairSoft Klient V104 – moderní a responzivní vrstva Měsíčních tržeb. */
(function () {
  "use strict";

  var TABLES = "#table_RocniCelkoveTrzbyZaObsluhu";
  var PALETTE = ["#17A7A2", "#526B91", "#E3A13A", "#887BD5", "#D76083", "#4D98C5", "#67AF7A", "#A95069", "#C58E3C", "#7389A4"];
  var chartColors = Object.create(null);
  var colorIndex = 0;
  var centerPluginReady = false;

  function clean(value) { return String(value == null ? "" : value).replace(/\u00a0/g, " ").replace(/\s+/g, " ").trim(); }
  function fold(value) {
    var text = clean(value).toLowerCase();
    return text.normalize ? text.normalize("NFD").replace(/[\u0300-\u036f]/g, "") : text;
  }
  function tr(source) { return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source; }
  function setText(node, source) {
    if (!node) return;
    if (typeof window.hsSetTranslatedText === "function") window.hsSetTranslatedText(node, source);
    else node.textContent = tr(source);
  }
  function setAttr(node, name, source) {
    if (!node) return;
    if (typeof window.hsSetTranslatedAttribute === "function") window.hsSetTranslatedAttribute(node, name, source);
    else node.setAttribute(name, tr(source));
  }
  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text != null) node.textContent = text;
    return node;
  }
  function svg(name) {
    var icons = {
      calendar: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"></rect><path d="M8 3v4M16 3v4M3 10h18"></path></svg>',
      table: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 9h18M8 9v11M15 9v11"></path></svg>',
      chart: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg>',
      donut: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9h-9V3Z"></path><path d="M15 3.6A9 9 0 0 1 20.4 9H15V3.6Z"></path></svg>',
      settings: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"></path><circle cx="16" cy="7" r="2"></circle><circle cx="8" cy="17" r="2"></circle></svg>',
      refresh: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5"></path><path d="M19 12a7 7 0 1 0-2 5"></path></svg>',
      trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"></path></svg>',
      export: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 20h14"></path></svg>',
      left: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>',
      right: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>'
    };
    return icons[name] || icons.table;
  }
  function isPage() {
    return /[?&]strana=TrzbyDleObsluhy(?:&|$)/i.test(location.search) || Boolean(document.querySelector(TABLES));
  }
  function directHeading(panel) {
    var children = panel ? panel.children : [];
    for (var i = 0; i < children.length; i += 1) if (children[i].classList && children[i].classList.contains("panel-heading")) return children[i];
    return null;
  }
  function enhanceHeading(panel, titleSource, subtitleSource, iconName) {
    var heading = directHeading(panel), title, actions, copy, strong, small, icon;
    if (!heading) return;
    title = heading.querySelector(".panel-title");
    actions = heading.querySelector(".actions");
    if (!title || title.dataset.hsAnnualHeading === "true") return;
    title.textContent = "";
    title.className = "panel-title hs-annual-heading";
    title.dataset.hsAnnualHeading = "true";
    icon = el("span", "hs-annual-heading__icon"); icon.innerHTML = svg(iconName || "table");
    copy = el("span", "hs-annual-heading__copy");
    strong = el("strong"); setText(strong, titleSource);
    copy.appendChild(strong);
    if (subtitleSource) { small = el("small"); setText(small, subtitleSource); copy.appendChild(small); }
    title.appendChild(icon); title.appendChild(copy);
    if (actions) actions.classList.add("hs-annual-panel-actions");
  }

  function hideLegacyPeriodNav(main) {
    Array.prototype.forEach.call(main.querySelectorAll('.panel-heading .actions a[href*="NavratovyRok"]'), function (link) {
      link.classList.add("hs-annual-legacy-period");
      var span = link.nextElementSibling;
      if (span && span.tagName === "SPAN") span.classList.add("hs-annual-legacy-period");
    });
  }
  function tableHeaders(table) {
    var row = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
    return row ? Array.prototype.map.call(row.cells, function (cell) { return clean(cell.textContent); }) : [];
  }
  function isTotal(value) { return /\b(celkem|celkom|total|gesamt|summe)\b/.test(fold(value)); }
  function rowsOf(table) {
    var rows = [];
    Array.prototype.forEach.call(table.tBodies || [], function (body) { Array.prototype.forEach.call(body.rows, function (row) { rows.push(row); }); });
    if (table.tFoot) Array.prototype.forEach.call(table.tFoot.rows, function (row) { rows.push(row); });
    return rows;
  }
  function money(value) {
    var text = clean(value);
    return !text || text === "-" ? "—" : (/Kč|CZK|EUR|USD|€|\$|£/i.test(text) ? text : text + " " + (window.hsAnnualCurrency || "Kč"));
  }
  function mobileCards(table, host) {
    var container = table._hsAnnualCards, individual = table.id === "table_RocniCelkoveTrzbyZaObsluhu", metricStart = individual ? 3 : 2;
    var metrics = ["Tržby celkem", "Za služby", "Za prodej", "Ceniny", "Kredit"], count = 0;
    if (!container) { container = el("div", "hs-annual-mobile-cards"); table._hsAnnualCards = container; }
    if (!host.contains(container)) { var bar = host.querySelector(".hs-annual-table-toolbar"); host.insertBefore(container, bar ? bar.nextSibling : host.firstChild); }
    container.textContent = "";
    rowsOf(table).forEach(function (row) {
      var cells = Array.prototype.map.call(row.cells, function (cell) { return clean(cell.textContent); });
      if (cells.length < metricStart + 2 || row.classList.contains("child")) return;
      var total = isTotal(cells.join(" ")), card = el("article", "hs-annual-mobile-card" + (total ? " hs-annual-mobile-card--total" : ""));
      var head = el("header", "hs-annual-mobile-card__head"), identity = el("div"), kicker = el("span", "hs-annual-mobile-card__kicker"), title = el("strong", "hs-annual-mobile-card__title"), badge = el("span", "hs-annual-mobile-card__badge", total ? "Σ" : cells[0]);
      setText(kicker, total ? "Součet" : (individual ? "Obsluha" : "Středisko | firma"));
      if (total) setText(title, "Celkem"); else title.textContent = cells[individual ? 2 : 1] || "—";
      identity.appendChild(kicker); identity.appendChild(title);
      if (individual && cells[1] && !total) identity.appendChild(el("span", "hs-annual-mobile-card__subtitle", cells[1]));
      head.appendChild(identity); head.appendChild(badge); card.appendChild(head);
      var grid = el("div", "hs-annual-mobile-card__metrics");
      for (var i = metricStart; i < cells.length; i += 2) {
        var metric = el("div", "hs-annual-mobile-metric" + (i === metricStart ? " hs-annual-mobile-metric--primary" : ""));
        var label = el("span", "hs-annual-mobile-metric__label"), gross = el("strong", "hs-annual-mobile-metric__value", money(cells[i])), net = el("span", "hs-annual-mobile-metric__net"), netLabel = el("span");
        setText(label, metrics[(i - metricStart) / 2] || "Hodnota"); setText(netLabel, "bez DPH"); net.appendChild(netLabel); net.appendChild(document.createTextNode(" · " + money(cells[i + 1])));
        metric.appendChild(label); metric.appendChild(gross); metric.appendChild(net); grid.appendChild(metric);
      }
      card.appendChild(grid); container.appendChild(card); count += 1;
    });
    if (!count) { var empty = el("p", "hs-annual-empty"); setText(empty, "Pro vybraný rok nejsou k dispozici žádné záznamy."); container.appendChild(empty); }
  }
  function normalizeExport(wrapper) {
    var buttons = wrapper.querySelectorAll(".dt-button.buttons-collection");
    Array.prototype.forEach.call(buttons, function (button) {
      if (button.dataset.hsAnnualExport === "true") return;
      button.textContent = ""; var icon = el("span", "hs-annual-export-icon"); icon.innerHTML = svg("export"); var label = el("span"); setText(label, "Export"); button.appendChild(icon); button.appendChild(label); button.dataset.hsAnnualExport = "true"; setAttr(button, "aria-label", "Export");
    });
  }
  function enhanceTable(table) {
    if (!table) return;
    if (table.dataset.hsAnnualTable !== "true") { table.dataset.hsAnnualTable = "true"; table.classList.add("hs-annual-table"); }
    var individual = table.id === "table_RocniCelkoveTrzbyZaObsluhu", panel = table.closest(".panel"), wrapper = table.closest(".dataTables_wrapper") || table.parentElement, scroll, legacyScroll;
    if (wrapper.classList && wrapper.classList.contains("dataTables_wrapper")) {
      legacyScroll = wrapper.parentElement && wrapper.parentElement.closest(".hs-annual-table-scroll");
      if (legacyScroll && legacyScroll !== wrapper && legacyScroll.parentNode) { legacyScroll.parentNode.insertBefore(wrapper, legacyScroll); if (!legacyScroll.children.length) legacyScroll.parentNode.removeChild(legacyScroll); }
    }
    if (panel) { panel.classList.add("hs-annual-card", "hs-annual-table-card"); enhanceHeading(panel, individual ? "Roční celkové tržby za obsluhu" : "Roční celkové tržby za obsluhu", "Přehled tržeb za vybraný rok", "table"); }
    wrapper.classList.add("hs-annual-table-wrapper");
    var buttons = wrapper.querySelector(".dt-buttons"), toolbar = wrapper.querySelector(".hs-annual-table-toolbar");
    if (buttons && !toolbar) { toolbar = el("div", "hs-annual-table-toolbar"); wrapper.insertBefore(toolbar, wrapper.firstChild); }
    if (buttons) { if (buttons.parentNode !== toolbar) toolbar.appendChild(buttons); normalizeExport(wrapper); }
    if (!table.closest(".hs-annual-table-scroll")) { scroll = el("div", "hs-annual-table-scroll"); table.parentNode.insertBefore(scroll, table); scroll.appendChild(table); }
    tableHeaders(table).forEach(function (label, index) { rowsOf(table).forEach(function (row) { if (row.cells[index]) row.cells[index].dataset.hsLabel = label; }); });
    rowsOf(table).forEach(function (row) { if (isTotal(row.textContent)) row.classList.add("hs-annual-total-row"); });
    mobileCards(table, wrapper);
    if (window.jQuery && table.dataset.hsAnnualDraw !== "true") { table.dataset.hsAnnualDraw = "true"; window.jQuery(table).on("draw.dt.hsAnnual", function () { var liveWrapper = table.closest(".dataTables_wrapper") || table.parentElement; mobileCards(table, liveWrapper); normalizeExport(liveWrapper); }); }
  }

  function chartInstance(canvas) {
    if (!window.Chart || !window.Chart.instances) return null;
    var instances = window.Chart.instances;
    for (var key in instances) if (Object.prototype.hasOwnProperty.call(instances, key)) {
      var instance = instances[key]; if (instance && (instance.canvas === canvas || (instance.chart && instance.chart.canvas === canvas))) return instance;
    }
    return null;
  }
  function ensureChartsInitialized(main) {
    if (typeof window.Chart !== "function") return;
    var configs = window.hsAnnualConfigs || {};
    Object.keys(configs).forEach(function (id) {
      var canvas = document.getElementById(id), chart, sourceLabels;
      if (!canvas || chartInstance(canvas)) return;
      if (configs[id].type === "bar" && typeof window.hsCreateDashboardChart === "function") {
        canvas.setAttribute("data-hs-chart-currency", window.hsAnnualCurrency || "Kč");
        (configs[id].data.datasets || []).forEach(function (dataset, index) { dataset.backgroundColor = PALETTE[index % PALETTE.length]; });
        sourceLabels = configs[id].data.labels.slice();
        chart = window.hsCreateDashboardChart(id, configs[id].data);
        chart._hsAnnualSources = sourceLabels;
        chart._hsAnnualDashboard = true;
      } else new window.Chart(canvas.getContext("2d"), configs[id]);
    });
  }
  function number(value) { var parsed = Number(String(value == null ? "" : value).replace(/\s/g, "").replace(",", ".")); return isFinite(parsed) ? parsed : 0; }
  function total(dataset) { return (dataset && dataset.data || []).reduce(function (sum, value) { return sum + number(value); }, 0); }
  function compact(value) { if (Math.abs(value) >= 1000000) return (Math.round(value / 100000) / 10).toString().replace(".", ",") + " mil."; return Math.round(value).toLocaleString("cs-CZ"); }
  function detailed(value, currency) { return Number(value).toLocaleString("cs-CZ", { minimumFractionDigits: currency ? 2 : 0, maximumFractionDigits: currency ? 2 : 0 }) + (currency ? " " + (window.hsAnnualCurrency || "Kč") : ""); }
  function color(label) { var key = fold(label) || "item-" + colorIndex; if (!chartColors[key]) { chartColors[key] = PALETTE[colorIndex % PALETTE.length]; colorIndex += 1; } return chartColors[key]; }
  function canvasOf(chart) { return chart && (chart.canvas || (chart.chart && chart.chart.canvas)); }
  function contextOf(chart) { return chart && (chart.ctx || (chart.chart && chart.chart.ctx)); }
  function resizeAndUpdate(chart) {
    if (!chart) return;
    if (typeof chart.stop === "function") chart.stop();
    if (typeof chart.resize === "function") chart.resize();
    if (typeof chart.update === "function") chart.update(0);
  }
  function hasChartValues(chart) {
    return (chart.data.datasets || []).some(function (dataset) { return (dataset.data || []).some(function (value) { return number(value) !== 0; }); });
  }
  function arcModel(chart) {
    if (chart && !hasChartValues(chart)) {
      var area = chart.chartArea || { left: 0, top: 0, right: chart.chart.width, bottom: chart.chart.height };
      var radius = Math.max(0, Math.min(area.right - area.left, area.bottom - area.top) / 2 - 8);
      return { x: (area.left + area.right) / 2, y: (area.top + area.bottom) / 2, outerRadius: radius, innerRadius: radius * .72 };
    }
 var meta = chart && chart.getDatasetMeta && chart.getDatasetMeta(0), arc = meta && meta.data && meta.data[0]; return arc && (arc._model || arc._view || (arc.getProps && arc.getProps(["x", "y", "innerRadius", "outerRadius"], true))); }
  function fitCenterFont(context, text, maximumWidth, preferredSize) {
    var size = preferredSize;
    while (size > 12) {
      context.font = "700 " + size + "px Tahoma, Arial, sans-serif";
      if (context.measureText(text).width <= maximumWidth) break;
      size -= 1;
    }
    return size;
  }

  function centerPlugin() {
    if (centerPluginReady || !window.Chart) return;
    var service = window.Chart.pluginService || window.Chart.plugins; if (!service || !service.register) return;
    service.register({ id: "hsAnnualCenter",
      beforeDatasetsDraw: function (chart) { var data = chart._hsAnnualCenter, model = arcModel(chart), ctx = contextOf(chart); if (!data || !model || !ctx) return; ctx.save(); ctx.beginPath(); ctx.arc(model.x, model.y, (model.innerRadius + model.outerRadius) / 2, 0, Math.PI * 2); ctx.lineWidth = model.outerRadius - model.innerRadius; ctx.strokeStyle = "#EEF3F6"; ctx.stroke(); ctx.restore(); },
      afterDatasetsDraw: function (chart) { var data = chart._hsAnnualCenter, model = arcModel(chart), ctx = contextOf(chart); if (!data || !model || !ctx) return; var value = compact(data.total) + (data.currency ? " " + (window.hsAnnualCurrency || "Kč") : ""); ctx.save(); ctx.textAlign = "center"; ctx.textBaseline = "middle"; ctx.fillStyle = "#203852"; ctx.font = "700 " + fitCenterFont(ctx, value, model.innerRadius * 1.55, Math.min(22, model.innerRadius * 0.32)) + "px Tahoma, Arial, sans-serif"; ctx.fillText(value, model.x, model.y - 5); ctx.fillStyle = "#8190A2"; ctx.font = "600 " + Math.max(9, Math.min(11, model.innerRadius * 0.16)) + "px Tahoma, Arial, sans-serif"; ctx.fillText(tr(data.caption).toUpperCase(), model.x, model.y + 16); ctx.restore(); }
    }); centerPluginReady = true;
  }
  function chartTitle(canvas) {
    var titles = {canvas:"Historie v čase za jednotlivce",canvas1:"Historie tržeb za služby v čase",canvas2:"Historie prodeje v čase",canvas3:"Počty účtenek za jednotlivá střediska","chart-area1":"Celkem služby","chart-area2":"Celkem prodej","chart-area3":"Účtenky celkem"};
    return titles[canvas.id] || (/^chart-area\d+$/.test(canvas.id) ? "Tržby dle typu platby" : "");
  }
  function isDonut(canvas) { return /^chart-area/.test(canvas.id); }
  function isCurrency(canvas) { return canvas.id !== "chart-area3" && canvas.id !== "canvas3"; }
  function syncLegend(canvas, colors) {
    var panel = canvas.closest(".panel"), markers = panel ? panel.querySelectorAll(".hs-annual-chart-legend__color > div") : [];
    Array.prototype.forEach.call(markers, function (marker, i) { if (colors[i]) marker.style.backgroundColor = colors[i]; });
  }
  function modernizeDonut(chart) {
    if (!chart || chart._hsAnnualDonut) return; chart._hsAnnualDonut = true; centerPlugin();
    var canvas = canvasOf(chart), labels = chart.data.labels || [], currency = isCurrency(canvas); chart._hsAnnualSources = labels.slice();
    chart.data.labels = labels.map(function (label) { return tr(clean(label).replace(/:$/, "")); });
    chart.options = chart.options || {}; chart.options.responsive = true; chart.options.maintainAspectRatio = false; chart.options.cutoutPercentage = 72;
    chart.options.legend = chart.options.legend || {}; chart.options.legend.display = false;
    chart.options.tooltips = chart.options.tooltips || {}; chart.options.tooltips.enabled = true; chart.options.tooltips.backgroundColor = "rgba(32,51,73,.96)"; chart.options.tooltips.titleFontSize = 13; chart.options.tooltips.bodyFontSize = 12; chart.options.tooltips.cornerRadius = 9; chart.options.tooltips.xPadding = 12; chart.options.tooltips.yPadding = 10; chart.options.tooltips.titleFontFamily = "Tahoma, Arial, sans-serif"; chart.options.tooltips.bodyFontFamily = "Tahoma, Arial, sans-serif";
    chart.options.tooltips.callbacks = chart.options.tooltips.callbacks || {};
    chart.options.tooltips.callbacks.title = function () { return ""; };
    chart.options.tooltips.callbacks.label = function (item, data) { var ds = data.datasets[item.datasetIndex || 0] || {}, value = number(ds.data[item.index]), sum = total(ds), label = clean(data.labels[item.index]); return label + ": " + detailed(value, currency) + " (" + (sum ? Math.round(value / sum * 100) : 0) + " %)"; };
    (chart.data.datasets || []).forEach(function (dataset) { var colors = chart._hsAnnualSources.map(color); dataset.backgroundColor = colors; dataset.hoverBackgroundColor = colors; dataset.borderColor = "#fff"; dataset.borderWidth = 4; dataset.hoverBorderColor = "#fff"; dataset.hoverBorderWidth = 7; chart._hsAnnualCenter = { total: total(dataset), currency: currency, caption: currency ? "Celkem" : "Účtenek" }; syncLegend(canvas, colors); });
    if (!hasChartValues(chart)) { chart.options.tooltips.enabled = false; (chart.data.datasets || []).forEach(function (dataset) { dataset.borderWidth = 0; dataset.hoverBorderWidth = 0; }); }
    resizeAndUpdate(chart);
  }
  function modernizeCartesian(chart, canvas) {
    if (!chart || chart._hsAnnualCartesian) return; chart._hsAnnualCartesian = true;
    if (chart._hsAnnualDashboard) {
      // Keep the Dashboard renderer, gradients, axes and mouse/touch tooltip.
      chart.options.legend.display = true;
      chart.options.legend.position = "bottom";
      chart.options.legend.labels.fontFamily = "Tahoma, Arial, sans-serif";
      chart.options.legend.labels.fontSize = 12;
      chart.options.legend.labels.usePointStyle = true;
      chart.options.legend.labels.boxWidth = 10;
      chart.options.hover.mode = "label";
      chart._hsDashboardHideEmptyTooltip = !hasChartValues(chart);
      (chart.data.datasets || []).forEach(function (dataset) { dataset._hsSourceLabel = dataset.label; dataset.label = tr(dataset.label); });
      resizeAndUpdate(chart);
      return;
    }
    if (!(chart.data.datasets || []).length) chart.data.datasets = [{ label: tr(canvas.id === "canvas3" ? "Účtenek" : canvas.id === "canvas2" ? "Prodej" : "Služby"), data: (chart.data.labels || []).map(function () { return 0; }) }];
    chart.options = chart.options || {}; chart.options.responsive = true; chart.options.maintainAspectRatio = false; chart.options.hover = chart.options.hover || {}; chart.options.hover.mode = "label"; chart.options.hover.intersect = false;
    chart.options.legend = chart.options.legend || {}; chart.options.legend.display = true; chart.options.legend.position = "bottom"; chart.options.legend.labels = chart.options.legend.labels || {}; chart.options.legend.labels.fontFamily = "Tahoma, Arial, sans-serif"; chart.options.legend.labels.fontColor = "#50647c"; chart.options.legend.labels.usePointStyle = true; chart.options.legend.labels.padding = 16; chart.options.legend.labels.boxWidth = 10; chart.options.legend.labels.fontSize = 12;
    chart.options.tooltips = chart.options.tooltips || {}; chart.options.tooltips.enabled = true; chart.options.tooltips.mode = "label"; chart.options.tooltips.intersect = false; chart.options.tooltips.backgroundColor = "rgba(32,51,73,.96)"; chart.options.tooltips.titleFontSize = 13; chart.options.tooltips.bodyFontSize = 12; chart.options.tooltips.cornerRadius = 9; chart.options.tooltips.xPadding = 12; chart.options.tooltips.yPadding = 10; chart.options.tooltips.titleFontFamily = "Tahoma, Arial, sans-serif"; chart.options.tooltips.bodyFontFamily = "Tahoma, Arial, sans-serif";
    chart.options.scales = chart.options.scales || {}; ["xAxes", "yAxes"].forEach(function (axisName) { (chart.options.scales[axisName] || []).forEach(function (axis) { axis.gridLines = axis.gridLines || {}; axis.gridLines.color = axisName === "yAxes" ? "rgba(116,134,155,.14)" : "rgba(0,0,0,0)"; axis.ticks = axis.ticks || {}; axis.ticks.fontFamily = "Tahoma, Arial, sans-serif"; axis.ticks.fontColor = "#718399"; axis.ticks.autoSkip = axisName === "xAxes"; axis.ticks.maxRotation = 0; axis.ticks.minRotation = 0; }); });
    (chart.data.datasets || []).forEach(function (dataset, index) { var c = PALETTE[index % PALETTE.length]; dataset.borderColor = c; dataset.backgroundColor = canvas.id === "canvas" ? c : "rgba(23,167,162,.10)"; dataset.pointBackgroundColor = c; dataset.pointBorderColor = "#fff"; dataset.pointRadius = canvas.id !== "canvas" ? 3 : 0; dataset.pointHoverRadius = 6; if (canvas.id === "canvas") { dataset.borderWidth = 0; dataset.borderRadius = 5; dataset.barPercentage = .78; dataset.categoryPercentage = .78; } else { dataset.borderWidth = 2.25; dataset.fill = false; dataset.lineTension = .32; } });
    chart.options.tooltips.callbacks = chart.options.tooltips.callbacks || {};
    chart.options.tooltips.callbacks.label = function (item, data) { return data.datasets[item.datasetIndex].label + ": " + detailed(item.yLabel, isCurrency(canvas)); };
    if (!hasChartValues(chart)) chart.options.tooltips.enabled = false;
    chart._hsAnnualSources = (chart.data.labels || []).slice();
    chart.data.labels = chart._hsAnnualSources.map(tr);
    (chart.data.datasets || []).forEach(function (ds) { ds._hsSourceLabel = ds.label; ds.label = tr(ds.label); });
    resizeAndUpdate(chart);
  }
  function enhanceLegend(panel) {
    Array.prototype.forEach.call(panel.querySelectorAll(".panel-body table:not([id])"), function (table) {
      if (table.dataset.hsAnnualLegend) return; table.dataset.hsAnnualLegend = "true"; table.classList.add("hs-annual-chart-legend");
      Array.prototype.forEach.call(table.rows, function (row) { row.classList.add("hs-annual-chart-legend__item"); if (row.cells[0]) row.cells[0].classList.add("hs-annual-chart-legend__color"); if (row.cells[1]) row.cells[1].classList.add("hs-annual-chart-legend__label"); if (row.cells[2]) row.cells[2].classList.add("hs-annual-chart-legend__value"); });
    });
  }
  function enhanceChart(canvas) {
    var title = chartTitle(canvas), panel = canvas.closest(".panel"), instance = chartInstance(canvas), donut = isDonut(canvas), plot;
    if (!title || !panel) return;
    panel.classList.add("hs-annual-card", "hs-annual-chart-card", donut ? "hs-annual-donut-card" : "hs-annual-cartesian-card");
    enhanceHeading(panel, title, donut ? "Rozdělení za vybraný rok" : (canvas.id === "canvas" ? "Služby a prodej po měsících" : "Přehled tržeb za vybraný rok"), donut ? "donut" : "chart");
    canvas.classList.add("hs-annual-chart-canvas"); canvas.setAttribute("role", "img"); setAttr(canvas, "aria-label", title);
    enhanceLegend(panel);
    plot = canvas.parentElement;
    // The bar canvas is a direct child of panel-body. Give every canvas its own sizing host.
    if (!plot.classList.contains("hs-annual-chart-plot")) {
      plot = el("div", "hs-annual-chart-plot");
      canvas.parentNode.insertBefore(plot, canvas); plot.appendChild(canvas);
    }
    if (donut) {
      if (!((instance && instance.data.labels) || []).length) {
        panel.classList.add("hs-annual-empty-chart");
        var emptyLegend = panel.querySelector(".panel-body table");
        if (emptyLegend && !emptyLegend.rows.length) {
          var legendColumn = emptyLegend.closest('[class*="col-"]');
          if (legendColumn) legendColumn.classList.add("hs-annual-empty-legend");
        }
      }
      var row = canvas.closest(".panel-body .row"), wrapper = plot;
      if (row) {
        while (wrapper.parentElement && wrapper.parentElement !== row) wrapper = wrapper.parentElement;
        wrapper.classList.add("hs-annual-chart-plot-wrap");
      }
    }
    if (!donut && plot && !plot.parentElement.classList.contains("hs-annual-chart-scroll")) { var scroll = el("div", "hs-annual-chart-scroll"); plot.parentNode.insertBefore(scroll, plot); scroll.appendChild(plot); }
    if (donut) modernizeDonut(instance); else modernizeCartesian(instance, canvas);
  }

  function prepareActions(main) {
    var panel = main.querySelector(".hs-annual-options-card");
    if (panel) enhanceHeading(panel, "Správa dat", "Načtení, synchronizace a odstranění zobrazených dat", "settings");
  }
  function translateConfirm(main) {
    Array.prototype.forEach.call(main.querySelectorAll('form'), function (form) {
      if (!form.querySelector('[name="DeleteRokData"]')) return;
      var button = form.querySelector("button");
      if (button) button.onclick = function () { return window.confirm(tr("Opravdu chcete SMAZAT data středisek a jednotlivců pro tento rok?")); };
    });
  }
  function refresh(main) { main.querySelectorAll(".hs-annual-table-row .panel").forEach(function(panel){panel.classList.add("hs-annual-card", "hs-annual-table-card");enhanceHeading(panel,"Roční celkové tržby za obsluhu","Přehled tržeb za vybraný rok","table");}); ensureChartsInitialized(main); Array.prototype.forEach.call(main.querySelectorAll(TABLES), enhanceTable); Array.prototype.forEach.call(main.querySelectorAll("canvas"), enhanceChart); hideLegacyPeriodNav(main); Array.prototype.forEach.call(main.querySelectorAll(".hs-annual-table-row"), function (row) { row.classList.add("hs-annual-table-ready"); }); }
  function runAfterLegacy(main) {
    var picker = document.getElementById("hs-annual-staff");
    if (picker && !picker.dataset.bound) { picker.dataset.bound = "true"; picker.addEventListener("change", function () { var url = new URL(location.href); url.searchParams.set("obsluha", picker.value); url.searchParams.set("NavratovyRok", window.hsAnnualYear); url.searchParams.delete("rok"); location.assign(url.href); }); }
    prepareActions(main); translateConfirm(main); refresh(main);
    var attempts = 0;
    (function poll() { attempts += 1; refresh(main); if (attempts < 18) window.setTimeout(poll, 350); }());
  }
  function initialize() {
    if (!isPage()) return; var main = document.getElementById("main-content"); if (!main) return; main.classList.add("hs-annual-revenue"); var page = main.closest("section.main-content-wrapper"); if (page) page.classList.add("hs-annual-revenue-page");
    if (document.readyState === "complete") window.setTimeout(function () { runAfterLegacy(main); }, 0);
    else window.addEventListener("load", function () { window.setTimeout(function () { runAfterLegacy(main); }, 0); }, { once: true });
  }
  document.addEventListener("hs:languagechange", function () { window.setTimeout(function () { var main = document.getElementById("main-content"); if (!main || !isPage()) return; Array.prototype.forEach.call(main.querySelectorAll("canvas"), function (canvas) { var chart = chartInstance(canvas); if (chart && chart._hsAnnualSources) chart.data.labels = chart._hsAnnualSources.map(function (label) { return tr(clean(label).replace(/:$/, "")); }); if (chart) (chart.data.datasets || []).forEach(function (ds) { if (ds._hsSourceLabel) ds.label = tr(ds._hsSourceLabel); }); if (chart && chart.update) chart.update(0); }); }); });
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initialize); else initialize();
}());
