/* HairSoft Klient V245 - complete customer PDF + XLSX export. */
(function () {
  "use strict";

  var infoPane = document.getElementById("Informace");
  if (!infoPane || !infoPane.querySelector('input[name="akce"][value="edit"]')) return;

  var COLORS = {
    navy: "#183250", blue: "#5D7599", teal: "#22B7AE", text: "#334861",
    muted: "#74869B", line: "#DCE4EB", stripe: "#F5F8FA", tealSoft: "#EAF7F6", white: "#FFFFFF"
  };

  function tr(s) { return typeof window.hsTranslate === "function" ? window.hsTranslate(s) : s; }
  function txt(v) { return String(v == null ? "" : v).replace(/\u00a0/g, " ").replace(/\r\n?/g, "\n").replace(/[ \t]+\n/g, "\n").replace(/\n[ \t]+/g, "\n").replace(/[ \t]{2,}/g, " ").trim(); }
  function htmlText(el) {
    if (!el) return "";
    var c = el.cloneNode(true), remove = c.querySelectorAll("script,style,.hs-timeline-action-group,.hs-rating-mobile-client-meta");
    Array.prototype.forEach.call(remove, function (n) { n.remove(); });
    Array.prototype.forEach.call(c.querySelectorAll("br"), function (n) { n.replaceWith("\n"); });
    return txt(c.textContent || "");
  }
  function pad(n) { return n < 10 ? "0" + n : String(n); }
  function dateStamp() { var d = new Date(); return pad(d.getDate()) + "." + pad(d.getMonth()+1) + "." + d.getFullYear() + " " + pad(d.getHours()) + ":" + pad(d.getMinutes()); }
  function fileDate() { var d = new Date(); return d.getFullYear() + "-" + pad(d.getMonth()+1) + "-" + pad(d.getDate()); }
  function safeFile(v) { var s = txt(v); if (s.normalize) s = s.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); return s.replace(/[^a-zA-Z0-9_-]+/g,"_").replace(/^_+|_+$/g,""); }
  function branchName() { var d=window.hsPageHeaderData||{}; return txt(d.branchName) || txt((document.querySelector(".breadcrumb .active b")||{}).textContent) || tr("Pobočka"); }

  var PROFILE = [
    {title:"Osobní a kontaktní údaje", fields:[
      ["lidi_hs_name","Jméno"],["lidi_hs_surname","Příjmení"],["lidi_hs_title","Titul"],["lidi_hs_BirthDay","Datum narození"],
      ["lidi_hs_email","Email"],["lidi_hs_genre","Pohlaví"],["lidi_hs_street","Ulice"],["lidi_hs_city","Město"],["lidi_hs_zip","PSČ"],
      ["lidi_hs_cell","Mobil"],["lidi_hs_phone","Telefon"]
    ]},
    {title:"Zdravotní údaje", fields:[["lidi_hs_rc","Rodné číslo"],["lidi_hs_pojistovnaGUID","Pojišťovna"],["lidi_hs_ostatni","Další záznamy (léky, alergie)"]]},
    {title:"Firma a komunikace", fields:[["lidi_hs_firm_name","Jméno firmy"],["lidi_hs_firm_register_number","IČO"],["lidi_hs_www","WWW"],["lidi_hs_skype","Skype"],["lidi_hs_facebook","Facebook"],["lidi_hs_NoSMS","Blokovat SMS"],["lidi_hs_card","Číslo karty"]]},
    {title:"Systémové údaje", fields:[["lidi_hs_pocet_navstev","Počet návštěv"],["lidi_hs_zrusene","Zrušené objednávky"],["lidi_hs_pristi_navsteva","Příští návštěva"],["lidi_hs_posledni_navsteva","Poslední návštěva"]]}
  ];

  function displayedField(name) {
    var anon = infoPane.querySelector('[name="' + name + '_hidden"]');
    if (anon && !anon.hidden && anon.value) return txt(anon.value);
    var nodes = infoPane.querySelectorAll('[name="' + name + '"]'), i, el;
    for (i=0;i<nodes.length;i+=1) { if ((nodes[i].type||"").toLowerCase() !== "hidden") { el=nodes[i]; break; } }
    if (!el && nodes.length) el=nodes[0];
    if (!el) return "";
    if (el.tagName === "SELECT") return txt(el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : el.value);
    if ((el.type||"").toLowerCase() === "checkbox") return el.checked ? tr("Ano") : tr("Ne");
    return txt(el.value);
  }

  function profileData() {
    var sections = PROFILE.map(function (s) {
      return {title:tr(s.title), rows:s.fields.map(function(f){return {label:tr(f[1]),value:displayedField(f[0])};}).filter(function(r){return r.value && r.value !== "—";})};
    }).filter(function(s){return s.rows.length;});
    var name = txt(displayedField("lidi_hs_name") + " " + displayedField("lidi_hs_surname"));
    var note = txt((document.querySelector('textarea[name="lidi_hs_note"]')||{}).value);
    var visits = displayedField("lidi_hs_pocet_navstev") || "0";
    var last = displayedField("lidi_hs_posledni_navsteva") || "—";
    var next = displayedField("lidi_hs_pristi_navsteva") || "—";
    var points = infoPane.getAttribute("data-hs-loyalty-points") || findWidgetValue("Bonusové body") || "0";
    var hsId = infoPane.getAttribute("data-hs-customer-id") || "";
    var email = displayedField("lidi_hs_email");
    var mobile = displayedField("lidi_hs_cell");
    var phone = displayedField("lidi_hs_phone");
    var street = displayedField("lidi_hs_street"), city = displayedField("lidi_hs_city"), zip = displayedField("lidi_hs_zip");
    var address = [street, [zip, city].filter(Boolean).join(" ")].filter(Boolean).join(", ");
    return {name:name||tr("Zákazník"), note:note, sections:sections, visits:visits, last:last, next:next, points:points, hsId:hsId, email:email, mobile:mobile, phone:phone, address:address};
  }

  function findWidgetValue(label) {
    var titles=document.querySelectorAll(".widget-mini .title"), i, card, total;
    for(i=0;i<titles.length;i+=1){ if(txt(titles[i].textContent)===label){card=titles[i].closest(".widget-mini");total=card&&card.querySelector(".total");return txt(total&&total.textContent);} }
    return "";
  }

  function allRows(selector) {
    var table=document.querySelector(selector), jq=window.jQuery, nodes;
    if(!table) return [];
    if(jq&&jq.fn&&jq.fn.DataTable&&jq.fn.dataTable.isDataTable(table)) {
      try { nodes=jq(table).DataTable().rows({order:"applied"}).nodes().toArray(); if(nodes.length) return nodes; } catch(e){}
    }
    return table.tBodies.length ? Array.prototype.slice.call(table.tBodies[0].rows) : [];
  }

  function timelineData() {
    return allRows("#zakaznici_tabulka_timeline").map(function(row){
      if(!row.cells||row.cells.length<3||row.cells[0].classList.contains("dataTables_empty")) return null;
      return {date:htmlText(row.cells[0]), note:htmlText(row.cells[1]), staff:htmlText(row.cells[2])};
    }).filter(Boolean);
  }

  function fileData() {
    return allRows("#zakaznici_tabulka_soubory").map(function(row){
      if(!row.cells||!row.cells.length||row.cells[0].classList.contains("dataTables_empty")) return null;
      var n=row.cells[0].querySelector(".hs-files-name__text");
      return txt(n?n.textContent:row.cells[0].textContent);
    }).filter(function(v){return v&&v!=="—";});
  }

  function ratingQuestion(cell, idx) {
    if(!cell) return null;
    var mobileQ=cell.querySelector(".hs-rating-mobile-question"), mobileA=cell.querySelector(".hs-rating-mobile-answer"), visible=cell.querySelector(".hs-rating-desktop-value"), q="", a="", raw;
    if(mobileQ) q=txt(mobileQ.textContent); if(mobileA) a=txt(mobileA.textContent);
    if(!q||!a){ raw=htmlText(cell.querySelector(".CellComment")); var qm=raw.match(/Otázka\s*:\s*([^\n]*?)(?=\n|Odpověď\s*:|$)/i), am=raw.match(/Odpověď\s*:\s*([\s\S]*)$/i); if(!q&&qm)q=txt(qm[1]); if(!a&&am)a=txt(am[1]); }
    if(!a) a=txt(visible?visible.textContent:cell.textContent); if(!q) q=tr("Otázka")+" "+idx;
    if(!a||a==="-") return null; return {question:q,answer:a};
  }

  function ratingsData() {
    return allRows("#TabulkaHodnoceni").map(function(row){
      if(!row.cells||row.cells.length<5||row.cells[0].classList.contains("dataTables_empty")) return null;
      var qs=[],i,q; for(i=5;i<row.cells.length;i+=1){q=ratingQuestion(row.cells[i],i-4);if(q)qs.push(q);}
      return {appointment:htmlText(row.cells[1]),rated:htmlText(row.cells[2]),staff:htmlText(row.cells[4]),questions:qs};
    }).filter(Boolean);
  }

  function programUrl(programId) {
    var u=new URL(window.location.href), guid=(document.getElementById("Programy")||{}).getAttribute ? document.getElementById("Programy").getAttribute("data-hs-customer-guid") : "";
    u.searchParams.set("strana","KartaOsoby"); if(guid)u.searchParams.set("osoba_guid",guid); u.searchParams.set("NavratProgramy","1");
    u.searchParams.delete("NavratGalerie");u.searchParams.delete("NavratTimeline");u.searchParams.delete("NavratSoubory");
    if(programId)u.searchParams.set("hs_program_id",String(programId));else u.searchParams.delete("hs_program_id"); return u.toString();
  }

  function parseProgram(doc) {
    var root=doc.querySelector("#Programy .hs-client-programs"), content=root&&root.querySelector(".hs-programs-content"); if(!root||!content)return null;
    var button=root.querySelector("[data-hs-program-photo-open], [data-hs-program-consume-open]"), title=root.querySelector("#hs-programs-title"), stats=content.querySelectorAll(".hs-programs-summary .hs-programs-stat strong");
    var name=button?button.getAttribute("data-program-name"):txt(title&&title.textContent), photoButton=root.querySelector("[data-hs-program-photo-open]");
    var values=Array.prototype.map.call(content.querySelectorAll(".hs-programs-value"),function(v){return {name:txt((v.querySelector("span")||{}).textContent),value:txt((v.querySelector("strong")||{}).textContent)};}).filter(function(v){return v.name;});
    var tables=content.querySelectorAll(".hs-programs-table"),visits=[],payments=[];
    Array.prototype.forEach.call(tables,function(table){
      var isPay=table.classList.contains("hs-programs-table--payments"); Array.prototype.forEach.call(table.querySelectorAll("tbody tr"),function(r){var c=r.cells;if(isPay&&c.length>=4)payments.push({date:htmlText(c[0]),amount:htmlText(c[1]),vat:htmlText(c[2]),entries:htmlText(c[3])});else if(!isPay&&c.length>=2)visits.push({date:htmlText(c[0]),count:htmlText(c[1])});});
    });
    return {id:button?button.getAttribute("data-program-id"):"",name:tr(name||tr("Program")),prepaid:txt(stats[0]&&stats[0].textContent)||"0",used:txt(stats[1]&&stats[1].textContent)||"0",remaining:txt(stats[2]&&stats[2].textContent)||"0",amount:txt(stats[3]&&stats[3].textContent)||"0",photoCount:photoButton?photoButton.getAttribute("data-program-photo-count")||txt((photoButton.querySelector("[data-hs-program-photo-total]")||{}).textContent)||"0":"0",values:values,visits:visits,payments:payments};
  }

  async function programsData() {
    var res=await fetch(programUrl(""),{credentials:"same-origin",cache:"no-store"}); if(!res.ok) return [];
    var html=await res.text(), parser=new DOMParser(), doc=parser.parseFromString(html,"text/html"), select=doc.querySelector("#hs-program-detail-select"), ids=[];
    if(select) ids=Array.prototype.map.call(select.options,function(o){return o.value;});
    var first=parseProgram(doc); if(!ids.length) return first?[first]:[];
    var jobs=ids.map(async function(id){
      if(first&&String(first.id)===String(id)) return first;
      try{var r=await fetch(programUrl(id),{credentials:"same-origin",cache:"no-store"});if(!r.ok)return null;var d=parser.parseFromString(await r.text(),"text/html");return parseProgram(d);}catch(e){return null;}
    });
    return (await Promise.all(jobs)).filter(Boolean);
  }

  async function profilePhotoCircle() {
    var photo=document.querySelector(".hs-client-profile-card__photo .OrizlaKulataFotka, .OrizlaKulataFotka"), style=photo&&photo.style?photo.style.backgroundImage:"", m=style.match(/url\(["']?(.*?)["']?\)/); if(!m||!m[1]||/no_image\.jpg/i.test(m[1]))return null;
    try{
      var url=new URL(m[1],window.location.href).href, blob=await (await fetch(url,{credentials:"same-origin",cache:"no-store"})).blob();
      var data=await new Promise(function(resolve,reject){var fr=new FileReader();fr.onload=function(){resolve(fr.result);};fr.onerror=reject;fr.readAsDataURL(blob);});
      return await new Promise(function(resolve){var img=new Image();img.onload=function(){var size=260,c=document.createElement("canvas"),x=c.getContext("2d"),s=Math.min(img.width,img.height),sx=(img.width-s)/2,sy=(img.height-s)/2;c.width=c.height=size;x.save();x.beginPath();x.arc(size/2,size/2,size/2,0,Math.PI*2);x.clip();x.drawImage(img,sx,sy,s,s,0,0,size,size);x.restore();resolve(c.toDataURL("image/png"));};img.onerror=function(){resolve(null);};img.src=data;});
    }catch(e){return null;}
  }

  async function collectAll() {
    var p=profileData(), asyncParts=await Promise.all([programsData(),profilePhotoCircle()]);
    return {profile:p,timeline:timelineData(),programs:asyncParts[0],ratings:ratingsData(),files:fileData(),photo:asyncParts[1],branch:branchName(),created:dateStamp()};
  }

  function line() { return {canvas:[{type:"line",x1:0,y1:0,x2:515,y2:0,lineWidth:0.7,lineColor:COLORS.line}],margin:[0,4,0,10]}; }
  function sectionTitle(title, subtitle, pageBreak) { return {pageBreak:pageBreak?"before":undefined,margin:[0,8,0,8],stack:[{text:title,color:COLORS.navy,bold:true,fontSize:15},{text:subtitle||"",color:COLORS.muted,fontSize:8,margin:[0,3,0,0]}]}; }
  function kvTable(rows) {
    var body=rows.map(function(r){return [{text:r.label,color:COLORS.muted,bold:true,fontSize:8},{text:r.value||"—",color:COLORS.text,fontSize:9}];});
    return {table:{widths:[150,"*"],body:body},layout:{hLineWidth:function(i,n){return i===n.table.body.length?0:0.35;},vLineWidth:function(){return 0;},hLineColor:function(){return COLORS.line;},paddingLeft:function(){return 6;},paddingRight:function(){return 6;},paddingTop:function(){return 5;},paddingBottom:function(){return 5;}}};
  }
  function simpleTable(headers, rows, widths) {
    var body=[headers.map(function(h){return {text:h,color:COLORS.white,bold:true,fontSize:7.8};})]; rows.forEach(function(r){body.push(r.map(function(v){return {text:v||"—",color:COLORS.text,fontSize:8,lineHeight:1.15};}));});
    return {table:{headerRows:1,widths:widths||headers.map(function(){return "*";}),keepWithHeaderRows:1,body:body},layout:{hLineWidth:function(i){return i===1?0.7:0.35;},vLineWidth:function(){return 0;},hLineColor:function(i){return i===1?COLORS.blue:COLORS.line;},paddingLeft:function(){return 6;},paddingRight:function(){return 6;},paddingTop:function(i){return i===0?6:5;},paddingBottom:function(i){return i===0?6:5;},fillColor:function(i){return i===0?COLORS.blue:(i%2===0?COLORS.stripe:COLORS.white);}}};
  }

  function buildPdf(data) {
    var p=data.profile, content=[];
    var heroCols=[];
    if(data.photo) heroCols.push({width:74,image:data.photo,fit:[64,64],margin:[0,0,14,0]});
    var heroStack=[{text:p.name,color:COLORS.navy,bold:true,fontSize:22},{text:tr("Zákaznický spis"),color:COLORS.muted,fontSize:9,margin:[0,3,0,0]},{text:data.branch,color:COLORS.teal,bold:true,fontSize:9,margin:[0,8,0,0]}];
    if(p.mobile||p.phone) heroStack.push({text:tr("Telefon")+": "+(p.mobile||p.phone),color:COLORS.text,fontSize:8.5,margin:[0,5,0,0]});
    if(p.email) heroStack.push({text:tr("Email")+": "+p.email,color:COLORS.text,fontSize:8.5,margin:[0,2,0,0]});
    if(p.address) heroStack.push({text:tr("Adresa")+": "+p.address,color:COLORS.text,fontSize:8.5,margin:[0,2,0,0]});
    heroCols.push({width:"*",stack:heroStack});
    heroCols.push({width:118,alignment:"right",stack:[{text:tr("Vytvořeno").toUpperCase(),color:COLORS.muted,bold:true,fontSize:7},{text:data.created,color:COLORS.navy,bold:true,fontSize:9,margin:[0,3,0,0]}].concat(p.hsId?[{text:"HAIRSOFT ID",color:COLORS.muted,bold:true,fontSize:7,margin:[0,9,0,0]},{text:p.hsId,color:COLORS.navy,bold:true,fontSize:10,margin:[0,3,0,0]}]:[])});
    content.push({columns:heroCols,margin:[0,0,0,12]});
    content.push({table:{widths:["*","*","*","*"],body:[[
      {text:tr("Počet návštěv")+"\n"+p.visits,style:"summary"},{text:tr("Bonusové body")+"\n"+p.points,style:"summary"},{text:tr("Poslední návštěva")+"\n"+p.last,style:"summary"},{text:tr("Příští návštěva")+"\n"+p.next,style:"summary"}
    ]]},layout:{fillColor:function(){return COLORS.tealSoft;},hLineWidth:function(){return 0;},vLineWidth:function(){return 0;},paddingLeft:function(){return 9;},paddingRight:function(){return 9;},paddingTop:function(){return 8;},paddingBottom:function(){return 8;}}});
    if(p.note) content.push({margin:[0,12,0,0],table:{widths:["*"],body:[[{stack:[{text:tr("Poznámka"),color:COLORS.muted,bold:true,fontSize:8},{text:p.note,color:COLORS.text,fontSize:9,margin:[0,4,0,0]}]}]]},layout:{fillColor:function(){return "#FAFBFC";},hLineColor:function(){return COLORS.line;},vLineColor:function(){return COLORS.line;},hLineWidth:function(){return 0.5;},vLineWidth:function(){return 0.5;},paddingLeft:function(){return 10;},paddingRight:function(){return 10;},paddingTop:function(){return 8;},paddingBottom:function(){return 8;}}});

    content.push(sectionTitle(tr("Informace o zákazníkovi"),tr("Přehled evidovaných údajů"),false));
    p.sections.forEach(function(s){content.push({text:s.title,color:COLORS.teal,bold:true,fontSize:10,margin:[0,7,0,4]});content.push(kvTable(s.rows));});

    content.push(sectionTitle(tr("Timeline"),tr("Přehled návštěv a poznámek zákazníka"),true));
    content.push(data.timeline.length?simpleTable([tr("Datum"),tr("Obsluha"),tr("Poznámka")],data.timeline.map(function(r){return [r.date,r.staff,r.note];}),[90,110,"*"]):{text:tr("Bez záznamů"),color:COLORS.muted,italics:true});

    content.push(sectionTitle(tr("Programy"),tr("Přehled čerpání a předplacených vstupů"),true));
    if(!data.programs.length) content.push({text:tr("Zákazník nemá žádný program."),color:COLORS.muted,italics:true});
    data.programs.forEach(function(pg,idx){
      if(idx) content.push(line());
      content.push({text:pg.name,color:COLORS.navy,bold:true,fontSize:13,margin:[0,3,0,7]});
      content.push({table:{widths:["*","*","*","*","*"],body:[[
        {text:tr("Předplaceno")+"\n"+pg.prepaid,style:"programStat"},{text:tr("Vyčerpáno")+"\n"+pg.used,style:"programStat"},{text:tr("Zbývá")+"\n"+pg.remaining,style:"programStat"},{text:tr("Částka celkem")+"\n"+pg.amount,style:"programStat"},{text:tr("Fotografií programu")+"\n"+pg.photoCount,style:"programStat"}
      ]]},layout:{fillColor:function(){return COLORS.stripe;},hLineWidth:function(){return 0;},vLineWidth:function(){return 0;},paddingLeft:function(){return 7;},paddingRight:function(){return 7;},paddingTop:function(){return 7;},paddingBottom:function(){return 7;}}});
      if(pg.values.length){content.push({text:tr("Údaje programu"),color:COLORS.teal,bold:true,fontSize:9,margin:[0,9,0,4]});content.push(kvTable(pg.values.map(function(v){return {label:v.name,value:v.value};})));}
      if(pg.visits.length){content.push({text:tr("Docházka"),color:COLORS.teal,bold:true,fontSize:9,margin:[0,9,0,4]});content.push(simpleTable([tr("Datum"),tr("Počet")],pg.visits.map(function(v){return [v.date,v.count];}),["*",70]));}
      if(pg.payments.length){content.push({text:tr("Předplacené vstupy"),color:COLORS.teal,bold:true,fontSize:9,margin:[0,9,0,4]});content.push(simpleTable([tr("Datum"),tr("Částka"),tr("DPH"),tr("Vstupů")],pg.payments.map(function(v){return [v.date,v.amount,v.vat,v.entries];}),[120,"*",55,55]));}
    });

    content.push(sectionTitle(tr("Hodnocení"),tr("Přehled zpětné vazby zákazníka"),true));
    if(!data.ratings.length) content.push({text:tr("Bez hodnocení"),color:COLORS.muted,italics:true});
    data.ratings.forEach(function(r,idx){
      if(idx) content.push(line());
      content.push({columns:[{width:"*",text:tr("Termín objednávky")+": "+(r.appointment||"—"),color:COLORS.text,fontSize:8},{width:"*",text:tr("Kdy bylo hodnoceno")+": "+(r.rated||"—"),color:COLORS.text,fontSize:8},{width:"*",text:tr("Obsluha")+": "+(r.staff||"—"),color:COLORS.text,fontSize:8}],margin:[0,0,0,5]});
      if(r.questions.length) content.push(simpleTable([tr("Otázka"),tr("Odpověď")],r.questions.map(function(q){return [q.question,q.answer];}),["*","*"])); else content.push({text:tr("Bez vyplněných otázek"),color:COLORS.muted,italics:true,fontSize:8});
    });

    content.push(sectionTitle(tr("Soubory"),tr("Seznam souborů evidovaných u zákazníka"),true));
    content.push(data.files.length?simpleTable([tr("Název souboru")],data.files.map(function(n){return [n];}),["*"]):{text:tr("Zatím zde nejsou žádné soubory."),color:COLORS.muted,italics:true});

    return {pageSize:"A4",pageMargins:[38,68,38,48],defaultStyle:{fontSize:8.5,color:COLORS.text,lineHeight:1.18},info:{title:tr("Zákaznický spis")+" – "+p.name,author:"HairSoft",subject:tr("Export zákazníka")},header:function(){return {margin:[38,20,38,0],stack:[{columns:[{text:"HAIRSOFT",color:COLORS.teal,bold:true,fontSize:11},{text:tr("Zákaznický spis").toUpperCase(),color:COLORS.muted,bold:true,fontSize:8,alignment:"right"}]},{canvas:[{type:"line",x1:0,y1:8,x2:519,y2:8,lineWidth:1.2,lineColor:COLORS.teal}]}]};},footer:function(cur,total){return {margin:[38,12,38,0],columns:[{text:tr("HairSoft Klient • vytvořeno")+" "+data.created,color:COLORS.muted,fontSize:7.3},{text:tr("Strana")+" "+cur+" / "+total,color:COLORS.muted,fontSize:7.3,alignment:"right"}]};},styles:{summary:{color:COLORS.navy,bold:true,fontSize:8.5,lineHeight:1.4},programStat:{color:COLORS.navy,bold:true,fontSize:8,lineHeight:1.4}},content:content};
  }

  function xmlEscape(v){return String(v==null?"":v).replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g,"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&apos;");}
  function colName(n){var s="";while(n>0){n--;s=String.fromCharCode(65+(n%26))+s;n=Math.floor(n/26);}return s;}
  function sheetXml(rows){
    var maxCols=0;rows.forEach(function(r){maxCols=Math.max(maxCols,r.length);});
    var widths=[];for(var c=0;c<maxCols;c++){var m=10;rows.forEach(function(r){m=Math.max(m,String(r[c]||"").length+2);});widths[c]=Math.min(46,m);}
    var cols=widths.map(function(w,i){return '<col min="'+(i+1)+'" max="'+(i+1)+'" width="'+w+'" customWidth="1"/>';}).join("");
    var body=rows.map(function(r,ri){return '<row r="'+(ri+1)+'">'+r.map(function(v,ci){var ref=colName(ci+1)+(ri+1),style=ri===0?1:0;return '<c r="'+ref+'" t="inlineStr" s="'+style+'"><is><t xml:space="preserve">'+xmlEscape(v)+'</t></is></c>';}).join("")+'</row>';}).join("");
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'+cols+'</cols><sheetData>'+body+'</sheetData></worksheet>';
  }
  function uniqueSheetName(name,used){var base=String(name).replace(/[\\\/?*\[\]:]/g," ").trim().slice(0,31)||"List",n=base,i=2;while(used[n]){n=(base.slice(0,27)+" "+i).slice(0,31);i++;}used[n]=true;return n;}
  async function makeXlsx(data){
    if(!window.JSZip) throw new Error(tr("Excel export není dostupný."));
    var p=data.profile, sheets=[], customer=[[tr("Sekce"),tr("Položka"),tr("Hodnota")]];
    customer.push([tr("Souhrn"),tr("Zákazník"),p.name],[tr("Souhrn"),tr("Pobočka"),data.branch],[tr("Souhrn"),"HairSoft ID",p.hsId],[tr("Souhrn"),tr("Bonusové body"),p.points],[tr("Souhrn"),tr("Počet návštěv"),p.visits],[tr("Souhrn"),tr("Poslední návštěva"),p.last],[tr("Souhrn"),tr("Příští návštěva"),p.next]); if(p.note)customer.push([tr("Souhrn"),tr("Poznámka"),p.note]);
    p.sections.forEach(function(s){s.rows.forEach(function(r){customer.push([s.title,r.label,r.value]);});}); sheets.push({name:tr("Zákazník"),rows:customer});
    sheets.push({name:tr("Timeline"),rows:[[tr("Datum"),tr("Obsluha"),tr("Záznam")]].concat(data.timeline.map(function(r){return[r.date,r.staff,r.note];}))});
    sheets.push({name:tr("Programy"),rows:[[tr("Program"),tr("Předplaceno"),tr("Vyčerpáno"),tr("Zbývá"),tr("Částka celkem"),tr("Fotografií programu"),tr("Údaje programu")]].concat(data.programs.map(function(pg){return[pg.name,pg.prepaid,pg.used,pg.remaining,pg.amount,pg.photoCount,pg.values.map(function(v){return v.name+": "+v.value;}).join("; ")];}))});
    var visits=[[tr("Program"),tr("Datum"),tr("Počet")]],payments=[[tr("Program"),tr("Datum"),tr("Částka"),tr("DPH"),tr("Vstupů")]]; data.programs.forEach(function(pg){pg.visits.forEach(function(v){visits.push([pg.name,v.date,v.count]);});pg.payments.forEach(function(v){payments.push([pg.name,v.date,v.amount,v.vat,v.entries]);});});
    sheets.push({name:tr("Docházka"),rows:visits});sheets.push({name:tr("Předplacené vstupy"),rows:payments});
    var ratings=[[tr("Termín objednávky"),tr("Kdy bylo hodnoceno"),tr("Obsluha"),tr("Otázka"),tr("Odpověď")]];data.ratings.forEach(function(r){if(r.questions.length)r.questions.forEach(function(q){ratings.push([r.appointment,r.rated,r.staff,q.question,q.answer]);});else ratings.push([r.appointment,r.rated,r.staff,"",""]);});sheets.push({name:tr("Hodnocení"),rows:ratings});
    sheets.push({name:tr("Soubory"),rows:[[tr("Název souboru")]].concat(data.files.map(function(n){return[n];}))});
    var zip=new JSZip(),used={},names=sheets.map(function(s){return uniqueSheetName(s.name,used);});
    zip.file("[Content_Types].xml",'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'+sheets.map(function(s,i){return '<Override PartName="/xl/worksheets/sheet'+(i+1)+'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';}).join("")+'</Types>');
    zip.folder("_rels").file(".rels",'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    zip.folder("xl").file("workbook.xml",'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'+names.map(function(n,i){return '<sheet name="'+xmlEscape(n)+'" sheetId="'+(i+1)+'" r:id="rId'+(i+1)+'"/>';}).join("")+'</sheets></workbook>');
    zip.folder("xl").folder("_rels").file("workbook.xml.rels",'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+sheets.map(function(s,i){return '<Relationship Id="rId'+(i+1)+'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'+(i+1)+'.xml"/>';}).join("")+'<Relationship Id="rId'+(sheets.length+1)+'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
    zip.folder("xl").file("styles.xml",'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="10"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="10"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF5D7599"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><dxfs count="0"/></styleSheet>');
    var ws=zip.folder("xl").folder("worksheets");sheets.forEach(function(s,i){ws.file("sheet"+(i+1)+".xml",sheetXml(s.rows));}); return await zip.generateAsync({type:"blob",mimeType:"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"});
  }

  function downloadBlob(blob,name){var a=document.createElement("a"),url=URL.createObjectURL(blob);a.href=url;a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(function(){URL.revokeObjectURL(url);},2000);}
  function showBusy(text){var o=document.querySelector(".hs-customer-export-busy");if(!o){o=document.createElement("div");o.className="hs-customer-export-busy";o.innerHTML='<div class="hs-customer-export-busy__card"><span class="hs-customer-export-spinner"></span><strong></strong><small></small></div>';document.body.appendChild(o);}o.querySelector("strong").textContent=text||tr("Připravuji export…");o.querySelector("small").textContent=tr("Načítám kompletní data zákazníka.");o.hidden=false;}
  function hideBusy(){var o=document.querySelector(".hs-customer-export-busy");if(o)o.hidden=true;}

  async function runExport(type){
    closeMenu();showBusy(type==="pdf"?tr("Připravuji PDF…"):tr("Připravuji Excel…"));
    try{var data=await collectAll(),fn="HairSoft_"+safeFile(data.profile.name)+"_"+fileDate();if(type==="pdf"){if(!window.pdfMake)throw new Error(tr("PDF export není dostupný."));window.pdfMake.createPdf(buildPdf(data)).download(fn+".pdf");}else{downloadBlob(await makeXlsx(data),fn+".xlsx");}}catch(e){window.alert((e&&e.message)?e.message:tr("Export se nepodařilo vytvořit."));}finally{hideBusy();}
  }

  function closeMenu(){var m=document.querySelector(".hs-customer-detail-export-menu"),b=document.querySelector(".hs-customer-detail-export-backdrop"),btn=document.querySelector(".hs-customer-detail-export-button");if(m)m.hidden=true;if(b)b.hidden=true;if(btn)btn.setAttribute("aria-expanded","false");}
  function toggleMenu(){var m=document.querySelector(".hs-customer-detail-export-menu"),b=document.querySelector(".hs-customer-detail-export-backdrop"),btn=document.querySelector(".hs-customer-detail-export-button"),open=m&&m.hidden;if(m)m.hidden=!open;if(b)b.hidden=!open;if(btn)btn.setAttribute("aria-expanded",open?"true":"false");}

  function mount(){
    var header=document.querySelector("#main-wrapper > section.main-content-wrapper > .pageheader"), meta=header&&header.querySelector(".breadcrumb-wrapper");if(!header||header.querySelector(".hs-customer-detail-export-button"))return;
    var actions=header.querySelector(".hs-customer-detail-header-actions");if(!actions){actions=document.createElement("div");actions.className="hs-customer-detail-header-actions";if(meta){header.insertBefore(actions,meta);actions.appendChild(meta);}else header.appendChild(actions);}
    var btn=document.createElement("button");btn.type="button";btn.className="hs-customer-detail-export-button";btn.setAttribute("aria-haspopup","true");btn.setAttribute("aria-expanded","false");btn.innerHTML='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"></path></svg><span>'+tr("Export")+'</span>';actions.appendChild(btn);
    var backdrop=document.createElement("div");backdrop.className="hs-customer-detail-export-backdrop";backdrop.hidden=true;document.body.appendChild(backdrop);
    var menu=document.createElement("div");menu.className="hs-customer-detail-export-menu";menu.hidden=true;menu.innerHTML='<button type="button" data-export="pdf"><span class="hs-customer-detail-export-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l3 3v17H6z"></path><path d="M15 2v4h4M8 15h8M8 18h6"></path></svg></span><span><strong>'+tr("PDF dokument")+'</strong><small>'+tr("Kompletní zákaznický spis")+'</small></span></button><button type="button" data-export="xlsx"><span class="hs-customer-detail-export-icon"><svg viewBox="0 0 24 24"><path d="M6 2h9l3 3v17H6z"></path><path d="M15 2v4h4M9 11l5 6M14 11l-5 6"></path></svg></span><span><strong>'+tr("Excel")+'</strong><small>'+tr("Více listů podle typu dat")+'</small></span></button>';document.body.appendChild(menu);
    btn.addEventListener("click",toggleMenu);backdrop.addEventListener("click",closeMenu);menu.addEventListener("click",function(e){var b=e.target.closest("button[data-export]");if(b)runExport(b.getAttribute("data-export"));});document.addEventListener("keydown",function(e){if(e.key==="Escape")closeMenu();});
    function position(){if(menu.hidden||window.innerWidth<768)return;var r=btn.getBoundingClientRect();menu.style.top=(r.bottom+8)+"px";menu.style.left=Math.max(12,r.right-260)+"px";}btn.addEventListener("click",function(){setTimeout(position,0);});window.addEventListener("resize",function(){if(window.innerWidth>=768)position();});
    document.addEventListener("hs:languagechange",function(){btn.querySelector("span").textContent=tr("Export");menu.querySelector('[data-export="pdf"] strong').textContent=tr("PDF dokument");menu.querySelector('[data-export="pdf"] small').textContent=tr("Kompletní zákaznický spis");menu.querySelector('[data-export="xlsx"] strong').textContent=tr("Excel");menu.querySelector('[data-export="xlsx"] small').textContent=tr("Více listů podle typu dat");});
  }

  if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",mount,{once:true});else mount();
}());