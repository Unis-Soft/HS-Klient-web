(function(){'use strict';
  var tr=function(s){return window.hsTranslate?window.hsTranslate(s):s;};
  window.hsStockPdf=function(doc,id){
    var source=(doc.content||[]).find(function(n){return n.table&&n.table.body;}),body=source?source.table.body:[];
    var title=tr(id==='sklad1'?'Aktuální stav zásob':'Seznam chybějícího zboží');
    var picker=document.querySelector('#idSkladuForm select'),warehouse=picker&&picker.selectedOptions[0]?picker.selectedOptions[0].textContent.trim():'';
    var branch=document.querySelector('.hs-dashboard-branch-value, .pageheader .breadcrumb .active');
    var cols=body[0]?body[0].length:4;
    doc.pageSize='A4';doc.pageOrientation='landscape';doc.pageMargins=[36,74,36,48];doc.defaultStyle={fontSize:9,color:'#334861'};
    doc.header=function(){return{margin:[36,22,36,0],stack:[{columns:[{text:'HAIRSOFT',color:'#22B7AE',bold:true,fontSize:11},{text:tr('Přehled skladu').toUpperCase(),color:'#74869B',fontSize:8,alignment:'right'}]},{canvas:[{type:'line',x1:0,y1:8,x2:770,y2:8,lineWidth:1.2,lineColor:'#22B7AE'}]}]};};
    doc.footer=function(page,count){return{margin:[36,12,36,0],columns:[{text:'HairSoft · '+new Date().toLocaleDateString('cs-CZ'),color:'#74869B',fontSize:8},{text:tr('Strana')+' '+page+' / '+count,alignment:'right',fontSize:8,color:'#74869B'}]};};
    var widths=Array.from({length:cols},function(_,i){return i===0?24:i===1?65:i===2?'*':cols>4?75:100;});
    doc.content=[{text:title,color:'#183250',bold:true,fontSize:18,margin:[0,0,0,6]},{text:[branch?branch.textContent.trim():'',warehouse].filter(Boolean).join(' · '),color:'#74869B',fontSize:9,margin:[0,0,0,18]}];
    if(body.length>1)doc.content.push({table:{headerRows:1,widths:widths,body:body.map(function(row,i){return row.map(function(cell,j){return{text:typeof cell==='object'?cell.text:String(cell),bold:i===0,fillColor:i===0?'#EAF7F6':i%2===0?'#F8FAFC':null,color:i===0?'#183250':'#334861',alignment:j>=3?'right':'left',margin:[5,9,5,9]};});})},layout:{hLineWidth:function(){return .5;},vLineWidth:function(){return 0;},hLineColor:function(){return '#DCE4EB';}}});
    else doc.content.push({text:tr('Nic nenalezeno'),color:'#74869B',margin:[0,10,0,0]});
    doc.info={title:title,author:'HairSoft'};
  };
}());
