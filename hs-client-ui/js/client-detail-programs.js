/* HairSoft Klient V244 - PROGRAMS detail, immediate consume refresh and fast local photo previews. */
(function () {
  "use strict";

  var pane = document.getElementById("Programy");
  if (!pane) return;

  var requestId = 0;
  var controller = null;
  var loading = false;

  function t(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

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
            '<span class="hs-programs-eyebrow">' + escapeHtml(t('Programy zákazníka')) + '</span>' +
            '<h3>' + escapeHtml(t('Programy')) + '</h3>' +
          '</div>' +
        '</div>' +
        '<div class="hs-programs-empty hs-programs-empty--error">' +
          '<strong>' + escapeHtml(t('Programy se nepodařilo načíst.')) + '</strong>' +
          '<span>' + escapeHtml(t(String(message || "Zkuste sekci otevřít znovu."))) + '</span>' +
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
    refreshVisiblePhotoTotal();
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
        label: t('Předplaceno') + ' +' + paymentVisits,
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
        label: t('Docházka') + ' -' + visitQuantity,
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

  function chartDayKey(date) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) return "";
    return [
      date.getFullYear(),
      String(date.getMonth() + 1).padStart(2, "0"),
      String(date.getDate()).padStart(2, "0")
    ].join("-");
  }

  function chartViewportWidth(content) {
    var width = 760;
    if (content && typeof content.getBoundingClientRect === 'function') {
      width = Math.round((content.getBoundingClientRect().width || 0) - 44);
    }
    if (!Number.isFinite(width) || width <= 0) width = 760;
    return Math.max(420, Math.min(1400, width));
  }

  function chartScrollableWidth(viewportWidth, itemCount, slotWidth) {
    var viewport = Math.max(420, Number(viewportWidth) || 760);
    var count = Math.max(1, Number(itemCount) || 1);
    var slot = Math.max(70, Number(slotWidth) || 92);
    // V241: graf je záměrně širší než viewport i na PC, aby byl vždy vodorovně posuvný.
    return Math.min(5200, Math.max(viewport + 260, 860, 82 + (count * slot)));
  }

  function applyProgramTableScroll(content) {
    if (!content) return;
    var wraps = content.querySelectorAll('[data-hs-program-table-limit]');
    for (var w = 0; w < wraps.length; w += 1) {
      var wrap = wraps[w];
      var table = wrap.querySelector('.hs-programs-table');
      var rows = table ? table.querySelectorAll('tbody tr') : [];
      var limit = parseInt(wrap.getAttribute('data-hs-program-table-limit') || '10', 10);
      if (!Number.isFinite(limit) || limit < 1) limit = 10;
      wrap.classList.remove('hs-programs-table-wrap--limited');
      wrap.style.maxHeight = '';
      if (!table || rows.length <= limit) continue;

      var height = 0;
      var thead = table.querySelector('thead');
      if (thead && typeof thead.getBoundingClientRect === 'function') height += thead.getBoundingClientRect().height;
      for (var i = 0; i < limit && i < rows.length; i += 1) {
        if (typeof rows[i].getBoundingClientRect === 'function') height += rows[i].getBoundingClientRect().height;
      }
      if (!Number.isFinite(height) || height <= 0) height = 410;
      wrap.style.maxHeight = Math.ceil(height + 2) + 'px';
      wrap.classList.add('hs-programs-table-wrap--limited');
    }
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
    var minY = 0;
    var rangeY = Math.max(1, maxY - minY);
    var tickValues = [minY, minY + rangeY * 0.25, minY + rangeY * 0.5, minY + rangeY * 0.75, maxY];
    var i;

    function getY(value) {
      return padTop + innerHeight - (((value - minY) / rangeY) * innerHeight);
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
      var label = formatShortDate(interval.previousDate) + ' → ' + formatShortDate(interval.date) + ': ' + interval.days + ' ' + t('dní');
      barMarkup += '' +
        '<rect x="' + x.toFixed(2) + '" y="' + y.toFixed(2) + '" width="' + barWidth.toFixed(2) + '" height="' + barHeight.toFixed(2) + '" rx="4" class="hs-programs-attendance-chart__bar">' +
          '<title>' + escapeHtml(label) + '</title>' +
        '</rect>';

      if (intervals.length <= 10) {
        barMarkup += '<text x="' + xCenter.toFixed(2) + '" y="' + Math.max(12, y - 8).toFixed(2) + '" text-anchor="middle" class="hs-programs-chart__point-value">' + interval.days + '</text>';
      }
    }

    var tickIndexes = [];
    var step;
    if (intervals.length <= 10) {
      for (i = 0; i < intervals.length; i += 1) tickIndexes.push(i);
    } else {
      step = Math.ceil(intervals.length / 8);
      for (i = 0; i < intervals.length; i += step) tickIndexes.push(i);
      if (tickIndexes[tickIndexes.length - 1] !== intervals.length - 1) tickIndexes.push(intervals.length - 1);
    }

    var seen = {};
    for (i = 0; i < tickIndexes.length; i += 1) {
      var idx = tickIndexes[i];
      if (seen[idx]) continue;
      seen[idx] = true;
      var tickX = padLeft + slotWidth * (idx + 0.5);
      tickMarkup += '<text x="' + tickX.toFixed(2) + '" y="' + (height - 12) + '" text-anchor="middle" class="hs-programs-chart__axis-label">' + escapeHtml(formatShortDate(intervals[idx].date)) + '</text>';
    }

    return '' +
      '<svg class="hs-programs-chart__svg" style="width:' + width + 'px;min-width:' + width + 'px;max-width:none" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="xMidYMid meet" role="img" aria-label="' + escapeHtml(t('Počet dní mezi jednotlivými čerpáními programu.')) + '">' +
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
    var chartWidth = chartScrollableWidth(chartViewportWidth(content), Math.max(1, intervals.length), 104);

    if (!intervals.length) {
      chartHtml = '' +
        '<section class="hs-programs-chart hs-programs-attendance-chart" data-hs-program-attendance-graph="1">' +
          '<div class="hs-programs-chart__heading">' +
            '<div><span>' + escapeHtml(t('Frekvence docházky')) + '</span><strong>—</strong></div>' +
            '<p>' + escapeHtml(t('Počet dní mezi jednotlivými čerpáními programu.')) + '</p>' +
          '</div>' +
          '<div class="hs-programs-attendance-chart__empty">' + escapeHtml(t('Pro výpočet frekvence jsou potřeba alespoň 2 čerpání.')) + '</div>' +
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
              '<span>' + escapeHtml(t('Frekvence docházky')) + '</span>' +
              '<strong>Ø ' + escapeHtml(formatAverageDays(average)) + ' ' + escapeHtml(t('dní')) + '</strong>' +
            '</div>' +
            '<p>' + escapeHtml(t('Graf ukazuje počet dní mezi každými dvěma po sobě jdoucími čerpáními programu.')) + '</p>' +
          '</div>' +
          '<div class="hs-programs-chart__legend">' +
            '<div class="hs-programs-chart__legend-item hs-programs-chart__legend-item--accent"><span>' + escapeHtml(t('Průměr')) + '</span><strong>' + escapeHtml(formatAverageDays(average)) + ' ' + escapeHtml(t('dní')) + '</strong></div>' +
            '<div class="hs-programs-chart__legend-item"><span>' + escapeHtml(t('Nejkratší')) + '</span><strong>' + shortest + ' ' + escapeHtml(t('dní')) + '</strong></div>' +
            '<div class="hs-programs-chart__legend-item"><span>' + escapeHtml(t('Nejdelší')) + '</span><strong>' + longest + ' ' + escapeHtml(t('dní')) + '</strong></div>' +
          '</div>' +
          '<div class="hs-programs-chart__canvas">' + buildAttendanceChartSvg(intervals, maxDays, chartWidth) + '</div>' +
          '<div class="hs-programs-chart__meta">' +
            '<span>' + escapeHtml(t('Od')) + ' ' + escapeHtml(formatShortDate(attendance.visits[0].date)) + ' ' + escapeHtml(t('do')) + ' ' + escapeHtml(formatShortDate(attendance.visits[attendance.visits.length - 1].date)) + '</span>' +
            '<span>' + escapeHtml(t('Intervalů')) + ': ' + intervals.length + '</span>' +
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

  function buildChartSvg(points, minY, maxY, chartWidth) {
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
    var rangeY = Math.max(1, maxY - minY);
    var tickValues = [minY, minY + rangeY * 0.25, minY + rangeY * 0.5, minY + rangeY * 0.75, maxY];
    var xTicks = [];

    function getX(point, index) {
      if (sameTs) {
        if (points.length === 1) return padLeft + innerWidth / 2;
        return padLeft + (innerWidth * index / (points.length - 1));
      }
      return padLeft + ((point.ts - minTs) / (maxTs - minTs)) * innerWidth;
    }

    function getY(value) {
      return padTop + innerHeight - (((value - minY) / rangeY) * innerHeight);
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
        '<title>' + escapeHtml(point.label + ' • ' + formatShortDate(point.date) + ' • ' + t('Zbývá') + ' ' + point.remaining) + '</title>';

      if (points.length <= 8) {
        var valueX = x;
        var valueAnchor = 'middle';
        if (i === 0) {
          valueX = x + 9;
          valueAnchor = 'start';
        } else if (i === points.length - 1) {
          valueX = x - 9;
          valueAnchor = 'end';
        }
        pointMarkup += '<text x="' + valueX.toFixed(2) + '" y="' + (y - 10).toFixed(2) + '" text-anchor="' + valueAnchor + '" class="hs-programs-chart__point-value">' + point.remaining + '</text>';
      }
    }

    var baselineY = getY(0);
    areaPoints.unshift(padLeft + ',' + baselineY.toFixed(2));
    areaPoints.push((width - padRight) + ',' + baselineY.toFixed(2));

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
      var axisAnchor = 'middle';
      if (i === 0) axisAnchor = 'start';
      else if (i === xTicks.length - 1) axisAnchor = 'end';
      tickMarkup += '<text x="' + xTicks[i].x.toFixed(2) + '" y="' + (height - 12) + '" text-anchor="' + axisAnchor + '" class="hs-programs-chart__axis-label">' + escapeHtml(xTicks[i].label) + '</text>';
    }

    return '' +
      '<svg class="hs-programs-chart__svg" style="width:' + width + 'px;min-width:' + width + 'px;max-width:none" viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="xMidYMid meet" role="img" aria-label="' + escapeHtml(t('Vývoj zůstatku vstupů v čase')) + '">' +
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
    var minRemaining;
    var maxRemaining;
    var chartHtml;
    var chartEl;
    var valuesSection;
    var chartWidth;

    if (!content) return;
    applyProgramTableScroll(content);
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
      var point = {
        ts: events[i].ts,
        date: events[i].date,
        type: events[i].type,
        label: events[i].label,
        remaining: balance,
        dayKey: chartDayKey(events[i].date)
      };

      if (points.length && point.dayKey && points[points.length - 1].dayKey === point.dayKey) {
        points[points.length - 1].ts = point.ts;
        points[points.length - 1].date = point.date;
        points[points.length - 1].type = point.type;
        points[points.length - 1].label += ' / ' + point.label;
        points[points.length - 1].remaining = point.remaining;
      } else {
        points.push(point);
      }
    }

    chartWidth = chartScrollableWidth(chartWidth, Math.max(1, points.length), 104);

    summary = readSummary(content);
    minRemaining = Math.min(0, summary.remaining);
    maxRemaining = Math.max(0, summary.prepaid, summary.remaining);
    for (i = 0; i < points.length; i += 1) {
      if (points[i].remaining < minRemaining) minRemaining = points[i].remaining;
      if (points[i].remaining > maxRemaining) maxRemaining = points[i].remaining;
    }
    minRemaining = minRemaining < 0 ? -niceMax(Math.abs(minRemaining)) : 0;
    maxRemaining = niceMax(Math.max(maxRemaining, 1));

    chartHtml = '' +
      '<section class="hs-programs-chart" data-hs-program-graph="1">' +
        '<div class="hs-programs-chart__heading">' +
          '<div>' +
            '<span>' + escapeHtml(t('Vývoj zůstatku vstupů')) + '</span>' +
            '<strong>' + summary.remaining + ' ' + escapeHtml(t('zbývá')) + '</strong>' +
          '</div>' +
          '<p>' + escapeHtml(t('Graf ukazuje, jak se v čase měnil počet zbývajících vstupů podle předplacení a docházky.')) + '</p>' +
        '</div>' +
        '<div class="hs-programs-chart__legend">' +
          '<div class="hs-programs-chart__legend-item"><span>' + escapeHtml(t('Předplaceno')) + '</span><strong>' + summary.prepaid + '</strong></div>' +
          '<div class="hs-programs-chart__legend-item"><span>' + escapeHtml(t('Vyčerpáno')) + '</span><strong>' + summary.used + '</strong></div>' +
          '<div class="hs-programs-chart__legend-item hs-programs-chart__legend-item--accent"><span>' + escapeHtml(t('Zbývá')) + '</span><strong>' + summary.remaining + '</strong></div>' +
        '</div>' +
        '<div class="hs-programs-chart__canvas">' + buildChartSvg(points, minRemaining, maxRemaining, chartWidth) + '</div>' +
        '<div class="hs-programs-chart__meta">' +
          '<span>' + escapeHtml(t('Období')) + ': ' + escapeHtml(formatShortDate(points[0].date)) + ' – ' + escapeHtml(formatShortDate(points[points.length - 1].date)) + '</span>' +
          '<span>' + escapeHtml(t('Událostí')) + ': ' + events.length + '</span>' +
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

  function currentConsumeModal() {
    var section = programsSection();
    return section ? section.querySelector("[data-hs-program-consume-modal]") : null;
  }

  function setConsumeFeedback(modal, message, state) {
    if (!modal) return;
    var feedback = modal.querySelector("[data-hs-program-consume-feedback]");
    if (!feedback) return;
    feedback.classList.remove("is-error", "is-success");
    if (!message) {
      feedback.textContent = "";
      feedback.hidden = true;
      return;
    }
    feedback.textContent = message;
    feedback.hidden = false;
    if (state === "error") feedback.classList.add("is-error");
    if (state === "success") feedback.classList.add("is-success");
  }

  function setConsumeBusy(modal, busy) {
    if (!modal) return;
    var controls = modal.querySelectorAll("[data-hs-program-consume-submit], [data-hs-program-consume-close], [data-hs-program-consume-minus], [data-hs-program-consume-plus], [data-hs-program-consume-quantity]");
    for (var i = 0; i < controls.length; i += 1) controls[i].disabled = Boolean(busy);
    modal.setAttribute("aria-busy", busy ? "true" : "false");
  }

  function openConsumeModal(button) {
    var modal = currentConsumeModal();
    if (!modal || !button) return;
    modal.setAttribute("data-program-id", button.getAttribute("data-program-id") || "");

    var name = modal.querySelector("[data-hs-program-consume-name]");
    var remaining = modal.querySelector("[data-hs-program-consume-remaining]");
    var quantity = modal.querySelector("[data-hs-program-consume-quantity]");
    if (name) name.textContent = button.getAttribute("data-program-name") || "";
    if (remaining) remaining.textContent = button.getAttribute("data-program-remaining") || "0";
    if (quantity) quantity.value = "1";
    setConsumeFeedback(modal, "", "");
    setConsumeBusy(modal, false);
    modal.hidden = false;
    document.documentElement.classList.add("hs-program-consume-open");
    window.setTimeout(function () {
      if (quantity && typeof quantity.focus === "function") {
        quantity.focus();
        if (typeof quantity.select === "function") quantity.select();
      }
    }, 0);
  }

  function closeConsumeModal() {
    var modal = currentConsumeModal();
    if (!modal || modal.hidden) return;
    if (modal.getAttribute("aria-busy") === "true") return;
    modal.hidden = true;
    document.documentElement.classList.remove("hs-program-consume-open");
  }

  function consumeQuantity(modal) {
    var input = modal ? modal.querySelector("[data-hs-program-consume-quantity]") : null;
    if (!input) return 0;
    var value = parseInt(String(input.value || "").replace(/[^0-9-]/g, ""), 10);
    return Number.isFinite(value) ? value : 0;
  }

  function changeConsumeQuantity(delta) {
    var modal = currentConsumeModal();
    if (!modal) return;
    var input = modal.querySelector("[data-hs-program-consume-quantity]");
    if (!input) return;
    var value = consumeQuantity(modal);
    if (value < 1) value = 1;
    value += delta;
    if (value < 1) value = 1;
    input.value = String(value);
    input.focus();
  }

  function pollConsumeCompletion(commandId, programId, attempt) {
    var id = parseInt(commandId || "0", 10);
    var pid = parseInt(programId || "0", 10);
    var step = Math.max(0, parseInt(attempt || "0", 10) || 0);
    if (!Number.isFinite(id) || id <= 0 || !Number.isFinite(pid) || pid <= 0 || step >= 30) return;
    window.setTimeout(function () {
      fetch("/str/program-consume.php?action=status&commandId=" + encodeURIComponent(String(id)), {
        method: "GET",
        credentials: "same-origin",
        headers: { "X-Requested-With": "XMLHttpRequest" }
      })
        .then(function (response) {
          return response.text().then(function (body) {
            var json = null;
            try { json = body ? JSON.parse(body) : null; } catch (ignore) {}
            if (!response.ok || !json || !json.ok) throw new Error(json && json.error ? json.error : ("HTTP " + response.status));
            return json;
          });
        })
        .then(function (json) {
          if (json.status === "done") {
            loadPrograms(String(pid));
            return;
          }
          if (json.status === "failed") return;
          pollConsumeCompletion(id, pid, step + 1);
        })
        .catch(function () { pollConsumeCompletion(id, pid, step + 1); });
    }, step === 0 ? 500 : 1000);
  }

  function submitConsume() {
    var modal = currentConsumeModal();
    if (!modal || modal.getAttribute("aria-busy") === "true") return;
    var quantity = consumeQuantity(modal);
    var programId = parseInt(modal.getAttribute("data-program-id") || "0", 10);
    var customerGuid = pane.getAttribute("data-hs-customer-guid") || "";
    var csrf = pane.getAttribute("data-hs-program-csrf") || "";

    if (!Number.isFinite(quantity) || quantity <= 0) {
      setConsumeFeedback(modal, t("Zadejte množství větší než 0."), "error");
      return;
    }
    if (!Number.isFinite(programId) || programId <= 0 || !customerGuid || !csrf) {
      setConsumeFeedback(modal, t("Čerpání se nepodařilo připravit. Obnovte stránku a zkuste to znovu."), "error");
      return;
    }

    setConsumeBusy(modal, true);
    setConsumeFeedback(modal, t("Předávám požadavek do HairSoft…"), "");

    fetch("/str/program-consume.php", {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: JSON.stringify({
        csrf: csrf,
        customerGuid: customerGuid,
        programId: programId,
        quantity: quantity
      })
    })
      .then(function (response) {
        return response.text().then(function (body) {
          var json = null;
          try { json = body ? JSON.parse(body) : null; } catch (ignore) {}
          if (!response.ok || !json || !json.ok) {
            var message = json && json.error ? json.error : ("HTTP " + response.status);
            throw new Error(message);
          }
          return json;
        });
      })
      .then(function (json) {
        setConsumeFeedback(modal, t("Požadavek na čerpání byl zařazen do fronty pro HairSoft."), "success");
        setConsumeBusy(modal, false);
        if (json && json.commandId) pollConsumeCompletion(json.commandId, programId, 0);
        window.setTimeout(function () {
          closeConsumeModal();
        }, 900);
      })
      .catch(function (error) {
        setConsumeBusy(modal, false);
        setConsumeFeedback(modal, error && error.message ? error.message : t("Čerpání se nepodařilo předat."), "error");
      });
  }


  var programPhotos = [];
  var photoCountPollTimer = null;

  function photoButtonForProgram(programId) {
    var id = String(programId || '');
    var buttons = pane.querySelectorAll('[data-hs-program-photo-open]');
    for (var i = 0; i < buttons.length; i += 1) {
      if (String(buttons[i].getAttribute('data-program-id') || '') === id) return buttons[i];
    }
    return null;
  }

  function updatePhotoTotal(programId, count) {
    var button = photoButtonForProgram(programId);
    if (!button) return;
    var safeCount = Math.max(0, parseInt(count, 10) || 0);
    var badge = button.querySelector('[data-hs-program-photo-total]');
    if (badge) badge.textContent = safeCount > 999 ? '999+' : String(safeCount);
    button.setAttribute('data-program-photo-count', String(safeCount));
    var label = t('Fotografie programu') + '. ' + t('Odesláno fotografií celkem') + ': ' + safeCount;
    button.setAttribute('aria-label', label);
    button.setAttribute('title', label);
  }

  function refreshVisiblePhotoTotal() {
    var button = pane.querySelector('[data-hs-program-photo-open]');
    if (!button) return;
    updatePhotoTotal(button.getAttribute('data-program-id') || '', button.getAttribute('data-program-photo-count') || '0');
  }

  function fetchPhotoTotal(programId) {
    var customerGuid = pane.getAttribute('data-hs-customer-guid') || '';
    var id = parseInt(programId || '0', 10);
    if (!customerGuid || !Number.isFinite(id) || id <= 0) return Promise.resolve(null);
    var url = '/str/program-photo.php?action=count&customerGuid=' + encodeURIComponent(customerGuid) + '&programId=' + encodeURIComponent(String(id));
    return fetch(url, { method:'GET', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'} })
      .then(function (response) {
        return response.text().then(function (body) {
          var json = null;
          try { json = body ? JSON.parse(body) : null; } catch (ignore) {}
          if (!response.ok || !json || !json.ok) throw new Error(json && json.error ? json.error : ('HTTP ' + response.status));
          return Math.max(0, parseInt(json.count, 10) || 0);
        });
      });
  }

  function pollPhotoTotal(programId, expectedCount, attempt) {
    if (photoCountPollTimer) window.clearTimeout(photoCountPollTimer);
    var delays = [1200, 2800, 5000, 8000, 12000, 18000];
    var step = Math.max(0, Number(attempt) || 0);
    if (step >= delays.length) return;
    photoCountPollTimer = window.setTimeout(function () {
      fetchPhotoTotal(programId).then(function (count) {
        if (count == null) return;
        updatePhotoTotal(programId, count);
        if (count < expectedCount) pollPhotoTotal(programId, expectedCount, step + 1);
      }).catch(function () {
        pollPhotoTotal(programId, expectedCount, step + 1);
      });
    }, delays[step]);
  }

  function currentPhotoModal() {
    var section = programsSection();
    return section ? section.querySelector("[data-hs-program-photo-modal]") : null;
  }

  function revokeProgramPhotos() {
    for (var i = 0; i < programPhotos.length; i += 1) {
      if (programPhotos[i].url) URL.revokeObjectURL(programPhotos[i].url);
    }
    programPhotos = [];
  }

  function setPhotoFeedback(modal, message, state) {
    if (!modal) return;
    var box = modal.querySelector("[data-hs-program-photo-feedback]");
    if (!box) return;
    box.classList.remove("is-error", "is-success");
    if (!message) { box.textContent = ""; box.hidden = true; return; }
    box.textContent = message; box.hidden = false;
    if (state === "error") box.classList.add("is-error");
    if (state === "success") box.classList.add("is-success");
  }

  function photoBusy(modal, busy) {
    if (!modal) return;
    var controls = modal.querySelectorAll("[data-hs-program-photo-close], [data-hs-program-photo-capture], [data-hs-program-photo-submit], [data-hs-program-photo-subfolder], [data-hs-program-photo-remove], [data-hs-program-photo-camera-input]");
    for (var i = 0; i < controls.length; i += 1) controls[i].disabled = Boolean(busy);
    var sending = modal.querySelector('[data-hs-program-photo-sending]');
    if (sending) sending.hidden = !busy;
    modal.setAttribute("aria-busy", busy ? "true" : "false");
  }

  function updatePhotoModal() {
    var modal = currentPhotoModal();
    if (!modal) return;
    var list = modal.querySelector("[data-hs-program-photo-list]");
    var empty = modal.querySelector("[data-hs-program-photo-empty]");
    var count = modal.querySelector("[data-hs-program-photo-count]");
    var submit = modal.querySelector("[data-hs-program-photo-submit]");
    if (count) count.textContent = String(programPhotos.length);
    if (empty) empty.hidden = programPhotos.length > 0;
    if (submit && modal.getAttribute("aria-busy") !== "true") submit.disabled = programPhotos.length === 0;
    if (!list) return;
    list.innerHTML = "";
    programPhotos.forEach(function (photo, index) {
      var item = document.createElement("div");
      item.className = "hs-programs-photo-thumb";
      item.innerHTML = '<img alt="' + escapeHtml(t("Fotografie")) + ' ' + (index + 1) + '" src="' + escapeHtml(photo.url) + '">' +
        '<button type="button" data-hs-program-photo-remove="' + index + '" aria-label="' + escapeHtml(t("Odebrat fotografii")) + '">×</button>' +
        '<span>' + (index + 1) + '</span>';
      list.appendChild(item);
    });
  }

  function openPhotoModal(button) {
    var modal = currentPhotoModal();
    if (!modal || !button) return;
    revokeProgramPhotos();
    modal.setAttribute("data-program-id", button.getAttribute("data-program-id") || "");
    modal.setAttribute("data-program-name", button.getAttribute("data-program-name") || "");
    var name = modal.querySelector("[data-hs-program-photo-name]");
    var folder = modal.querySelector("[data-hs-program-photo-subfolder]");
    var date = modal.querySelector("[data-hs-program-photo-date]");
    if (name) name.textContent = button.getAttribute("data-program-name") || "";
    if (folder) folder.value = "";
    if (date) {
      var d = new Date();
      date.textContent = String(d.getDate()).padStart(2, "0") + "." + String(d.getMonth() + 1).padStart(2, "0") + "." + d.getFullYear();
    }
    setPhotoFeedback(modal, "", ""); photoBusy(modal, false); updatePhotoModal();
    modal.hidden = false; document.documentElement.classList.add("hs-program-photo-open");
  }

  function closePhotoModal() {
    var modal = currentPhotoModal();
    if (!modal || modal.hidden || modal.getAttribute("aria-busy") === "true") return;
    modal.hidden = true; document.documentElement.classList.remove("hs-program-photo-open"); revokeProgramPhotos();
  }

  function jpegFromFile(file) {
    return new Promise(function (resolve, reject) {
      var objectUrl = URL.createObjectURL(file);
      var image = new Image();
      image.onload = function () {
        try {
          var maxSide = 2560;
          var scale = Math.min(1, maxSide / Math.max(image.naturalWidth || 1, image.naturalHeight || 1));
          var width = Math.max(1, Math.round((image.naturalWidth || 1) * scale));
          var height = Math.max(1, Math.round((image.naturalHeight || 1) * scale));
          var canvas = document.createElement("canvas"); canvas.width = width; canvas.height = height;
          var ctx = canvas.getContext("2d");
          if (!ctx) throw new Error(t("Fotografii se nepodařilo zpracovat."));
          ctx.drawImage(image, 0, 0, width, height);
          canvas.toBlob(function (blob) {
            URL.revokeObjectURL(objectUrl);
            if (!blob) { reject(new Error(t("Fotografii se nepodařilo zpracovat."))); return; }
            resolve(blob);
          }, "image/jpeg", 0.90);
        } catch (error) { URL.revokeObjectURL(objectUrl); reject(error); }
      };
      image.onerror = function () { URL.revokeObjectURL(objectUrl); reject(new Error(t("Fotografii se nepodařilo načíst."))); };
      image.src = objectUrl;