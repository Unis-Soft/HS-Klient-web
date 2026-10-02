/* HairSoft Klient V227 - lazy PROGRAMS detail with balance and attendance-frequency charts. */
(function () {
  "use strict";

  var pane = document.getElementById("Programy");
  if (!pane) return;

  var requestId = 0;
  var controller = null;
  var loading = false;

  function programsSection() {
    return pane.querySelector(".hs-client-programs");
  }

  function setLoading(active) {
    pane.classList.toggle("hs-programs-is-loading", Boolean(active));
    pane.setAttribute("aria-busy", active ? "true" : "false");
  }

  function showError(message) {
    var section = programsSection();
    if (!section) return;
    section.innerHTML = '' +
      '<div class="hs-programs-panel">' +
        '<div class="hs-programs-panel__heading">' +
          '<div class="hs-programs-panel__heading-copy">' +
            '<span class="hs-programs-eyebrow">Programy zákazníka</span>' +
            '<h3>Programy</h3>' +
          '</div>' +
        '</div>' +
        '<div class="hs-programs-empty hs-programs-empty--error">' +
          '<strong>Programy se nepodařilo načíst.</strong>' +
          '<span>' + String(message || "Zkuste sekci otevřít znovu.") + '</span>' +
        '</div>' +
      '</div>';
  }

  function buildUrl(programId) {
    var url = new URL(window.location.href);
    var guid = pane.getAttribute("data-hs-customer-guid") || "";

    url.searchParams.set("strana", "KartaOsoby");
    if (guid) url.searchParams.set("osoba_guid", guid);
    url.searchParams.delete("NavratGalerie");
    url.searchParams.delete("NavratTimeline");
    url.searchParams.delete("NavratSoubory");
    url.searchParams.set("NavratProgramy", "1");

    if (programId) url.searchParams.set("hs_program_id", String(programId));
    else url.searchParams.delete("hs_program_id");

    return url.toString();
  }

  function replaceFromResponse(html) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(html, "text/html");
    var fresh = doc.querySelector("#Programy .hs-client-programs");
    var current = programsSection();

    if (!fresh || !current) {
      throw new Error("Odpověď neobsahuje sekci Programy.");
    }

    current.replaceWith(document.importNode(fresh, true));
    pane.setAttribute("data-hs-programs-loaded", "1");
    renderProgramsChart();
  }

  function parseDate(dateText) {
    var match = String(dateText || "").trim().match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})(?:\s+(\d{1,2}):(\d{2}))?$/);
    if (!match) return null;

    return new Date(
      parseInt(match[3], 10),
      parseInt(match[2], 10) - 1,
      parseInt(match[1], 10),
      parseInt(match[4] || '0', 10),
      parseInt(match[5] || '0', 10),
      0,
      0
    );
  }

  function parseIntSafe(value) {
    var normalized = String(value == null ? "" : value)
      .replace(/\s+/g, '')
      .replace(/[^0-9\-]/g, '');
    var parsed = parseInt(normalized, 10);
    return isNaN(parsed) ? 0 : parsed;
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatShortDate(date) {
    if (!(date instanceof Date) || isNaN(date.getTime())) return '—';
    var day = date.getDate();
    var month = date.getMonth() + 1;
    return (day < 10 ? '0' + day : String(day)) + '.' + (month < 10 ? '0' + month : String(month)) + '.' + date.getFullYear();
  }

  function niceMax(value) {
    var safeValue = Math.max(1, Number(value) || 1);
    if (safeValue <= 5) return 5;

    var exponent = Math.floor(Math.log(safeValue) / Math.LN10);
    var magnitude = Math.pow(10, exponent);
    var normalized = safeValue / magnitude;
    var niceNormalized = 10;

    if (normalized <= 1) niceNormalized = 1;
    else if (normalized <= 2) niceNormalized = 2;
    else if (normalized <= 5) niceNormalized = 5;

    return niceNormalized * magnitude;
  }

  function readSummary(content) {
    var stats = content.querySelectorAll('.hs-programs-summary .hs-programs-stat strong');
    return {
      prepaid: stats[0] ? parseIntSafe(stats[0].textContent) : 0,
      used: stats[1] ? parseIntSafe(stats[1].textContent) : 0,
      remaining: stats[2] ? parseIntSafe(stats[2].textContent) : 0
    };
  }

  function collectProgramEvents(content) {
    var events = [];
    var paymentRows = content.querySelectorAll('.hs-programs-table--payments tbody tr');
    var visitTables = content.querySelectorAll('.hs-programs-columns .hs-programs-card .hs-programs-table');
    var visitRows = [];
    var i;

    if (visitTables.length) {
      for (i = 0; i < visitTables.length; i += 1) {
        if (!visitTables[i].classList.contains('hs-programs-table--payments')) {
          visitRows = visitTables[i].querySelectorAll('tbody tr');
          break;
        }
      }
    }

    for (i = 0; i < paymentRows.length; i += 1) {
      var paymentCells = paymentRows[i].querySelectorAll('td');
      if (paymentCells.length < 4) continue;
      var paymentDate = parseDate(paymentCells[0].textContent);
      var paymentVisits = parseIntSafe(paymentCells[3].textContent);
      if (!paymentDate || !paymentVisits) continue;
      events.push({
        ts: paymentDate.getTime(),
        date: paymentDate,
        delta: paymentVisits,
        type: 'payment',
        label: 'Předplaceno +' + paymentVisits,
        sortWeight: 0
      });
    }

    for (i = 0; i < visitRows.length; i += 1) {
      var visitCells = visitRows[i].querySelectorAll('td');
      if (visitCells.length < 2) continue;
      var visitDate = parseDate(visitCells[0].textContent);
      var visitQuantity = parseIntSafe(visitCells[1].textContent);
      if (!visitDate || !visitQuantity) continue;
      events.push({
        ts: visitDate.getTime(),
        date: visitDate,
        delta: -visitQuantity,
        type: 'visit',
        label: 'Docházka -' + visitQuantity,
        sortWeight: 1
      });
    }

    events.sort(function (a, b) {
      if (a.ts !== b.ts) return a.ts - b.ts;
      if (a.sortWeight !== b.sortWeight) return a.sortWeight - b.sortWeight;
      return 0;
    });

    return events;
  }

  function collectVisitIntervals(content) {
    var visitTables = content.querySelectorAll('.hs-programs-columns .hs-programs-card .hs-programs-table');
    var visitRows = [];
    var visits = [];
    var intervals = [];
    var i;

    for (i = 0; i < visitTables.length; i += 1) {
      if (!visitTables[i].classList.contains('hs-programs-table--payments')) {
        visitRows = visitTables[i].querySelectorAll('tbody tr');
        break;
      }
    }

    for (i = 0; i < visitRows.length; i += 1) {
      var cells = visitRows[i].querySelectorAll('td');
      if (!cells.length) continue;
      var date = parseDate(cells[0].textContent);
      if (!date) continue;
      visits.push({ ts: date.getTime(), date: date });
    }

    visits.sort(function (a, b) { return a.ts - b.ts; });

    for (i = 1; i < visits.length; i += 1) {
      var diffDays = Math.max(0, Math.round((visits[i].ts - visits[i - 1].ts) / 86400000));
      intervals.push({
        ts: visits[i].ts,
        date: visits[i].date,
        previousDate: visits[i - 1].date,
        days: diffDays
      });
    }

    return { visits: visits, intervals: intervals };
  }

  function formatAverageDays(value) {
    var rounded = Math.round((Number(value) || 0) * 10) / 10;
    return String(rounded).replace('.', ',');
  }

  function chartViewportWidth(content) {
    var width = 760;
    if (content && typeof content.getBoundingClientRect === 'function') {
      width = Math.round((content.getBoundingClientRect().width || 0) - 44);
    }
    if (!Number.isFinite(width) || width <= 0) width = 760;
    return Math.max(420, Math.min(1400, width));
  }

  function buildAttendanceChartSvg(intervals, maxY, chartWidth) {
    var width = Math.max(420, Number(chartWidth) || 760);
    var height = 240;
    var padLeft = 42;
    var padRight = 20;
    var padTop = 16;
    var padBottom = 42;
    var innerWidth = width - padLeft - padRight;
    var innerHeight = height - padTop - padBottom;
    var slotWidth = innerWidth / Math.max(1, intervals.length);
    var barWidth = Math.max(8, Math.min(42, slotWidth * 0.58));
    var gridMarkup = '';
    var barMarkup = '';
    var tickMarkup = '';
    var tickValues = [0, maxY * 0.25, maxY * 0.5, maxY * 0.75, maxY];
    var i;

    function getY(value) {
      return padTop + innerHeight - ((value / maxY) * innerHeight);
    }

    for (i = 0; i < tickValues.length; i += 1) {
      var tickValue = Math.round(tickValues[i]);
      var tickY = getY(tickValue);
      gridMarkup += '' +
        '<line x1="' + padLeft + '" y1="' + tickY.toFixed(2) + '" x2="' + (width - padRight) + '" y2="' + tickY.toFixed(2) + '" class="hs-programs-chart__grid-line"></line>' +
        '<text x="' + (padLeft - 8) + '" y="' + (tickY + 4).toFixed(2) + '" text-anchor="end" class="hs-programs-chart__grid-label">' + tickValue + '</text>';
    }

    for (i = 0; i < intervals.length; i += 1) {
      var interval = intervals[i];
      var xCenter = padLeft + slotWidth * (i + 0.5);
      var y = getY(interval.days);
      var barHeight = Math.max(1, padTop + innerHeight - y);
      var x = xCenter - barWidth / 2;
      var label = formatShortDate(interval.previousDate) + ' → ' + formatShortDate(interval.date) + ': ' + interval.days + ' dní';
      barMarkup += '' +
        '<rect x="' + x.toFixed(2) + '" y="' + y.toFixed(2) + '" width="' + barWidth.toFixed(2) + '" height="' + barHeight.toFixed(2) + '" rx="4" class="hs-programs-attendance-chart__bar">' +
          '<title>' + escapeHtml(label) + '</title>' +
        '</rect>';

      if (intervals.length <= 10) {
        barMarkup += '<text x="' + xCenter.toFixed(2) + '" y="' + Math.max(12, y - 8).toFixed(2) + '" text-anchor="middle" class="hs-programs-chart__point-value">' + interval.days + '</text>';
      }
    }

    var tickIndexes = [0];
    if (intervals.length > 2) tickIndexes.push(Math.floor((intervals.length - 1) / 2));
    if (intervals.length > 1) tickIndexes.push(intervals.length - 1);
    var seen = {};
    for (i = 0; i < tickIndexes.length; i += 1) {
      var idx = tickIndexes[i];
      if (seen[idx]) continue;
      seen[idx] = true;
      var tickX = padLeft + slotWidth * (idx + 0.5);
      tickMarkup += '<text x="' + tickX.toFixed(2) + '" y="' + (height - 12) + '" text-anchor="middle" class="hs-programs-chart__axis-label">' + escapeHtml(formatShortDate(intervals[idx].date)) + '</text>';
    }

    return '' +
      '<svg class="hs-programs-chart__svg" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Počet dní mezi jednotlivými čerpáními programu">' +
        gridMarkup + barMarkup + tickMarkup +
      '</svg>';
  }

  function renderProgramsAttendanceChart(content) {
    var existing = content.querySelector('[data-hs-program-attendance-graph]');
    if (existing && existing.parentNode) existing.parentNode.removeChild(existing);

    var attendance = collectVisitIntervals(content);
    var intervals = attendance.intervals;
    var chartHtml;
    var chartEl;
    var valuesSection;
    var chartWidth = chartViewportWidth(content);

    if (!intervals.length) {
      chartHtml = '' +
        '<section class="hs-programs-chart hs-programs-attendance-chart" data-hs-program-attendance-graph="1">' +
          '<div class="hs-programs-chart__heading">' +
            '<div><span>Frekvence docházky</span><strong>—</strong></div>' +
            '<p>Počet dní mezi jednotlivými čerpáními programu.</p>' +
          '</div>' +
          '<div class="hs-programs-attendance-chart__empty">Pro výpočet frekvence jsou potřeba alespoň 2 čerpání.</div>' +
        '</section>';
    } else {
      var sum = 0;
      var shortest = intervals[0].days;
      var longest = intervals[0].days;
      var maxDays = 0;
      var i;
      for (i = 0; i < intervals.length; i += 1) {
        sum += intervals[i].days;
        if (intervals[i].days < shortest) shortest = intervals[i].days;
        if (intervals[i].days > longest) longest = intervals[i].days;
        if (intervals[i].days > maxDays) maxDays = intervals[i].days;
      }
      var average = sum / intervals.length;
      maxDays = niceMax(Math.max(1, maxDays));

      chartHtml = '' +
        '<section class="hs-programs-chart hs-programs-attendance-chart" data-hs-program-attendance-graph="1">' +
          '<div class="hs-programs-chart__heading">' +
            '<div>' +
              '<span>Frekvence docházky</span>' +
              '<strong>Ø ' + escapeHtml(formatAverageDays(average)) + ' dní</strong>' +
            '</div>' +
            '<p>Graf ukazuje počet dní mezi každými dvěma po sobě jdoucími čerpáními programu.</p>' +
          '</div>' +
          '<div class="hs-programs-chart__legend">' +
            '<div class="hs-programs-chart__legend-item hs-programs-chart__legend-item--accent"><span>Průměr</span><strong>' + escapeHtml(formatAverageDays(average)) + ' dní</strong></div>' +
            '<div class="hs-programs-chart__legend-item"><span>Nejkratší</span><strong>' + shortest + ' dní</strong></div>' +
            '<div class="hs-programs-chart__legend-item"><span>Nejdelší</span><strong>' + longest + ' dní</strong></div>' +
          '</div>' +
          '<div class="hs-programs-chart__canvas">' + buildAttendanceChartSvg(intervals, maxDays, chartWidth) + '</div>' +
          '<div class="hs-programs-chart__meta">' +
            '<span>Od ' + escapeHtml(formatShortDate(attendance.visits[0].date)) + ' do ' + escapeHtml(formatShortDate(attendance.visits[attendance.visits.length - 1].date)) + '</span>' +
            '<span>Intervalů: ' + intervals.length + '</span>' +
          '</div>' +
        '</section>';
    }

    chartEl = document.createElement('div');
    chartEl.innerHTML = chartHtml;
    chartEl = chartEl.firstChild;

    valuesSection = content.querySelector('.hs-programs-values');
    if (valuesSection) content.insertBefore(chartEl, valuesSection);
    else content.appendChild(chartEl);
  }

  function buildChartSvg(points, maxY, chartWidth) {
    var width = Math.max(420, Number(chartWidth) || 760);
    var height = 240;
    var padLeft = 42;
    var padRight = 20;
    var padTop = 16;
    var padBottom = 42;
    var innerWidth = width - padLeft - padRight;
    var innerHeight = height - padTop - padBottom;
    var i;
    var minTs = points[0].ts;
    var maxTs = points[points.length - 1].ts;
    var sameTs = minTs === maxTs;
    var linePoints = [];
    var areaPoints = [];
    var pointMarkup = '';
    var gridMarkup = '';
    var tickValues = [0, maxY * 0.25, maxY * 0.5, maxY * 0.75, maxY];
    var xTicks = [];

    function getX(point, index) {
      if (sameTs) {
        if (points.length === 1) return padLeft + innerWidth / 2;
        return padLeft + (innerWidth * index / (points.length - 1));
      }
      return padLeft + ((point.ts - minTs) / (maxTs - minTs)) * innerWidth;
    }

    function getY(value) {
      return padTop + innerHeight - ((value / maxY) * innerHeight);
    }

    for (i = 0; i < tickValues.length; i += 1) {
      var tickValue = Math.round(tickValues[i]);
      var tickY = getY(tickValue);
      gridMarkup += '' +
        '<line x1="' + padLeft + '" y1="' + tickY.toFixed(2) + '" x2="' + (width - padRight) + '" y2="' + tickY.toFixed(2) + '" class="hs-programs-chart__grid-line"></line>' +
        '<text x="' + (padLeft - 8) + '" y="' + (tickY + 4).toFixed(2) + '" text-anchor="end" class="hs-programs-chart__grid-label">' + tickValue + '</text>';
    }

    for (i = 0; i < points.length; i += 1) {
      var point = points[i];
      var x = getX(point, i);
      var y = getY(point.remaining);
      linePoints.push(x.toFixed(2) + ',' + y.toFixed(2));
      areaPoints.push(x.toFixed(2) + ',' + y.toFixed(2));
      pointMarkup += '' +
        '<circle cx="' + x.toFixed(2) + '" cy="' + y.toFixed(2) + '" r="4.5" class="hs-programs-chart__point hs-programs-chart__point--' + point.type + '"></circle>' +
        '<title>' + escapeHtml(point.label + ' • ' + formatShortDate(point.date) + ' • Zbývá ' + point.remaining) + '</title>';

      if (points.length <= 8) {
        pointMarkup += '<text x="' + x.toFixed(2) + '" y="' + (y - 10).toFixed(2) + '" text-anchor="middle" class="hs-programs-chart__point-value">' + point.remaining + '</text>';
      }
    }

    areaPoints.unshift(padLeft + ',' + (padTop + innerHeight));
    areaPoints.push((width - padRight) + ',' + (padTop + innerHeight));

    xTicks.push({ x: getX(points[0], 0), label: formatShortDate(points[0].date) });
    if (points.length > 2) {
      var midIndex = Math.floor((points.length - 1) / 2);
      xTicks.push({ x: getX(points[midIndex], midIndex), label: formatShortDate(points[midIndex].date) });
    }
    if (points.length > 1) {
      xTicks.push({ x: getX(points[points.length - 1], points.length - 1), label: formatShortDate(points[points.length - 1].date) });
    }

    var tickMarkup = '';
    var lastLabel = '';
    for (i = 0; i < xTicks.length; i += 1) {
      if (xTicks[i].label === lastLabel) continue;
      lastLabel = xTicks[i].label;
      tickMarkup += '<text x="' + xTicks[i].x.toFixed(2) + '" y="' + (height - 12) + '" text-anchor="middle" class="hs-programs-chart__axis-label">' + escapeHtml(xTicks[i].label) + '</text>';
    }

    return '' +
      '<svg class="hs-programs-chart__svg" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Vývoj zůstatku vstupů v čase">' +
        gridMarkup +
        '<path d="M ' + areaPoints.join(' L ') + ' Z" class="hs-programs-chart__area"></path>' +
        '<polyline points="' + linePoints.join(' ') + '" class="hs-programs-chart__line"></polyline>' +
        pointMarkup +
        tickMarkup +
      '</svg>';
  }

  function renderProgramsChart() {
    var section = programsSection();
    var content = section ? section.querySelector('.hs-programs-content') : null;
    var existing;
    var events;
    var points = [];
    var i;
    var balance = 0;
    var summary;
    var maxRemaining;
    var chartHtml;
    var chartEl;
    var valuesSection;
    var chartWidth;

    if (!content) return;
    chartWidth = chartViewportWidth(content);

    existing = content.querySelector('[data-hs-program-graph]');
    if (existing && existing.parentNode) existing.parentNode.removeChild(existing);

    events = collectProgramEvents(content);
    if (!events.length) {
      renderProgramsAttendanceChart(content);
      return;
    }

    for (i = 0; i < events.length; i += 1) {
      balance += events[i].delta;
      points.push({
        ts: events[i].ts,
        date: events[i].date,
        type: events[i].type,
        label: events[i].label,
        remaining: Math.max(0, balance)
      });
    }

    summary = readSummary(content);
    maxRemaining = 0;
    for (i = 0; i < points.length; i += 1) {
      if (points[i].remaining > maxRemaining) maxRemaining = points[i].remaining;
    }
    maxRemaining = niceMax(Math.max(maxRemaining, summary.prepaid, summary.remaining, 1));

    chartHtml = '' +
      '<section class="hs-programs-chart" data-hs-program-graph="1">' +
        '<div class="hs-programs-chart__heading">' +
          '<div>' +
            '<span>Vývoj zůstatku vstupů</span>' +
            '<strong>' + summary.remaining + ' zbývá</strong>' +
          '</div>' +
          '<p>Graf ukazuje, jak se v čase měnil počet zbývajících vstupů podle předplacení a docházky.</p>' +
        '</div>' +
        '<div class="hs-programs-chart__legend">' +
          '<div class="hs-programs-chart__legend-item"><span>Předplaceno</span><strong>' + summary.prepaid + '</strong></div>' +
          '<div class="hs-programs-chart__legend-item"><span>Vyčerpáno</span><strong>' + summary.used + '</strong></div>' +
          '<div class="hs-programs-chart__legend-item hs-programs-chart__legend-item--accent"><span>Zbývá</span><strong>' + summary.remaining + '</strong></div>' +
        '</div>' +
        '<div class="hs-programs-chart__canvas">' + buildChartSvg(points, maxRemaining, chartWidth) + '</div>' +
        '<div class="hs-programs-chart__meta">' +
          '<span>Období: ' + escapeHtml(formatShortDate(points[0].date)) + ' – ' + escapeHtml(formatShortDate(points[points.length - 1].date)) + '</span>' +
          '<span>Událostí: ' + points.length + '</span>' +
        '</div>' +
      '</section>';

    chartEl = document.createElement('div');
    chartEl.innerHTML = chartHtml;
    chartEl = chartEl.firstChild;

    valuesSection = content.querySelector('.hs-programs-values');
    if (valuesSection) content.insertBefore(chartEl, valuesSection);
    else content.appendChild(chartEl);

    renderProgramsAttendanceChart(content);
  }

  function loadPrograms(programId) {
    var localRequest = ++requestId;
    loading = true;

    if (controller && typeof controller.abort === "function") controller.abort();
    controller = typeof AbortController !== "undefined" ? new AbortController() : null;

    setLoading(true);

    return fetch(buildUrl(programId), {
      method: "GET",
      credentials: "same-origin",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      signal: controller ? controller.signal : undefined
    })
      .then(function (response) {
        if (!response.ok) throw new Error("HTTP " + response.status);
        return response.text();
      })
      .then(function (html) {
        if (localRequest !== requestId) return;
        replaceFromResponse(html);
      })
      .catch(function (error) {
        if (error && error.name === "AbortError") return;
        if (localRequest !== requestId) return;
        showError(error && error.message ? error.message : "Neznámá chyba.");
      })
      .finally(function () {
        if (localRequest === requestId) {
          loading = false;
          setLoading(false);
        }
      });
  }

  function ensureLoaded() {
    if (pane.getAttribute("data-hs-programs-loaded") === "1") {
      renderProgramsChart();
      return;
    }
    if (loading) return;
    loadPrograms("");
  }

  pane.addEventListener("change", function (event) {
    var select = event.target.closest ? event.target.closest("[data-hs-program-select]") : null;
    if (!select || !pane.contains(select)) return;
    loadPrograms(select.value || "");
  });

  pane.addEventListener("submit", function (event) {
    var form = event.target.closest ? event.target.closest("[data-hs-programs-picker-form]") : null;
    if (!form || !pane.contains(form)) return;
    event.preventDefault();
    var select = form.querySelector("[data-hs-program-select]");
    loadPrograms(select ? select.value : "");
  });

  if (window.jQuery) {
    window.jQuery(document)
      .off("shown.bs.tab.hsPrograms", 'a[href="#Programy"]')
      .on("shown.bs.tab.hsPrograms", 'a[href="#Programy"]', ensureLoaded);
  }

  if (!window.jQuery) {
    document.addEventListener("click", function (event) {
      var link = event.target.closest ? event.target.closest('a[href="#Programy"]') : null;
      if (!link) return;
      window.setTimeout(ensureLoaded, 0);
    });
  }

  if (pane.classList.contains("active") && pane.getAttribute("data-hs-programs-loaded") !== "1") {
    ensureLoaded();
  } else if (pane.getAttribute("data-hs-programs-loaded") === "1") {
    renderProgramsChart();
  }
}());
