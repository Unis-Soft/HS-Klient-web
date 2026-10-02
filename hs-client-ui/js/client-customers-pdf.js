/* HairSoft Klient V236 – moderní PDF export seznamu zákazníků. */
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

  function tr(source) {
    return typeof window.hsTranslate === "function" ? window.hsTranslate(source) : source;
  }

  function textValue(value) {
    if (value && typeof value === "object" && Object.prototype.hasOwnProperty.call(value, "text")) value = value.text;
    if (Array.isArray(value)) value = value.map(textValue).join(" ");
    return String(value == null ? "" : value)
      .replace(/\u00a0/g, " ")
      .replace(/<br\s*\/?\s*>/gi, "\n")
      .replace(/<[^>]*>/g, " ")
      .replace(/[ \t]+\n/g, "\n")
      .replace(/\s+/g, " ")
      .trim();
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

  function safeFilenamePart(value) {
    var clean = textValue(value);
    if (clean.normalize) clean = clean.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    return clean.replace(/[^a-zA-Z0-9_-]+/g, "_").replace(/^_+|_+$/g, "");
  }

  function branchName() {
    var data = window.hsPageHeaderData || {};
    var branch = textValue(data.branchName || "");
    var fallback;
    if (branch) return branch;
    fallback = document.querySelector(".pageheader .description b, .hs-dashboard-heading .description b, .breadcrumb .active b");
    return textValue(fallback && fallback.textContent) || tr("Pobočka");
  }

  function findTable(documentDefinition) {
    var content = documentDefinition && documentDefinition.content ? documentDefinition.content : [];
    var index;
    for (index = 0; index < content.length; index += 1) {
      if (content[index] && content[index].table && content[index].table.body) return content[index];
    }
    return null;
  }

  function programVisible() {
    return typeof window.hsCustomersProgramColumnVisible === "function" ? Boolean(window.hsCustomersProgramColumnVisible()) : true;
  }

  function programHeader() {
    if (typeof window.hsCustomersProgramExportHeader === "function") {
      return textValue(window.hsCustomersProgramExportHeader()) || tr("Programy");
    }
    return tr("Programy");
  }

  function headerLabels(hasProgram) {
    var labels = [
      tr("Zákazník"),
      tr("Email"),
      tr("Mobil"),
      tr("Počet návštěv"),
      tr("Zrušené"),
      tr("Příští návštěva"),
      tr("Poslední návštěva")
    ];
    if (hasProgram) labels.push(programHeader());
    labels.push(tr("Body"));
    labels.push("Blacklist");
    return labels;
  }

  function widths(hasProgram) {
    if (hasProgram) return ["*", 112, 68, 43, 43, 76, 76, 58, 38, 48];
    return ["*", 118, 72, 45, 45, 82, 82, 40, 50];
  }

  function normalizeRows(sourceBody, hasProgram) {
    var output = [];
    var index;
    var row;
    var firstName;
    var surname;
    var combined;
    var expectedSourceColumns = hasProgram ? 11 : 10;

    if (!sourceBody || !sourceBody.length) return output;
    output.push(headerLabels(hasProgram).map(function (label) {
      return { text: label, bold: true, color: COLORS.white, fontSize: 7.2 };
    }));

    for (index = 1; index < sourceBody.length; index += 1) {
      row = sourceBody[index].map(textValue);
      if (row.length < expectedSourceColumns) continue;

      // Source export intentionally contains surname + hidden first name.
      surname = row[0];
      firstName = row[1];
      combined = [textValue((firstName ? firstName + " " : "") + surname)].concat(row.slice(2));

      // Last field is the raw blacklist flag.
      if (combined.length) {
        var last = combined.length - 1;
        if (combined[last] === "1") combined[last] = tr("Ano");
        else if (combined[last] === "0" || combined[last] === "") combined[last] = tr("Ne");
      }

      output.push(combined.map(function (cell, cellIndex) {
        return {
          text: cell || "—",
          color: cellIndex === 0 ? COLORS.navy : COLORS.text,
          bold: cellIndex === 0,
          fontSize: 7.1,
          lineHeight: 1.15
        };
      }));
    }
    return output;
  }

  window.hsCustomerPdfBody = function (data) {
    return textValue(data);
  };

  window.hsCustomerPdfFilename = function () {
    var branch = safeFilenamePart(branchName());
    return "HairSoft_Seznam_zakazniku" + (branch ? "_" + branch : "") + "_" + fileDate();
  };

  window.hsCustomizeCustomerPdf = function (documentDefinition) {
    var sourceTable = findTable(documentDefinition);
    var sourceBody = sourceTable && sourceTable.table ? sourceTable.table.body || [] : [];
    var hasProgram = programVisible();
    var rows;
    var title = tr("Seznam zákazníků");
    var branch = branchName();

    // Defensive reconciliation: source export includes 11 columns with Programy,
    // 10 without it (surname + first name are still separate at this stage).
    if (sourceBody.length && sourceBody[0] && sourceBody[0].length === 10) hasProgram = false;
    if (sourceBody.length && sourceBody[0] && sourceBody[0].length === 11) hasProgram = true;
    rows = normalizeRows(sourceBody, hasProgram);

    documentDefinition.pageOrientation = "landscape";
    documentDefinition.pageSize = "A4";
    documentDefinition.pageMargins = [30, 74, 30, 46];
    documentDefinition.defaultStyle = { fontSize: 7.1, color: COLORS.text, lineHeight: 1.15 };
    documentDefinition.info = {
      title: title + " – " + branch,
      author: "HairSoft",
      subject: title
    };
    documentDefinition.header = function () {
      return {
        margin: [30, 22, 30, 0],
        stack: [
          {
            columns: [
              { text: "HAIRSOFT", color: COLORS.teal, bold: true, fontSize: 11 },
              { text: title.toUpperCase(), color: COLORS.muted, bold: true, fontSize: 8, alignment: "right" }
            ]
          },
          { canvas: [{ type: "line", x1: 0, y1: 8, x2: 782, y2: 8, lineWidth: 1.2, lineColor: COLORS.teal }] }
        ]
      };
    };
    documentDefinition.footer = function (currentPage, pageCount) {
      return {
        margin: [30, 12, 30, 0],
        columns: [
          { text: tr("HairSoft Klient • vytvořeno") + " " + dateStamp(), color: COLORS.muted, fontSize: 7.3 },
          { text: tr("Strana") + " " + currentPage + " / " + pageCount, color: COLORS.muted, fontSize: 7.3, alignment: "right" }
        ]
      };
    };

    documentDefinition.content = [
      {
        margin: [0, 0, 0, 15],
        columns: [
          {
            width: "*",
            stack: [
              { text: title, color: COLORS.navy, bold: true, fontSize: 18 },
              { text: tr("Pobočka") + ": " + branch, color: COLORS.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }
            ]
          },
          {
            width: 180,
            alignment: "right",
            stack: [
              { text: dateStamp(), color: COLORS.navy, bold: true, fontSize: 9 },
              { text: rows.length > 1 ? String(rows.length - 1) + " × " + tr("Zákazník") : "", color: COLORS.muted, fontSize: 7.5, margin: [0, 4, 0, 0] }
            ]
          }
        ]
      }
    ];

    if (rows.length <= 1) {
      documentDefinition.content.push({ text: tr("Nic nenalezeno"), color: COLORS.muted, italics: true, fontSize: 10, margin: [0, 14, 0, 0] });
      return;
    }

    documentDefinition.content.push({
      table: {
        headerRows: 1,
        widths: widths(hasProgram),
        dontBreakRows: true,
        keepWithHeaderRows: 1,
        body: rows
      },
      layout: {
        hLineWidth: function (lineIndex) { return lineIndex === 1 ? 0.8 : 0.4; },
        vLineWidth: function () { return 0; },
        hLineColor: function (lineIndex) { return lineIndex === 1 ? COLORS.blue : COLORS.line; },
        paddingLeft: function () { return 6; },
        paddingRight: function () { return 6; },
        paddingTop: function (rowIndex) { return rowIndex === 0 ? 7 : 6; },
        paddingBottom: function (rowIndex) { return rowIndex === 0 ? 7 : 6; },
        fillColor: function (rowIndex) {
          if (rowIndex === 0) return COLORS.blue;
          return rowIndex % 2 === 0 ? COLORS.stripe : COLORS.white;
        }
      }
    });
  };
}());