/* HairSoft Klient V144 – styled PDF export for SMS credit top-ups. */
(function () {
  "use strict";

  var C = {
    navy: "#183250",
    teal: "#22B7AE",
    text: "#334861",
    muted: "#74869B",
    line: "#DCE4EB",
    tealSoft: "#EAF7F6",
    rowAlt: "#F8FAFC"
  };

  function text(value) {
    if (value && typeof value === "object" && Object.prototype.hasOwnProperty.call(value, "text")) value = value.text;
    if (Array.isArray(value)) value = value.map(text).join(" ");
    return String(value == null ? "" : value)
      .replace(/\u00a0/g, " ")
      .replace(/<[^>]*>/g, " ")
      .replace(/\s+/g, " ")
      .trim();
  }

  function tr(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function stamp(file) {
    var d = new Date();
    var pad = function (n) { return n < 10 ? "0" + n : String(n); };
    return file
      ? d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate())
      : pad(d.getDate()) + "." + pad(d.getMonth() + 1) + "." + d.getFullYear() + " " + pad(d.getHours()) + ":" + pad(d.getMinutes());
  }

  function sourceTable(doc) {
    var content = doc && doc.content || [];
    for (var i = 0; i < content.length; i += 1) {
      if (content[i] && content[i].table && content[i].table.body) return content[i];
    }
    return null;
  }

  function branch() {
    var selected = document.querySelector("#VybranaPobockaID option:checked");
    var breadcrumb = document.querySelector(".pageheader .breadcrumb .active b");
    return text((selected && selected.textContent) || (breadcrumb && breadcrumb.textContent)) || tr("Pobočka");
  }

  function tariff() {
    var title = document.querySelector(".hs-sms-credit-card .panel-title");
    var value = text(title && title.textContent);
    var colon = value.indexOf(":");
    return colon >= 0 ? value.slice(colon + 1).trim() : value;
  }

  function normalizeBody(body) {
    return (body || []).map(function (row, rowIndex) {
      return row.map(function (cell, columnIndex) {
        var value = text(cell);
        var header = rowIndex === 0;
        return {
          text: value,
          bold: header,
          color: header ? C.navy : C.text,
          fillColor: header ? C.tealSoft : (rowIndex % 2 === 0 ? C.rowAlt : null),
          alignment: columnIndex === 0 ? "center" : (columnIndex >= 2 ? "right" : "left"),
          margin: header ? [6, 8, 6, 8] : [6, 9, 6, 9]
        };
      });
    });
  }

  window.hsSmsOverviewPdfFilename = function () {
    return "HairSoft_SMS_dobiti_kreditu_" + stamp(true);
  };

  window.hsSmsOverviewPdf = function (doc) {
    var source = sourceTable(doc);
    var body = source && source.table ? source.table.body || [] : [];
    var report = tr("Přehled dobití kreditu");
    var tariffText = tariff();
    var tableBody = normalizeBody(body);

    doc.pageSize = "A4";
    doc.pageOrientation = "portrait";
    doc.pageMargins = [36, 74, 36, 48];
    doc.defaultStyle = { fontSize: 8.5, color: C.text, lineHeight: 1.22 };
    doc.info = { title: report, author: "HairSoft", subject: tr("SMS a hovory") };

    doc.header = function () {
      return {
        margin: [36, 22, 36, 0],
        stack: [
          {
            columns: [
              { text: "HAIRSOFT", color: C.teal, bold: true, fontSize: 11 },
              { text: tr("SMS a hovory").toUpperCase(), color: C.muted, bold: true, fontSize: 8, alignment: "right" }
            ]
          },
          { canvas: [{ type: "line", x1: 0, y1: 8, x2: 523, y2: 8, lineWidth: 1.2, lineColor: C.teal }] }
        ]
      };
    };

    doc.footer = function (current, count) {
      return {
        margin: [36, 12, 36, 0],
        columns: [
          { text: tr("HairSoft Klient • vytvořeno") + " " + stamp(false), color: C.muted, fontSize: 7.5 },
          { text: tr("Strana") + " " + current + " / " + count, color: C.muted, fontSize: 7.5, alignment: "right" }
        ]
      };
    };

    var content = [
      {
        margin: [0, 0, 0, 16],
        columns: [
          {
            width: "*",
            stack: [
              { text: report, color: C.navy, bold: true, fontSize: 18 },
              { text: tariffText || tr("Tarif SMS"), color: C.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }
            ]
          },
          {
            width: 190,
            alignment: "right",
            stack: [
              { text: branch(), color: C.navy, bold: true, fontSize: 11 },
              { text: stamp(false), color: C.muted, fontSize: 8, margin: [0, 4, 0, 0] }
            ]
          }
        ]
      }
    ];

    if (tableBody.length <= 1) {
      content.push({ text: tr("Nic nenalezeno"), color: C.muted, italics: true, fontSize: 10, margin: [0, 14, 0, 0] });
    } else {
      content.push({
        table: {
          headerRows: 1,
          dontBreakRows: true,
          widths: [34, "*", 105, 105],
          body: tableBody
        },
        layout: {
          hLineWidth: function () { return .55; },
          vLineWidth: function () { return 0; },
          hLineColor: function () { return C.line; },
          paddingLeft: function () { return 0; },
          paddingRight: function () { return 0; },
          paddingTop: function () { return 0; },
          paddingBottom: function () { return 0; }
        }
      });
    }

    doc.content = content;
  };
}());
