/* HairSoft Klient V104 – lokalizovaný PDF export Měsíčních tržeb. */
(function () {
  "use strict";
  var C = { navy: "#183250", blue: "#5D7599", teal: "#22B7AE", text: "#334861", muted: "#74869B", line: "#DCE4EB", tealSoft: "#EAF7F6" };
  function text(value) {
    if (value && typeof value === "object" && Object.prototype.hasOwnProperty.call(value, "text")) value = value.text;
    if (Array.isArray(value)) value = value.map(text).join(" ");
    return String(value == null ? "" : value).replace(/\u00a0/g, " ").replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
  }
  function tr(source) { return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source; }
  function fold(value) { var s = text(value).toLowerCase(); return s.normalize ? s.normalize("NFD").replace(/[\u0300-\u036f]/g, "") : s; }
  function stamp(file) {
    var d = new Date(), pad = function (n) { return n < 10 ? "0" + n : String(n); };
    return file ? d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()) : pad(d.getDate()) + "." + pad(d.getMonth() + 1) + "." + d.getFullYear() + " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
  }
  function period() {
    var month = document.querySelector(".hs-monthly-period-group:first-child .hs-monthly-period-value"), year = document.querySelector(".hs-monthly-period-group:last-child .hs-monthly-period-value");
    if (month || year) return [text(month && month.textContent), text(year && year.textContent)].filter(Boolean).join(" ");
    var link = document.querySelector('a[href*="mesic=0"]'), yearLink = document.querySelector('a[href*="rok_zmena=0"]');
    return [text(link && link.nextElementSibling && link.nextElementSibling.textContent), text(yearLink && yearLink.nextElementSibling && yearLink.nextElementSibling.textContent)].filter(Boolean).join(" ") || tr("Neuvedeno");
  }
  function branch() {
    var node = document.querySelector(".pageheader .breadcrumb .active b, .pageheader .description b");
    return text(node && node.textContent) || tr("Pobočka");
  }
  function sourceTable(doc) {
    var content = doc && doc.content || [];
    for (var i = 0; i < content.length; i += 1) if (content[i] && content[i].table && content[i].table.body) return content[i];
    return null;
  }
  function metric(label, gross, net, primary) {
    return { stack: [
      { text: text(label).toUpperCase(), color: C.muted, bold: true, fontSize: 7, characterSpacing: .25 },
      { text: text(gross) || "—", color: primary ? C.teal : C.navy, bold: true, fontSize: 10, margin: [0, 5, 0, 0] },
      { text: tr("bez DPH") + "  " + (text(net) || "—"), color: C.muted, fontSize: 7.3, margin: [0, 3, 0, 0] }
    ], margin: [8, 8, 8, 8] };
  }
  function record(row, headers, individual, ordinal) {
    var start = individual ? 3 : 2, name = text(row[individual ? 2 : 1]), location = individual ? text(row[1]) : "", total = /\b(celkem|celkom|total|gesamt|summe)\b/.test(fold(row.map(text).join(" "))), values = [];
    for (var i = start; i < row.length; i += 2) values.push(metric(headers[i] || tr("Hodnota"), row[i], row[i + 1], i === start));
    while (values.length < 5) values.push(metric(tr("Hodnota"), "—", "—", false));
    values = values.slice(0, 5);
    return {
      unbreakable: true, margin: [0, 0, 0, 10],
      table: { widths: ["*"], dontBreakRows: true, body: [[{ stack: [
        { table: { widths: ["*"], body: [[{ columns: [
          { width: "*", stack: [
            { text: (total ? tr("Součet") : (individual ? tr("Obsluha") : tr("Středisko"))).toUpperCase() + (total ? "" : " " + ordinal), color: total ? C.blue : C.teal, bold: true, fontSize: 7.2, characterSpacing: .35 },
            { text: name || (total ? tr("Celkem") : tr("Bez názvu")), color: C.navy, bold: true, fontSize: 11, margin: [0, 3, 0, 0] },
            { text: location, color: C.muted, fontSize: 7.5, margin: [0, location ? 3 : 0, 0, 0] }
          ] },
          { width: 120, text: tr("Období") + "\n" + period(), color: C.muted, fontSize: 7.5, lineHeight: 1.25, alignment: "right" }
        ], margin: [10, 8, 10, 8], fillColor: total ? "#EEF3F8" : C.tealSoft }]] }, layout: "noBorders" },
        { table: { widths: ["*", "*", "*", "*", "*"], dontBreakRows: true, body: [values] }, layout: {
          hLineWidth: function () { return 0; }, vLineWidth: function (i) { return i > 0 && i < 5 ? .45 : 0; }, vLineColor: function () { return C.line; }, paddingLeft: function () { return 0; }, paddingRight: function () { return 0; }, paddingTop: function () { return 0; }, paddingBottom: function () { return 0; }
        } }
      ] }]] },
      layout: { hLineWidth: function () { return .55; }, vLineWidth: function () { return .55; }, hLineColor: function () { return C.line; }, vLineColor: function () { return C.line; }, paddingLeft: function () { return 0; }, paddingRight: function () { return 0; }, paddingTop: function () { return 0; }, paddingBottom: function () { return 0; } }
    };
  }

  window.hsMonthlyRevenuePdfPageActive = function () { return /[?&]strana=MesicniTrzby(?:&|$)/i.test(location.search) || Boolean(document.querySelector("#table_MesicniSumarZaStrediska,#table_MesicniSumarZaJednotlivce")); };
  window.hsMonthlyRevenuePdfFilename = function () { return "HairSoft_Mesicni_trzby_" + stamp(true); };
  window.hsCustomizeMonthlyRevenuePdf = function (doc) {
    var table = sourceTable(doc), body = table && table.table ? table.table.body || [] : [], headers = body.length ? body[0].map(text) : [];
    var individual = headers.some(function (header) { return /jmeno obsluhy|meno obsluhy|staff member|mitarbeiter/.test(fold(header)); });
    var report = tr(individual ? "Měsíční sumář za jednotlivce" : "Měsíční sumář za střediska a firmy"), content = [];
    doc.pageOrientation = "landscape"; doc.pageSize = "A4"; doc.pageMargins = [36, 74, 36, 48]; doc.defaultStyle = { fontSize: 8.5, color: C.text, lineHeight: 1.22 };
    doc.info = { title: report + " – " + period(), author: "HairSoft", subject: tr("Měsíční tržby") };
    doc.header = function () { return { margin: [36, 22, 36, 0], stack: [
      { columns: [{ text: "HAIRSOFT", color: C.teal, bold: true, fontSize: 11 }, { text: tr("Měsíční tržby").toUpperCase(), color: C.muted, bold: true, fontSize: 8, alignment: "right" }] },
      { canvas: [{ type: "line", x1: 0, y1: 8, x2: 770, y2: 8, lineWidth: 1.2, lineColor: C.teal }] }
    ] }; };
    doc.footer = function (current, count) { return { margin: [36, 12, 36, 0], columns: [
      { text: tr("HairSoft Klient • vytvořeno") + " " + stamp(false), color: C.muted, fontSize: 7.5 },
      { text: tr("Strana") + " " + current + " / " + count, color: C.muted, fontSize: 7.5, alignment: "right" }
    ] }; };
    content.push({ margin: [0, 0, 0, 16], columns: [
      { width: "*", stack: [{ text: report, color: C.navy, bold: true, fontSize: 18 }, { text: tr("Přehled tržeb za vybraný měsíc"), color: C.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }] },
      { width: 230, alignment: "right", stack: [{ text: period(), color: C.navy, bold: true, fontSize: 12 }, { text: branch(), color: C.muted, fontSize: 8, margin: [0, 4, 0, 0] }] }
    ] });
    if (body.length <= 1) content.push({ text: tr("Pro vybraný měsíc nejsou k dispozici žádné záznamy."), color: C.muted, italics: true, fontSize: 10, margin: [0, 14, 0, 0] });
    else for (var i = 1; i < body.length; i += 1) content.push(record(body[i], headers, individual, i));
    doc.content = content;
  };
}());
