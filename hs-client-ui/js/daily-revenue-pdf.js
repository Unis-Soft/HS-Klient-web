/* HairSoft Klient V101 – lokalizovaný PDF export Denních tržeb. */
(function () {
  "use strict";

  var COLORS = {
    navy: "#183250",
    blue: "#5D7599",
    teal: "#22B7AE",
    text: "#334861",
    muted: "#74869B",
    line: "#DCE4EB",
    stripe: "#F5F8FA",
    tealSoft: "#EAF7F6",
    white: "#FFFFFF"
  };

  function textValue(value) {
    if (value && typeof value === "object" && Object.prototype.hasOwnProperty.call(value, "text")) value = value.text;
    if (Array.isArray(value)) value = value.map(textValue).join(" ");
    return String(value == null ? "" : value)
      .replace(/\u00a0/g, " ")
      .replace(/<[^>]*>/g, " ")
      .replace(/\s+/g, " ")
      .trim();
  }

  function tr(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function folded(value) {
    var text = textValue(value).toLowerCase();
    if (text.normalize) text = text.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    return text;
  }

  function dateStamp() {
    var now = new Date();
    var pad = function (number) { return number < 10 ? "0" + number : String(number); };
    return pad(now.getDate()) + "." + pad(now.getMonth() + 1) + "." + now.getFullYear() +
      " " + pad(now.getHours()) + ":" + pad(now.getMinutes());
  }

  function fileDate() {
    var now = new Date();
    var pad = function (number) { return number < 10 ? "0" + number : String(number); };
    return now.getFullYear() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate());
  }

  function selectedDate() {
    var input = document.getElementById("datepicker1");
    return textValue(input && input.value) || tr("Neuvedeno");
  }

  function branchName() {
    var branch = document.querySelector(".pageheader .breadcrumb .active b, .pageheader .description b");
    return textValue(branch && branch.textContent) || tr("Pobočka");
  }

  function findTable(documentDefinition) {
    var content = documentDefinition && documentDefinition.content ? documentDefinition.content : [];
    var index;
    for (index = 0; index < content.length; index += 1) {
      if (content[index] && content[index].table && content[index].table.body) return content[index];
    }
    return null;
  }

  function metricCell(label, gross, net, highlight) {
    return {
      stack: [
        { text: textValue(label).toUpperCase(), color: COLORS.muted, bold: true, fontSize: 7, characterSpacing: 0.25 },
        { text: textValue(gross) || "—", color: highlight ? COLORS.teal : COLORS.navy, bold: true, fontSize: 10, margin: [0, 5, 0, 0] },
        { text: tr("bez DPH") + "  " + (textValue(net) || "—"), color: COLORS.muted, fontSize: 7.3, margin: [0, 3, 0, 0] }
      ],
      margin: [8, 8, 8, 8]
    };
  }

  function revenueBlock(row, headers, isIndividual, rowIndex) {
    var metricStart = isIndividual ? 3 : 2;
    var name = isIndividual ? textValue(row[2]) : textValue(row[1]);
    var branch = isIndividual ? textValue(row[1]) : "";
    var total = /\b(celkem|celkom|total|gesamt|summe)\b/.test(folded(row.join ? row.map(textValue).join(" ") : ""));
    var metrics = [];
    var index;
    for (index = metricStart; index < row.length; index += 2) {
      metrics.push(metricCell(headers[index] || tr("Hodnota"), row[index], row[index + 1], index === metricStart));
    }
    while (metrics.length < 5) metrics.push(metricCell(tr("Hodnota"), "—", "—", false));
    if (metrics.length > 5) metrics = metrics.slice(0, 5);

    return {
      unbreakable: true,
      margin: [0, 0, 0, 10],
      table: {
        widths: ["*"],
        dontBreakRows: true,
        body: [[{
          stack: [
            {
              table: {
                widths: ["*"],
                body: [[{
            columns: [
              {
                width: "*",
                stack: [
                  { text: total ? tr("Součet").toUpperCase() : (isIndividual ? tr("Obsluha").toUpperCase() + " " + rowIndex : tr("Středisko").toUpperCase() + " " + rowIndex), color: total ? COLORS.blue : COLORS.teal, bold: true, fontSize: 7.2, characterSpacing: 0.35 },
                  { text: name || (total ? tr("Celkem") : tr("Bez názvu")), color: COLORS.navy, bold: true, fontSize: 11, margin: [0, 3, 0, 0] },
                  { text: branch, color: COLORS.muted, fontSize: 7.5, margin: [0, branch ? 3 : 0, 0, 0] }
                ]
              },
              { text: tr("Datum") + "\n" + selectedDate(), width: 100, color: COLORS.muted, fontSize: 7.5, lineHeight: 1.25, alignment: "right" }
            ],
            margin: [10, 8, 10, 8],
            fillColor: total ? "#EEF3F8" : COLORS.tealSoft
                }]]
              },
              layout: {
                hLineWidth: function () { return 0; },
                vLineWidth: function () { return 0; },
                paddingLeft: function () { return 0; },
                paddingRight: function () { return 0; },
                paddingTop: function () { return 0; },
                paddingBottom: function () { return 0; }
              }
            },
            {
            table: {
              widths: ["*", "*", "*", "*", "*"],
              dontBreakRows: true,
              body: [metrics]
            },
            layout: {
              hLineWidth: function () { return 0; },
              vLineWidth: function (lineIndex) { return lineIndex > 0 && lineIndex < 5 ? 0.45 : 0; },
              vLineColor: function () { return COLORS.line; },
              paddingLeft: function () { return 0; },
              paddingRight: function () { return 0; },
              paddingTop: function () { return 0; },
              paddingBottom: function () { return 0; }
            }
            }
          ],
          margin: [0, 0, 0, 0]
        }]]
      },
      layout: {
        hLineWidth: function () { return 0.55; },
        vLineWidth: function () { return 0.55; },
        hLineColor: function () { return COLORS.line; },
        vLineColor: function () { return COLORS.line; },
        paddingLeft: function () { return 0; },
        paddingRight: function () { return 0; },
        paddingTop: function () { return 0; },
        paddingBottom: function () { return 0; }
      }
    };
  }

  window.hsDailyRevenuePdfFilename = function () {
    return "HairSoft_Denni_trzby_" + fileDate();
  };

  window.hsDailyRevenuePdfPageActive = function () {
    return /[?&]strana=CelkoveTrzby(?:&|$)/i.test(window.location.search) ||
      Boolean(document.querySelector("#table_DenniSumarZaStrediska, #table_DenniSumarZaJednotlivce"));
  };

  window.hsCustomizeDailyRevenuePdf = function (documentDefinition) {
    var sourceTable = findTable(documentDefinition);
    var sourceBody = sourceTable && sourceTable.table ? sourceTable.table.body || [] : [];
    var headers = sourceBody.length ? sourceBody[0].map(textValue) : [];
    var isIndividual = headers.some(function (header) {
      return /jmeno obsluhy|meno obsluhy|staff member|mitarbeiter/.test(folded(header));
    });
    var reportName = tr(isIndividual ? "Denní sumář za jednotlivce" : "Denní sumář za střediska a firmy");
    var content = [];
    var index;

    documentDefinition.pageOrientation = "landscape";
    documentDefinition.pageSize = "A4";
    documentDefinition.pageMargins = [36, 74, 36, 48];
    documentDefinition.defaultStyle = { fontSize: 8.5, color: COLORS.text, lineHeight: 1.22 };
    documentDefinition.info = {
      title: reportName + " – " + selectedDate(),
      author: "HairSoft",
      subject: tr("Denní tržby")
    };
    documentDefinition.header = function () {
      return {
        margin: [36, 22, 36, 0],
        stack: [
          {
            columns: [
              { text: "HAIRSOFT", color: COLORS.teal, bold: true, fontSize: 11 },
              { text: tr("Denní tržby").toUpperCase(), color: COLORS.muted, bold: true, fontSize: 8, alignment: "right" }
            ]
          },
          { canvas: [{ type: "line", x1: 0, y1: 8, x2: 770, y2: 8, lineWidth: 1.2, lineColor: COLORS.teal }] }
        ]
      };
    };
    documentDefinition.footer = function (currentPage, pageCount) {
      return {
        margin: [36, 12, 36, 0],
        columns: [
          { text: tr("HairSoft Klient • vytvořeno") + " " + dateStamp(), color: COLORS.muted, fontSize: 7.5 },
          { text: tr("Strana") + " " + currentPage + " / " + pageCount, color: COLORS.muted, fontSize: 7.5, alignment: "right" }
        ]
      };
    };

    content.push({
      margin: [0, 0, 0, 16],
      columns: [
        {
          width: "*",
          stack: [
            { text: reportName, color: COLORS.navy, bold: true, fontSize: 18 },
            { text: tr("Přehled tržeb za vybraný den"), color: COLORS.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }
          ]
        },
        {
          width: 230,
          alignment: "right",
          stack: [
            { text: selectedDate(), color: COLORS.navy, bold: true, fontSize: 12 },
            { text: branchName(), color: COLORS.muted, fontSize: 8, margin: [0, 4, 0, 0] }
          ]
        }
      ]
    });

    if (sourceBody.length <= 1) {
      content.push({ text: tr("Pro vybraný den nejsou k dispozici žádné záznamy."), color: COLORS.muted, italics: true, fontSize: 10, margin: [0, 14, 0, 0] });
    } else {
      for (index = 1; index < sourceBody.length; index += 1) {
        content.push(revenueBlock(sourceBody[index], headers, isIndividual, index));
      }
    }
    documentDefinition.content = content;
  };
}());
