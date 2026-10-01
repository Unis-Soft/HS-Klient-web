/* HairSoft V145 – report-style PDF/Excel helpers for SMS and call lists. */
(function(){
  'use strict';
  window.hsSmsListExcel=function(xlsx){
    if(!xlsx||!xlsx.xl||!xlsx.xl['styles.xml'])return;
    var xml=xlsx.xl['styles.xml'];Array.prototype.forEach.call(xml.getElementsByTagName('fill'),function(fill,i){if(!fill.children.length){var pattern=xml.createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main','patternFill');pattern.setAttribute('patternType',i===1?'gray125':'none');fill.appendChild(pattern);}});
  };
  window.hsSmsListPdf=function(doc,tableId,sourceTitle){
    var tr=window.hsTranslate||function(s){return s;};
    var table=document.getElementById(tableId),panel=table&&table.closest('.panel'),heading=panel&&panel.querySelector('.panel-title');
    var title=heading?heading.textContent.trim():tr(sourceTitle||'Přehled');
    var source=doc.content&&doc.content.find?doc.content.find(function(n){return n&&n.table;}):null;
    var body=source&&source.table&&source.table.body?source.table.body:[];
    function text(c){return c&&typeof c==='object'?String(c.text==null?'':c.text):String(c==null?'':c);}
    var branch=document.querySelector('.hs-dashboard-branch-value');
    doc.pageSize='A4';doc.pageOrientation=tableId==='odchozi_sms_tabulka'?'landscape':'portrait';doc.pageMargins=[36,65,36,48];doc.defaultStyle={fontSize:9,color:'#334861'};
    doc.header=function(){return{margin:[36,22,36,0],columns:[{text:'HAIRSOFT',bold:true,color:'#18a29b',fontSize:12},{text:tr('SMS a hovory'),alignment:'right',color:'#7b8ca1'}]};};
    doc.footer=function(p,n){return{margin:[36,14,36,0],columns:[{text:'HairSoft · '+new Date().toLocaleDateString('cs-CZ'),fontSize:8,color:'#7b8ca1'},{text:tr('Strana')+' '+p+' / '+n,alignment:'right',fontSize:8,color:'#7b8ca1'}]};};
    doc.content=[{text:title,fontSize:19,bold:true,color:'#183250',margin:[0,0,0,8]},{text:branch?branch.textContent.trim():'',color:'#7b8ca1',margin:[0,0,0,18]}];
    if(body.length<2){doc.content.push({text:tr('Nic nenalezeno')});doc.info={title:title,author:'HairSoft'};return;}
    var names=body[0].map(text),records=body.slice(1);
    if(names.length<=5){
      var widths;
      if(tableId==='prichozi_hovory_tabulka')widths=[90,100,'*'];
      else if(tableId==='prichozi_SMS_tabulka')widths=[78,88,'*',90,76];
      else widths=names.map(function(){return '*';});
      var formatted=[names.map(function(v){return{text:v,bold:true,color:'#28415b',fillColor:'#f3f7f9'};})];
      records.forEach(function(row,r){formatted.push(row.map(function(c){return{text:text(c),fillColor:r%2?'#f9fbfc':'#ffffff'};}));});
      doc.content.push({table:{headerRows:1,dontBreakRows:true,widths:widths,body:formatted},layout:{hLineWidth:function(){return .45;},vLineWidth:function(){return .35;},hLineColor:function(){return '#dbe5ed';},vLineColor:function(){return '#e5edf2';},paddingLeft:function(){return 6;},paddingRight:function(){return 6;},paddingTop:function(){return 7;},paddingBottom:function(){return 7;}}});
    }else{
      function card(row){var fields=[];for(var i=0;i<names.length;i++){fields.push([{text:names[i],color:'#73859a'},{text:text(row[i]),bold:names[i]===tr('Stav')||names[i]===tr('Počet SMS'),color:'#334861'}]);}
        return{stack:[{text:text(row[0])||tr('Odchozí SMS'),fontSize:11.5,bold:true,color:'#183250',margin:[0,0,0,8]},{fontSize:7.8,table:{widths:[76,'*'],body:fields},layout:{hLineWidth:function(){return .35;},vLineWidth:function(){return 0;},hLineColor:function(){return '#e1e8ef';},paddingLeft:function(){return 0;},paddingRight:function(){return 4;},paddingTop:function(){return 3;},paddingBottom:function(){return 3;}}}]};
      }
      var pairs=[];for(var x=0;x<records.length;x+=2)pairs.push([card(records[x]),records[x+1]?card(records[x+1]):{text:''}]);
      doc.content.push({table:{widths:['*','*'],dontBreakRows:true,body:pairs},layout:{vLineWidth:function(i){return i===1?.7:0;},vLineColor:function(){return '#cbd8e3';},hLineWidth:function(i){return i===0?0:.6;},hLineColor:function(){return '#dce5ec';},paddingLeft:function(i){return i===1?12:0;},paddingRight:function(i){return i===0?12:0;},paddingTop:function(){return 10;},paddingBottom:function(){return 13;}}});
    }
    doc.info={title:title,author:'HairSoft'};
  };
}());
