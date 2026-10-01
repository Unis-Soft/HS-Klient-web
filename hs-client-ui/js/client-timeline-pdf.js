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
    white: "#FFFFFF"
  };

  function textValue(value) {
    return String(value == null ? "" : value)
      .replace(/\u00a0/g, " ")
      .replace(/\r\n?/g, "\n")
      .replace(/[ \t]+\n/g, "\n")
      .replace(/\n[ \t]+/g, "\n")
      .replace(/\n{3,}/g, "\n\n")
      .trim();
  }

  function customerName() {
    var firstName = document.querySelector('#Informace [name="lidi_hs_name"]');
    var surname = document.querySelector('#Informace [name="lidi_hs_surname"]');
    var name = textValue(firstName && firstName.value);
    var familyName = textValue(surname && surname.value);
    var heading;

    if (name || familyName) return textValue(name + " " + familyName);

    heading = document.querySelector(".pageheader h1, .hs-dashboard-heading h1");
    return textValue(heading ? heading.textContent : "") || "Zákazník";
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
    return textValue(value)
      .normalize ? textValue(value).normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9_-]+/g, "_").replace(/^_+|_+$/g, "") : textValue(value).replace(/[^a-zA-Z0-9_-]+/g, "_");
  }

  function toCell(cell) {
    if (cell && typeof cell === "object") return cell;
    return { text: textValue(cell) };
  }

  function findTable(documentDefinition) {
    var index;
    for (index = 0; index < documentDefinition.content.length; index += 1) {
      if (documentDefinition.content[index] && documentDefinition.content[index].table) {
        return documentDefinition.content[index];
      }
    }
    return null;
  }

  function styleTable(tableNode) {
    var body = tableNode.table.body || [];
    var rowIndex;
    var columnIndex;

    tableNode.table.headerRows = 1;
    tableNode.table.widths = [118, "*", 105];
    tableNode.table.dontBreakRows = true;
    tableNode.table.keepWithHeaderRows = 1;
    tableNode.margin = [0, 0, 0, 12];
    tableNode.layout = {
      hLineWidth: function () { return 0.55; },
      vLineWidth: function () { return 0; },
      hLineColor: function (lineIndex) { return lineIndex === 1 ? COLORS.blue : COLORS.line; },
      paddingLeft: function () { return 10; },
      paddingRight: function () { return 10; },
      paddingTop: function (row) { return row === 0 ? 9 : 8; },
      paddingBottom: function (row) { return row === 0 ? 9 : 8; },
      fillColor: function (row) {
        if (row === 0) return COLORS.blue;
        return row % 2 === 0 ? COLORS.stripe : COLORS.white;
      }
    };

    for (rowIndex = 0; rowIndex < body.length; rowIndex += 1) {
      for (columnIndex = 0; columnIndex < body[rowIndex].length; columnIndex += 1) {
        body[rowIndex][columnIndex] = toCell(body[rowIndex][columnIndex]);
        body[rowIndex][columnIndex].margin = [0, 1, 0, 1];
        body[rowIndex][columnIndex].color = rowIndex === 0 ? COLORS.white : COLORS.text;
        body[rowIndex][columnIndex].fontSize = rowIndex === 0 ? 9 : 8.5;
        body[rowIndex][columnIndex].bold = rowIndex === 0;
        body[rowIndex][columnIndex].alignment = "left";
        if (rowIndex > 0 && columnIndex === 0) {
          body[rowIndex][columnIndex].bold = true;
          body[rowIndex][columnIndex].color = COLORS.navy;
        }
      }
    }
  }

  window.hsTimelinePdfCell = function (data) {
    var holder = document.createElement("div");
    holder.innerHTML = String(data == null ? "" : data).replace(/<br\s*\/?\s*>/gi, "\n");
    return textValue(holder.textContent || holder.innerText || "");
  };

  window.hsTimelinePdfFilename = function () {
    var name = safeFilenamePart(customerName());
    return "HairSoft_Timeline" + (name ? "_" + name : "") + "_" + fileDate();
  };

  window.hsCustomizeTimelinePdf = function (documentDefinition) {
    var name = customerName();
    var tableNode = findTable(documentDefinition);

    documentDefinition.pageMargins = [36, 74, 36, 48];
    documentDefinition.defaultStyle = {
      fontSize: 8.5,
      color: COLORS.text,
      lineHeight: 1.24
    };
    documentDefinition.info = {
      title: "Timeline – " + name,
      author: "HairSoft",
      subject: "Timeline zákazníka"
    };

    documentDefinition.header = function () {
      return {
        margin: [36, 22, 36, 0],
        stack: [
          {
            columns: [
              { text: "HAIRSOFT", color: COLORS.teal, bold: true, fontSize: 11 },
              { text: "TIMELINE ZÁKAZNÍKA", color: COLORS.muted, bold: true, fontSize: 8, alignment: "right" }
            ]
          },
          {
            canvas: [
              { type: "line", x1: 0, y1: 8, x2: 770, y2: 8, lineWidth: 1.2, lineColor: COLORS.teal }
            ]
          }
        ]
      };
    };

    documentDefinition.footer = function (currentPage, pageCount) {
      return {
        margin: [36, 12, 36, 0],
        columns: [
          { text: "HairSoft Klient • vytvořeno " + dateStamp(), color: COLORS.muted, fontSize: 7.5 },
          { text: "Strana " + currentPage + " / " + pageCount, color: COLORS.muted, fontSize: 7.5, alignment: "right" }
        ]
      };
    };

    documentDefinition.content.unshift({
      margin: [0, 0, 0, 16],
      columns: [
        {
          width: "*",
          stack: [
            { text: "Timeline", color: COLORS.navy, bold: true, fontSize: 19 },
            { text: "Přehled návštěv a poznámek zákazníka", color: COLORS.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }
          ]
        },
        {
          width: 245,
          alignment: "right",
          stack: [
            { text: name, color: COLORS.navy, bold: true, fontSize: 12 },
            { text: "Vytvořeno " + dateStamp(), color: COLORS.muted, fontSize: 8, margin: [0, 4, 0, 0] }
          ]
        }
      ]
    });

    if (tableNode) styleTable(tableNode);
  };
}());
