/* HairSoft Klient V86 – moderní PDF export Hodnocení. */
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
    return String(value == null ? "" : value)
      .replace(/\u00a0/g, " ")
      .replace(/\r\n?/g, "\n")
      .replace(/[ \t]+\n/g, "\n")
      .replace(/\n[ \t]+/g, "\n")
      .replace(/[ \t]{2,}/g, " ")
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
    var clean = textValue(value);
    if (clean.normalize) clean = clean.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    return clean.replace(/[^a-zA-Z0-9_-]+/g, "_").replace(/^_+|_+$/g, "");
  }

  function htmlText(element) {
    var holder;
    if (!element) return "";
    holder = document.createElement("div");
    holder.innerHTML = String(element.innerHTML || "")
      .replace(/<br\s*\/?\s*>/gi, "\n")
      .replace(/<hr\b[^>]*>/gi, "\n");
    return textValue(holder.textContent || holder.innerText || "");
  }

  function plainCellText(cell) {
    var clone;
    var remove;
    var index;
    if (!cell) return "";
    clone = cell.cloneNode(true);
    remove = clone.querySelectorAll(".CellComment, .hs-rating-mobile-client-meta, .hs-rating-mobile-question, .hs-rating-mobile-answer");
    for (index = 0; index < remove.length; index += 1) remove[index].remove();
    return textValue(clone.textContent || clone.innerText || "");
  }

  function commentDetails(comment) {
    var text = htmlText(comment);
    var questionMatch = text.match(/Otázka\s*:\s*([^\n]*?)(?=\n|Odpověď\s*:|$)/i);
    var answerMatch = text.match(/Odpověď\s*:\s*([\s\S]*)$/i);
    return {
      question: questionMatch ? textValue(questionMatch[1]) : "",
      answer: answerMatch ? textValue(answerMatch[1]) : ""
    };
  }

  function questionValue(cell, index) {
    var visibleNode;
    var questionNode;
    var answerNode;
    var details;
    var visible;
    var question;
    var answer;
    var score;
    if (!cell || cell.classList.contains("hs-rating-question--empty")) return null;

    visibleNode = cell.querySelector(".hs-rating-desktop-value");
    questionNode = cell.querySelector(".hs-rating-mobile-question");
    answerNode = cell.querySelector(".hs-rating-mobile-answer");
    details = commentDetails(cell.querySelector(".CellComment"));
    visible = textValue(visibleNode ? visibleNode.textContent : plainCellText(cell));
    if (!visible || visible === "-") return null;
    question = textValue(questionNode ? questionNode.textContent : details.question) || "Otázka " + index;
    answer = textValue(answerNode ? answerNode.textContent : details.answer) || visible;
    score = /^\d+(?:[.,]\d+)?$/.test(visible);
    return { question: question, answer: answer, score: score };
  }

  function rowValue(row) {
    var cells = row && row.cells ? row.cells : [];
    var nameLink;
    var questions = [];
    var index;
    var value;
    if (cells.length < 5 || cells[0].classList.contains("dataTables_empty")) return null;
    nameLink = cells[3].querySelector("a");
    for (index = 5; index < cells.length; index += 1) {
      value = questionValue(cells[index], index - 4);
      if (value) questions.push(value);
    }
    return {
      appointment: plainCellText(cells[1]),
      rated: plainCellText(cells[2]),
      customer: textValue(nameLink ? nameLink.textContent : plainCellText(cells[3])),
      staff: plainCellText(cells[4]),
      questions: questions
    };
  }

  function filteredRows() {
    var table = document.querySelector("#TabulkaHodnoceni");
    var jq = window.jQuery;
    var nodes;
    var result = [];
    var index;
    var value;
    if (!table) return result;

    if (jq && jq.fn && jq.fn.DataTable && jq.fn.dataTable.isDataTable(table)) {
      nodes = jq(table).DataTable().rows({ search: "applied", order: "applied" }).nodes().toArray();
    } else {
      nodes = table.tBodies.length ? Array.prototype.slice.call(table.tBodies[0].rows) : [];
    }
    for (index = 0; index < nodes.length; index += 1) {
      value = rowValue(nodes[index]);
      if (value) result.push(value);
    }
    return result;
  }

  function metadataCell(label, value, width) {
    return {
      width: width,
      stack: [
        { text: label.toUpperCase(), color: COLORS.muted, bold: true, fontSize: 7.3, characterSpacing: 0.3 },
        { text: value || "—", color: COLORS.navy, bold: true, fontSize: 9, margin: [0, 4, 0, 0] }
      ]
    };
  }

  function questionTable(questions) {
    var body = [[
      { text: "OTÁZKA", color: COLORS.white, bold: true, fontSize: 8 },
      { text: "ODPOVĚĎ", color: COLORS.white, bold: true, fontSize: 8 }
    ]];
    var index;
    var answer;
    for (index = 0; index < questions.length; index += 1) {
      answer = questions[index].score ? questions[index].answer + " / 5" : questions[index].answer;
      body.push([
        { text: questions[index].question, color: COLORS.text, fontSize: 8.5, lineHeight: 1.22 },
        { text: answer || "—", color: questions[index].score ? COLORS.teal : COLORS.text, bold: questions[index].score, fontSize: 8.5, lineHeight: 1.22 }
      ]);
    }
    if (body.length === 1) {
      body.push([
        { text: "Bez vyplněných otázek", color: COLORS.muted, italics: true, fontSize: 8.5, colSpan: 2 },
        {}
      ]);
    }
    return {
      table: {
        headerRows: 1,
        widths: [260, "*"],
        dontBreakRows: true,
        keepWithHeaderRows: 1,
        body: body
      },
      layout: {
        hLineWidth: function (lineIndex) { return lineIndex === 1 ? 0.8 : 0.45; },
        vLineWidth: function () { return 0; },
        hLineColor: function (lineIndex) { return lineIndex === 1 ? COLORS.blue : COLORS.line; },
        paddingLeft: function () { return 10; },
        paddingRight: function () { return 10; },
        paddingTop: function (rowIndex) { return rowIndex === 0 ? 7 : 7; },
        paddingBottom: function (rowIndex) { return rowIndex === 0 ? 7 : 7; },
        fillColor: function (rowIndex) {
          if (rowIndex === 0) return COLORS.blue;
          return rowIndex % 2 === 0 ? COLORS.stripe : COLORS.white;
        }
      }
    };
  }

  function ratingBlock(rating, index) {
    return {
      margin: [0, 0, 0, 14],
      stack: [
        {
          table: {
            widths: ["*"],
            body: [[{
              margin: [10, 9, 10, 9],
              columns: [
                {
                  width: "*",
                  stack: [
                    { text: "HODNOCENÍ " + (index + 1), color: COLORS.teal, bold: true, fontSize: 8 },
                    { text: rating.customer || customerName(), color: COLORS.navy, bold: true, fontSize: 11, margin: [0, 3, 0, 0] }
                  ]
                },
                metadataCell("Kdy bylo hodnoceno", rating.rated, 120),
                metadataCell("Termín objednávky", rating.appointment, 145),
                metadataCell("Obsluha", rating.staff, 120)
              ],
              columnGap: 14,
              fillColor: COLORS.tealSoft
            }]]
          },
          layout: {
            hLineWidth: function () { return 0.55; },
            vLineWidth: function () { return 0; },
            hLineColor: function () { return COLORS.line; },
            paddingLeft: function () { return 0; },
            paddingRight: function () { return 0; },
            paddingTop: function () { return 0; },
            paddingBottom: function () { return 0; }
          }
        },
        questionTable(rating.questions)
      ]
    };
  }

  window.hsRatingsPdfCell = function (data) {
    var holder = document.createElement("div");
    holder.innerHTML = String(data == null ? "" : data).replace(/<br\s*\/?\s*>/gi, "\n");
    return textValue(holder.textContent || holder.innerText || "");
  };

  window.hsRatingsPdfFilename = function () {
    var name = safeFilenamePart(customerName());
    return "HairSoft_Hodnoceni" + (name ? "_" + name : "") + "_" + fileDate();
  };

  window.hsCustomizeRatingsPdf = function (documentDefinition) {
    var name = customerName();
    var ratings = filteredRows();
    var content = [];
    var index;

    documentDefinition.pageOrientation = "landscape";
    documentDefinition.pageSize = "A4";
    documentDefinition.pageMargins = [36, 74, 36, 48];
    documentDefinition.defaultStyle = {
      fontSize: 8.5,
      color: COLORS.text,
      lineHeight: 1.24
    };
    documentDefinition.info = {
      title: "Hodnocení – " + name,
      author: "HairSoft",
      subject: "Přehled hodnocení zákazníka"
    };

    documentDefinition.header = function () {
      return {
        margin: [36, 22, 36, 0],
        stack: [
          {
            columns: [
              { text: "HAIRSOFT", color: COLORS.teal, bold: true, fontSize: 11 },
              { text: "HODNOCENÍ ZÁKAZNÍKA", color: COLORS.muted, bold: true, fontSize: 8, alignment: "right" }
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

    content.push({
      margin: [0, 0, 0, 16],
      columns: [
        {
          width: "*",
          stack: [
            { text: "Hodnocení", color: COLORS.navy, bold: true, fontSize: 19 },
            { text: "Přehled odpovědí zákazníka", color: COLORS.muted, fontSize: 8.5, margin: [0, 4, 0, 0] }
          ]
        },
        {
          width: 270,
          alignment: "right",
          stack: [
            { text: name, color: COLORS.navy, bold: true, fontSize: 12 },
            { text: "Hodnocení: " + ratings.length + "  •  Vytvořeno " + dateStamp(), color: COLORS.muted, fontSize: 8, margin: [0, 4, 0, 0] }
          ]
        }
      ]
    });

    if (!ratings.length) {
      content.push({
        text: "Žádná hodnocení neodpovídají aktuálnímu filtru.",
        color: COLORS.muted,
        italics: true,
        fontSize: 10,
        margin: [0, 14, 0, 0]
      });
    } else {
      for (index = 0; index < ratings.length; index += 1) content.push(ratingBlock(ratings[index], index));
    }
    documentDefinition.content = content;
  };
}());
