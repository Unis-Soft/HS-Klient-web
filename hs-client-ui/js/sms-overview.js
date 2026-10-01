/* HairSoft Klient V144 – SMS and calls overview refinements. */
(function () {
  "use strict";

  var PALETTE = ["#17A7A2", "#526B91", "#E3A13A", "#887BD5", "#D76083", "#4D98C5", "#67AF7A", "#A95069", "#C58E3C", "#7389A4"];
  var chartColors = Object.create(null);
  var colorIndex = 0;
  var centerPluginReady = false;

  function qs(root, selector) { return root ? root.querySelector(selector) : null; }
  function qsa(root, selector) { return root ? Array.prototype.slice.call(root.querySelectorAll(selector)) : []; }
  function text(el) { return el ? el.textContent.replace(/\s+/g, " ").trim() : ""; }
  function clean(value) { return String(value == null ? "" : value).replace(/\u00a0/g, " ").replace(/\s+/g, " ").trim(); }
  function fold(value) {
    var s = clean(value).toLowerCase();
    return s.normalize ? s.normalize("NFD").replace(/[\u0300-\u036f]/g, "") : s;
  }
  function tr(source) { return window.hsTranslate ? window.hsTranslate(source) : source; }
  function setText(node, source) {
    if (!node) return;
    if (window.hsSetTranslatedText) window.hsSetTranslatedText(node, source);
    else node.textContent = tr(source);
  }
  function setAttr(node, name, source) {
    if (!node) return;
    if (window.hsSetTranslatedAttribute) window.hsSetTranslatedAttribute(node, name, source);
    else node.setAttribute(name, tr(source));
  }

  var icons = {
    branch:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"/></svg>',
    filter:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18M6 12h12M10 19h4"/></svg>',
    trend:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17 6-6 4 4 8-9"/><path d="M15 6h6v6"/></svg>',
    pie:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-9-9v9z"/><path d="M12 3a9 9 0 0 1 9 9h-9z"/></svg>',
    wallet:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7V6a2 2 0 0 0-2-2H5a3 3 0 0 0 0 6h15v10H5a3 3 0 0 1-3-3V7"/><path d="M16 14h.01"/></svg>',
    send:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/></svg>',
    phone:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z"/></svg>',
    message:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>',
    plus:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
    export:'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 20h14"/></svg>'
  };

  function addHeadingIcon(panel, icon) {
    var heading = qs(panel, ".panel-heading");
    var title = qs(heading, ".panel-title");
    if (!heading || !title || qs(heading, ".hs-sms-heading-icon")) return;
    var span = document.createElement("span");
    span.className = "hs-sms-heading-icon";
    span.innerHTML = icons[icon] || icons.message;
    heading.insertBefore(span, title);
  }

  function classifyPanels(page) {
    qsa(page, ".panel").forEach(function (panel) {
      var title = text(qs(panel, ".panel-title"));
      if (panel.classList.contains("hs-sms-branch-card") || title.indexOf("Výběr pobočky") === 0) addHeadingIcon(panel, "branch");
      else if (panel.classList.contains("hs-sms-count-card") || title.indexOf("Odeslaných SMS filtr") === 0 || title.indexOf("Filtr odeslaných SMS") === 0) addHeadingIcon(panel, "filter");
      else if (panel.classList.contains("hs-sms-line-card") || title.indexOf("Dle typu SMS") === 0) addHeadingIcon(panel, "trend");
      else if (panel.classList.contains("hs-sms-type-card") || title.indexOf("Zprávy dle typu") === 0) addHeadingIcon(panel, "pie");
      else if (panel.classList.contains("hs-sms-credit-card") || title.indexOf("Přehled dobití kreditu") === 0) addHeadingIcon(panel, "wallet");
      else if (panel.classList.contains("hs-sms-outgoing-card") || title.indexOf("Přehled odchozích zpráv") === 0) addHeadingIcon(panel, "send");
      else if (panel.classList.contains("hs-sms-incoming-card") || title.indexOf("Přehled příchozích hovorů a SMS") === 0) addHeadingIcon(panel, "phone");
    });
  }

  function classifyRows(page) {
    qsa(page, ":scope > .row").forEach(function (row) {
      if (qs(row, ".hs-sms-branch-card") && qs(row, ".hs-sms-count-card")) row.classList.add("hs-sms-filter-row");
      if (qs(row, ".hs-sms-line-card") && qs(row, ".hs-sms-type-card")) row.classList.add("hs-sms-charts");
      if (qs(row, ".hs-sms-credit-card") || qs(row, ".hs-sms-outgoing-card") || qs(row, ".hs-sms-incoming-card")) row.classList.add("hs-sms-details-grid");
    });
    qsa(page, ".hs-sms-incoming-card").forEach(function (panel) {
      var col = panel.parentElement;
      if (col && /col-/.test(col.className)) col.classList.add("hs-sms-incoming-col");
    });
  }

  function enhanceForms(page) {
    var branch = qs(page, "#VybranaPobockaID");
    if (branch && branch.form) branch.addEventListener("change", function () { branch.form.submit(); });
    var result = qs(page, ".hs-sms-filter-result[data-hs-sms-count]");
    if (result) updateFilterResult(result);
  }

  function updateFilterResult(result) {
    var label = qs(result, ".hs-sms-filter-result__label");
    var value = qs(result, ".hs-sms-filter-result__value");
    if (label) label.textContent = tr("Počet odeslaných SMS");
    if (value) value.textContent = String(result.dataset.hsSmsCount || "0") + " SMS";
  }

  function enhanceSummaryTables(page) {
    qsa(page, ".hs-sms-outgoing-card table, .hs-sms-incoming-card table").forEach(function (table) {
      table.classList.add("hs-sms-summary-table");
      var rows = qsa(table, "tbody tr");
      rows.forEach(function (row) {
        var cells = qsa(row, "td");
        if (cells.length === 1 && !text(cells[0])) row.classList.add("hs-sms-separator");
      });
      if (table.closest(".hs-sms-outgoing-card")) rows.slice(-2).forEach(function (row) { row.classList.add("hs-sms-total"); });
    });
  }

  function buildCreditMobileCards(page) {
    var table = qs(page, "#example");
    if (!table || table.dataset.hsSmsCards === "1") return;
    table.dataset.hsSmsCards = "1";
    table.classList.add("hs-sms-desktop-table");
    var sourceHeaders = ["#", "Datum a čas", "Částka", "Výše kreditu"];
    var list = document.createElement("div");
    list.className = "hs-sms-mobile-list";
    table.parentNode.insertBefore(list, table.nextSibling);

    function renderRows(rows) {
      list.textContent = "";
      rows.forEach(function (row) {
        var cells = qsa(row, "td");
        if (!cells.length) return;
        var card = document.createElement("article");
        card.className = "hs-sms-mobile-card";
        var title = document.createElement("div");
        title.className = "hs-sms-mobile-card__title";
        title.textContent = tr(sourceHeaders[1]) + ": " + (text(cells[1]) || "—");
        card.appendChild(title);
        var grid = document.createElement("div");
        grid.className = "hs-sms-mobile-card__grid";
        cells.forEach(function (cell, i) {
          if (i === 1) return;
          var item = document.createElement("div");
          item.className = "hs-sms-mobile-card__item";
          var label = document.createElement("span");
          label.textContent = tr(sourceHeaders[i] || "");
          var value = document.createElement("strong");
          value.textContent = text(cell) || "—";
          item.appendChild(label);
          item.appendChild(value);
          grid.appendChild(item);
        });
        card.appendChild(grid);
        list.appendChild(card);
      });
    }

    var jq = window.jQuery;
    if (jq && jq.fn && jq.fn.dataTable && jq.fn.dataTable.isDataTable(table)) {
      var dt = jq(table).DataTable();
      var redraw = function () { renderRows(dt.rows({ search:"applied", order:"applied", page:"current" }).nodes().toArray()); };
      dt.off("draw.hsSmsCards").on("draw.hsSmsCards", redraw);
      redraw();
    } else renderRows(qsa(table, "tbody tr"));
  }

  function enhanceCreditDataTable(page) {
    var panel = qs(page, ".hs-sms-credit-card");
    var table = qs(panel, "#example");
    if (!panel || !table) return;
    var wrapper = table.closest(".dataTables_wrapper");
    if (!wrapper) return;
    wrapper.classList.add("hs-sms-credit-table-wrap");
    /* V144: legacy index.php adds table-responsive to the whole DataTables wrapper.
       That made the horizontal scrollbar run underneath the pagination. */
    wrapper.classList.remove("table-responsive");
    var wrapperParent = wrapper.parentElement;
    if (wrapperParent && wrapperParent.classList.contains("table-responsive")) wrapperParent.classList.remove("table-responsive");
    var filter = qs(wrapper, ".dataTables_filter");
    if (filter) filter.remove();
    var buttons = qs(wrapper, ".dt-buttons");
    if (buttons) {
      buttons.classList.add("hs-sms-credit-toolbar");
      var button = qs(buttons, ".buttons-collection");
      if (button) {
        button.dataset.hsSmsExport = "1";
        button.innerHTML = icons.export + '<span></span>';
        setText(qs(button, "span"), "Export");
      }
      wrapper.insertBefore(buttons, wrapper.firstChild);
    }
    var info = qs(wrapper, ".dataTables_info");
    var paginate = qs(wrapper, ".dataTables_paginate");
    if (info) info.classList.add("hs-sms-credit-info");
    if (paginate) paginate.classList.add("hs-sms-credit-pagination");
  }

  function enhanceTopup(page) {
    var link = qs(page, 'a[href*="dobiti-kreditu-sms"]');
    if (!link) return;
    link.className = "hs-sms-topup";
    link.setAttribute("rel", "noopener");
    link.innerHTML = icons.plus + '<span class="hs-sms-topup-label"></span>';
    updateTopupLabel(page);
  }

  function updateTopupLabel(page) {
    setText(qs(page, ".hs-sms-topup-label"), "Dobít kredit");
  }

  function number(value) {
    var parsed = Number(String(value == null ? "" : value).replace(/\s/g, "").replace(",", "."));
    return isFinite(parsed) ? parsed : 0;
  }
  function total(values) { return (values || []).reduce(function (sum, value) { return sum + number(value); }, 0); }
  function compact(value) {
    if (Math.abs(value) >= 1000000) return (Math.round(value / 100000) / 10).toString().replace(".", ",") + " mil.";
    return Math.round(value).toLocaleString("cs-CZ");
  }
  function hasValues(values) { return (values || []).some(function (value) { return number(value) !== 0; }); }
  function color(label) {
    var key = fold(label) || "item-" + colorIndex;
    if (!chartColors[key]) { chartColors[key] = PALETTE[colorIndex % PALETTE.length]; colorIndex += 1; }
    return chartColors[key];
  }

  function chartInstance(canvas) {
    if (!window.Chart || !window.Chart.instances) return null;
    var instances = window.Chart.instances;
    for (var key in instances) if (Object.prototype.hasOwnProperty.call(instances, key)) {
      var instance = instances[key];
      if (instance && (instance.canvas === canvas || (instance.chart && instance.chart.canvas === canvas))) return instance;
    }
    return null;
  }

  function contextOf(chart) { return chart && (chart.ctx || (chart.chart && chart.chart.ctx)); }
  function arcModel(chart) {
    var meta = chart && chart.getDatasetMeta && chart.getDatasetMeta(0);
    var arc = meta && meta.data && meta.data[0];
    if (arc) return arc._model || arc._view || (arc.getProps && arc.getProps(["x", "y", "innerRadius", "outerRadius"], true));
    var area = chart && (chart.chartArea || (chart.chart && { left:0, top:0, right:chart.chart.width, bottom:chart.chart.height }));
    if (!area) return null;
    var radius = Math.max(0, Math.min(area.right - area.left, area.bottom - area.top) / 2 - 8);
    return { x:(area.left + area.right) / 2, y:(area.top + area.bottom) / 2, outerRadius:radius, innerRadius:radius * .72 };
  }
  function fitCenterFont(context, value, maxWidth, preferred) {
    var size = preferred;
    while (size > 12) {
      context.font = "700 " + size + "px Tahoma, Arial, sans-serif";
      if (context.measureText(value).width <= maxWidth) break;
      size -= 1;
    }
    return size;
  }
  function centerPlugin() {
    if (centerPluginReady || !window.Chart) return;
    var service = window.Chart.pluginService || window.Chart.plugins;
    if (!service || !service.register) return;
    service.register({
      id:"hsSmsCenter",
      beforeDatasetsDraw:function (chart) {
        var data = chart._hsSmsCenter, model = arcModel(chart), ctx = contextOf(chart);
        if (!data || !model || !ctx) return;
        ctx.save();
        ctx.beginPath();
        ctx.arc(model.x, model.y, (model.innerRadius + model.outerRadius) / 2, 0, Math.PI * 2);
        ctx.lineWidth = model.outerRadius - model.innerRadius;
        ctx.strokeStyle = "#EEF3F6";
        ctx.stroke();
        ctx.restore();
      },
      afterDatasetsDraw:function (chart) {
        var data = chart._hsSmsCenter, model = arcModel(chart), ctx = contextOf(chart);
        if (!data || !model || !ctx) return;
        var value = compact(data.total);
        ctx.save();
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#203852";
        ctx.font = "700 " + fitCenterFont(ctx, value, model.innerRadius * 1.55, Math.min(22, model.innerRadius * .32)) + "px Tahoma, Arial, sans-serif";
        ctx.fillText(value, model.x, model.y - 5);
        ctx.fillStyle = "#8190A2";
        ctx.font = "600 " + Math.max(9, Math.min(11, model.innerRadius * .16)) + "px Tahoma, Arial, sans-serif";
        ctx.fillText(tr("Celkem SMS").toUpperCase(), model.x, model.y + 16);
        ctx.restore();
      }
    });
    centerPluginReady = true;
  }

  function prepareChartLayout(page) {
    var linePanel = qs(page, ".hs-sms-line-card");
    var typePanel = qs(page, ".hs-sms-type-card");
    if (linePanel) {
      linePanel.classList.add("hs-sms-chart-card", "hs-sms-cartesian-card");
      var lineCanvas = qs(linePanel, "#canvas1");
      if (lineCanvas && !lineCanvas.closest(".hs-sms-chart-plot")) {
        var lineHost = document.createElement("div");
        lineHost.className = "hs-sms-chart-plot";
        lineCanvas.parentNode.insertBefore(lineHost, lineCanvas);
        lineHost.appendChild(lineCanvas);
        var scroll = document.createElement("div");
        scroll.className = "hs-sms-chart-scroll";
        lineHost.parentNode.insertBefore(scroll, lineHost);
        scroll.appendChild(lineHost);
      }
    }
    if (typePanel) {
      typePanel.classList.add("hs-sms-chart-card", "hs-sms-donut-card");
      var legend = qs(typePanel, ".panel-body table");
      if (legend) {
        legend.classList.add("hs-sms-chart-legend");
        qsa(legend, "tr").forEach(function (row) {
          if (row.cells.length < 3) return;
          row.classList.add("hs-sms-chart-legend__item");
          row.cells[0].classList.add("hs-sms-chart-legend__color");
          row.cells[1].classList.add("hs-sms-chart-legend__label");
          row.cells[2].classList.add("hs-sms-chart-legend__value");
        });
      }
      var pieCanvas = qs(typePanel, "#chart-area1");
      if (pieCanvas && !pieCanvas.closest(".hs-sms-chart-plot")) {
        var pieHost = document.createElement("div");
        pieHost.className = "hs-sms-chart-plot";
        pieCanvas.parentNode.insertBefore(pieHost, pieCanvas);
        pieHost.appendChild(pieCanvas);
      }
    }
  }

  function showEmpty(canvas, sourceText) {
    if (!canvas || !canvas.parentNode) return;
    canvas.style.display = "none";
    var empty = document.createElement("div");
    empty.className = "hs-sms-empty";
    empty.dataset.hsSource = sourceText;
    setText(empty, sourceText);
    canvas.parentNode.appendChild(empty);
  }

  function renderCharts(page) {
    if (!window.Chart || !window.hsSmsChartData) return;
    prepareChartLayout(page);
    var payload = window.hsSmsChartData;
    var lineCanvas = qs(page, "#canvas1");
    var pieCanvas = qs(page, "#chart-area1");

    if (lineCanvas && payload.line && payload.line.datasets && !chartInstance(lineCanvas)) {
      var hasLineData = payload.line.datasets.some(function (dataset) { return hasValues(dataset.data); });
      if (!hasLineData) showEmpty(lineCanvas, "Nejsou žádná data k zobrazení");
      else {
        payload.line._hsSourceLabels = (payload.line.labels || []).slice();
        payload.line.labels = payload.line._hsSourceLabels.map(tr);
        payload.line.datasets.forEach(function (dataset, index) {
          dataset._hsSourceLabel = dataset.label;
          dataset.label = tr(dataset.label);
          var c = PALETTE[index % PALETTE.length];
          dataset.borderColor = c;
          dataset.backgroundColor = "rgba(23,167,162,.10)";
          dataset.pointBackgroundColor = c;
          dataset.pointBorderColor = "#fff";
          dataset.pointBorderWidth = 1.5;
          dataset.borderWidth = 2.25;
          dataset.pointRadius = 3;
          dataset.pointHoverRadius = 6;
          dataset.fill = false;
          dataset.lineTension = .32;
        });
        window.hsSmsLineChart = new window.Chart(lineCanvas.getContext("2d"), {
          type:"line",
          data:payload.line,
          options:{
            responsive:true,
            maintainAspectRatio:false,
            hover:{mode:"label",intersect:false},
            legend:{display:true,position:"bottom",labels:{fontFamily:"Tahoma, Arial, sans-serif",fontColor:"#50647c",usePointStyle:true,padding:16,boxWidth:10,fontSize:12}},
            tooltips:{enabled:true,mode:"label",intersect:false,backgroundColor:"rgba(32,51,73,.96)",titleFontSize:13,bodyFontSize:12,cornerRadius:9,xPadding:12,yPadding:10,titleFontFamily:"Tahoma, Arial, sans-serif",bodyFontFamily:"Tahoma, Arial, sans-serif"},
            scales:{
              xAxes:[{gridLines:{color:"rgba(0,0,0,0)"},ticks:{fontFamily:"Tahoma, Arial, sans-serif",fontColor:"#718399",autoSkip:false,maxRotation:0,minRotation:0}}],
              yAxes:[{gridLines:{color:"rgba(116,134,155,.14)",drawBorder:false},ticks:{beginAtZero:true,fontFamily:"Tahoma, Arial, sans-serif",fontColor:"#718399",precision:0}}]
            }
          }
        });
      }
    }

    if (pieCanvas && payload.doughnut && !chartInstance(pieCanvas)) {
      var values = payload.doughnut.data || [];
      if (!hasValues(values)) showEmpty(pieCanvas, "Nejsou žádná data k zobrazení");
      else {
        centerPlugin();
        payload.doughnut._hsSourceLabels = (payload.doughnut.labels || []).slice();
        payload.doughnut.labels = payload.doughnut._hsSourceLabels.map(tr);
        var colors = payload.doughnut._hsSourceLabels.map(color);
        var legend = qs(page, ".hs-sms-type-card .hs-sms-chart-legend");
        if (legend) qsa(legend, ".hs-sms-chart-legend__item").forEach(function (row) {
          var labelCell = row.cells && row.cells[1], marker = qs(row, ".hs-sms-chart-legend__color > div");
          if (!labelCell || !marker) return;
          var visible = fold(text(labelCell));
          var source = payload.doughnut._hsSourceLabels.find(function (item) { return fold(tr(item)) === visible || fold(item) === visible; });
          marker.style.backgroundColor = color(source || text(labelCell));
        });
        window.hsSmsTypeChart = new window.Chart(pieCanvas.getContext("2d"), {
          type:"doughnut",
          data:{labels:payload.doughnut.labels,datasets:[{data:values,backgroundColor:colors,hoverBackgroundColor:colors,borderColor:"#fff",borderWidth:4,hoverBorderColor:"#fff",hoverBorderWidth:7}]},
          options:{
            responsive:true,
            maintainAspectRatio:false,
            cutoutPercentage:72,
            legend:{display:false},
            animation:{duration:0},
            tooltips:{enabled:true,backgroundColor:"rgba(32,51,73,.96)",titleFontSize:13,bodyFontSize:12,cornerRadius:9,xPadding:12,yPadding:10,titleFontFamily:"Tahoma, Arial, sans-serif",bodyFontFamily:"Tahoma, Arial, sans-serif",callbacks:{title:function(){return "";},label:function(item,data){var ds=data.datasets[item.datasetIndex||0]||{},v=number(ds.data[item.index]),sum=total(ds.data),label=clean(data.labels[item.index]);return label+": "+Math.round(v).toLocaleString("cs-CZ")+" ("+(sum?Math.round(v/sum*100):0)+" %)";}}}
          }
        });
        window.hsSmsTypeChart._hsSmsCenter = { total:total(values) };
        window.hsSmsTypeChart.update(0);
      }
    }
  }

  function updateLanguage(page) {
    updateTopupLabel(page);
    var result = qs(page, ".hs-sms-filter-result[data-hs-sms-count]");
    if (result) updateFilterResult(result);
    var exportButton = qs(page, ".hs-sms-credit-card .buttons-collection[data-hs-sms-export]");
    if (exportButton) setText(qs(exportButton, "span"), "Export");
    qsa(page, ".hs-sms-empty").forEach(function (empty) { setText(empty, empty.dataset.hsSource || "Nejsou žádná data k zobrazení"); });
    if (window.hsSmsLineChart && window.hsSmsChartData && window.hsSmsChartData.line) {
      var line = window.hsSmsChartData.line;
      if (line._hsSourceLabels) window.hsSmsLineChart.data.labels = line._hsSourceLabels.map(tr);
      window.hsSmsLineChart.data.datasets.forEach(function (dataset) { if (dataset._hsSourceLabel) dataset.label = tr(dataset._hsSourceLabel); });
      window.hsSmsLineChart.update(0);
    }
    if (window.hsSmsTypeChart && window.hsSmsChartData && window.hsSmsChartData.doughnut) {
      var donut = window.hsSmsChartData.doughnut;
      if (donut._hsSourceLabels) window.hsSmsTypeChart.data.labels = donut._hsSourceLabels.map(tr);
      window.hsSmsTypeChart.update(0);
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var page = document.querySelector("#main-content.hs-sms-page");
    if (!page) return;
    classifyPanels(page);
    classifyRows(page);
    enhanceForms(page);
    enhanceSummaryTables(page);
    enhanceTopup(page);
    enhanceCreditDataTable(page);
    buildCreditMobileCards(page);
    renderCharts(page);
    page.classList.add("hs-sms-ready");
    document.addEventListener("hs:languagechange", function () { updateLanguage(page); });
  });
}());
