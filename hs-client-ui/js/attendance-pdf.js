/* HairSoft V160: robustní PDF export Docházky. Pokud vlastní renderer selže, DataTables PDF zůstane funkční. */
(function(){'use strict';
 function tr(s){return typeof window.hsTranslate==='function'?window.hsTranslate(s):s;}
 function textValue(value){
  if(value===null||typeof value==='undefined')return '';
  if(Array.isArray(value)){var parts=[];for(var i=0;i<value.length;i++)parts.push(textValue(value[i]));return parts.join(' ');}
  if(typeof value==='object'&&Object.prototype.hasOwnProperty.call(value,'text'))return textValue(value.text);
  return String(value).replace(/\u00a0/g,' ').replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim();
 }
 function sourceTable(doc){var content=doc&&doc.content?doc.content:[];for(var i=0;i<content.length;i++){if(content[i]&&content[i].table&&content[i].table.body)return content[i];}return null;}
 function dateStamp(){var d=new Date(),pad=function(n){return n<10?'0'+n:String(n);};return pad(d.getDate())+'.'+pad(d.getMonth()+1)+'.'+d.getFullYear()+' '+pad(d.getHours())+':'+pad(d.getMinutes());}
 function cloneBody(body,font){var out=[];for(var r=0;r<body.length;r++){var row=[];for(var c=0;c<body[r].length;c++){var isHead=r===0,isFoot=r===body.length-1&&body.length>2;row.push({text:textValue(body[r][c])||'—',fontSize:font,bold:isHead||isFoot,color:isHead?'#183250':isFoot?'#087d79':'#334861',fillColor:isHead?'#eef4f6':isFoot?'#eef8f7':(r%2===0?'#f8fafc':'#ffffff'),alignment:'center',margin:[2,4,2,4]});}out.push(row);}return out;}
 window.hsAttendancePdf=function(doc){
  try{
   var source=sourceTable(doc);if(!source||!source.table||!source.table.body||!source.table.body.length)return;
   var body=source.table.body,cols=body[0]&&body[0].length?body[0].length:0;if(!cols)return;
   var page=document.querySelector('.hs-attendance-page');
   var period=page&&page.getAttribute('data-attendance-period')||'';
   var center=page&&page.getAttribute('data-attendance-center')||'';
   var service=page&&page.getAttribute('data-attendance-service')||'';
   var font=cols<=5?8.2:cols<=8?7.1:cols<=12?6.2:5.2;
   var widths=[];for(var i=0;i<cols;i++)widths.push(i===0?34:'*');
   var meta=[];if(period)meta.push(tr('Období')+': '+period);if(center)meta.push(tr('Středisko')+': '+center);meta.push(service==='all'?tr('Včetně servisních obsluh'):tr('Bez servisních obsluh'));
   doc.pageSize='A4';doc.pageOrientation='landscape';doc.pageMargins=[36,74,36,48];doc.defaultStyle={fontSize:8,color:'#334861',lineHeight:1.15};
   doc.info={title:tr('Docházka')+(period?' '+period:''),author:'HairSoft',subject:tr('Docházka')};
   doc.header=function(){return{margin:[36,22,36,0],stack:[{columns:[{text:'HAIRSOFT',color:'#22B7AE',bold:true,fontSize:11},{text:tr('Docházka').toUpperCase(),color:'#74869B',bold:true,fontSize:8,alignment:'right'}]},{canvas:[{type:'line',x1:0,y1:8,x2:770,y2:8,lineWidth:1.2,lineColor:'#22B7AE'}]}]};};
   doc.footer=function(p,n){return{margin:[36,12,36,0],columns:[{text:tr('HairSoft Klient • vytvořeno')+' '+dateStamp(),fontSize:7.5,color:'#74869B'},{text:tr('Strana')+' '+p+' / '+n,alignment:'right',fontSize:7.5,color:'#74869B'}]};};
   doc.content=[
    {text:tr('Docházka')+(period?' · '+period:''),fontSize:18,bold:true,color:'#183250',margin:[0,0,0,5]},
    {text:meta.join('   ·   '),fontSize:8.5,color:'#74869B',margin:[0,0,0,16]},
    {table:{headerRows:1,dontBreakRows:true,widths:widths,body:cloneBody(body,font)},layout:{hLineWidth:function(){return .45;},vLineWidth:function(){return .35;},hLineColor:function(){return '#d9e3e9';},vLineColor:function(){return '#e2e9ee';},paddingLeft:function(){return 2;},paddingRight:function(){return 2;},paddingTop:function(){return 2;},paddingBottom:function(){return 2;}}}
   ];
  }catch(error){if(window.console&&console.error)console.error('HairSoft Docházka PDF:',error);}
 };
}());
