/* Readable voucher exports: retain every field, with no action icons in the report. */
(function(){'use strict';
 // The legacy Buttons exporter emits one empty fill; give it valid OOXML content.
 window.hsVoucherExcel=function(xlsx){var xml=xlsx.xl['styles.xml'];Array.from(xml.getElementsByTagName('fill')).forEach(function(fill,i){if(!fill.children.length){var pattern=xml.createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main','patternFill');pattern.setAttribute('patternType',i===1?'gray125':'none');fill.appendChild(pattern);}});};
 window.hsVoucherPdf=function(doc,id){var tr=window.hsTranslate||function(s){return s;},table=document.getElementById(id),panel=table&&table.closest('.panel'),h=panel&&panel.querySelector('.panel-title'),title=h?h.textContent.trim():tr('Vouchery');var source=doc.content.find(function(n){return n.table;}),body=source?source.table.body:[],text=function(c){return c&&typeof c==='object'?String(c.text||''):String(c||'');};
 doc.pageSize='A4';doc.pageOrientation='portrait';doc.pageMargins=[36,65,36,48];doc.defaultStyle={fontSize:9,color:'#334861'};
 doc.header=function(){return{margin:[36,22,36,0],columns:[{text:'HAIRSOFT',bold:true,color:'#18a29b',fontSize:12},{text:tr('Vouchery'),alignment:'right',color:'#7b8ca1'}]};};
 doc.footer=function(p,n){return{margin:[36,14,36,0],columns:[{text:'HairSoft · '+new Date().toLocaleDateString('cs-CZ'),fontSize:8,color:'#7b8ca1'},{text:tr('Strana')+' '+p+' / '+n,alignment:'right',fontSize:8,color:'#7b8ca1'}]};};
 var branch=document.querySelector('.hs-dashboard-branch-value');doc.content=[{text:title,fontSize:19,bold:true,color:'#183250',margin:[0,0,0,8]},{text:branch?branch.textContent.trim():'',color:'#7b8ca1',margin:[0,0,0,18]}];
 if(body.length<2){doc.content.push({text:tr('Nic nenalezeno')});return;}
 var names=body[0].map(text);if(names.length>6){
  var codeIndex=names.indexOf(tr('Kód'));
  function card(row){var fields=[];row.forEach(function(c,i){if((i===0&&id==='TabulkaVouchery')||i===codeIndex)return;fields.push([{text:names[i],color:'#73859a'},{text:text(c),bold:names[i]===tr('Zůstatek'),color:names[i]===tr('Zůstatek')?'#087d79':'#334861'}]);});return{stack:[{text:codeIndex>=0?text(row[codeIndex]):tr('Vouchery'),fontSize:12,bold:true,color:'#183250',margin:[0,0,0,9]},{fontSize:8.2,table:{widths:[78,'*'],body:fields},layout:{hLineWidth:function(){return .35;},vLineWidth:function(){return 0;},hLineColor:function(){return '#e1e8ef';},paddingLeft:function(){return 0;},paddingRight:function(){return 4;},paddingTop:function(){return 4;},paddingBottom:function(){return 4;}}}]};}
  var records=body.slice(1),pairs=[];for(var i=0;i<records.length;i+=2)pairs.push([card(records[i]),records[i+1]?card(records[i+1]):{text:''}]);
  doc.content.push({table:{widths:['*','*'],dontBreakRows:true,body:pairs},layout:{vLineWidth:function(i){return i===1?.7:0;},vLineColor:function(){return '#cbd8e3';},hLineWidth:function(i){return i===0?0:.6;},hLineColor:function(){return '#dce5ec';},paddingLeft:function(i){return i===1?14:0;},paddingRight:function(i){return i===0?14:0;},paddingTop:function(){return 12;},paddingBottom:function(){return 16;}}});
 }
 else doc.content.push({table:{headerRows:1,widths:names.map(function(){return '*';}),body:body},layout:'lightHorizontalLines'});doc.info={title:title,author:'HairSoft'};
 };
}());
