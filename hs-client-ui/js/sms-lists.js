/* HairSoft V145 – shared modern presentation for the three SMS/call list pages. */
(function(){
  'use strict';
  var route=new URLSearchParams(location.search).get('strana')||'';
  var configs={
    PrichoziHovory:{id:'prichozi_hovory_tabulka',title:'Přehled hovorů',placeholder:'Hledat v hovorech…',icon:'phone',wide:[],headers:['Datum hovoru','Telefon','Zákazník']},
    PrichoziSMS:{id:'prichozi_SMS_tabulka',title:'Přehled SMS',placeholder:'Hledat v příchozích SMS…',icon:'message',wide:[2],headers:['Datum SMS','Telefon','Text SMS','Zákazník','Reakce na']},
    OdchoziSMS:{id:'odchozi_sms_tabulka',title:'Přehled odchozích SMS',placeholder:'Hledat v odchozích SMS…',icon:'send',wide:[5,6],headers:['Přijato do systému','Telefon','Příjmení','Jméno','Pohlaví','Text','Šablona','Kdy odeslat','Odesláno','Stav','Počet SMS']}
  };
  var cfg=configs[route];if(!cfg)return;
  var main=document.getElementById('main-content');if(!main)return;
  main.classList.add('hs-sms-list-page');main.setAttribute('data-hs-sms-list',route);
  function tr(s){return window.hsTranslate?window.hsTranslate(s):s;}
  function setText(n,s){if(!n)return;if(window.hsSetTranslatedText)window.hsSetTranslatedText(n,s);else n.textContent=tr(s);}
  function setAttr(n,a,s){if(!n)return;if(window.hsSetTranslatedAttribute)window.hsSetTranslatedAttribute(n,a,s);else n.setAttribute(a,tr(s));}
  function svg(type){
    var paths={
      search:'<circle cx="10" cy="10" r="7"></circle><path d="M21 21l-5-5"></path>',
      export:'<path d="M12 3v12M7 10l5 5 5-5M5 20h14"></path>',
      phone:'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z"></path>',
      message:'<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4zM7 8h10M7 12h7"></path>',
      send:'<path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"></path>'
    };return '<svg viewBox="0 0 24 24" aria-hidden="true">'+paths[type]+'</svg>';
  }
  function preparePanel(){
    var table=document.getElementById(cfg.id);if(!table)return null;
    var panel=table.closest('.panel');if(!panel)return table;
    panel.classList.add('hs-sms-list-panel');
    var col=panel.closest('[class*="col-md-"]');if(col)col.classList.add('hs-sms-list-column');
    var heading=panel.querySelector('.panel-heading'),title=panel.querySelector('.panel-title');
    if(title)setText(title,cfg.title);
    if(heading&&!heading.querySelector('.hs-sms-list-heading-icon')){var ico=document.createElement('span');ico.className='hs-sms-list-heading-icon';ico.innerHTML=svg(cfg.icon);heading.insertBefore(ico,heading.firstChild);}
    if(table.tHead&&table.tHead.rows[0])Array.prototype.forEach.call(table.tHead.rows[0].cells,function(cell,i){if(cfg.headers[i])setText(cell,cfg.headers[i]);});
    return table;
  }
  function mobileCards(table,api){
    var wrap=table.closest('.dataTables_wrapper');if(!wrap)return;
    var host=wrap.querySelector('.hs-sms-list-cards');if(!host){host=document.createElement('div');host.className='hs-sms-list-cards';var scroll=wrap.querySelector('.hs-sms-list-scroll');wrap.insertBefore(host,scroll||wrap.querySelector('.dataTables_info')||null);}
    host.textContent='';
    api.rows({search:'applied',order:'applied',page:'current'}).every(function(){
      var row=this.node();if(!row)return;var cells=Array.prototype.slice.call(row.cells),vals=cells.map(function(c){return c.textContent.replace(/\s+/g,' ').trim();});
      if(!vals.length)return;
      var card=document.createElement('article');card.className='hs-sms-list-card';var head=document.createElement('header'),headLabel=document.createElement('span'),headValue=document.createElement('strong');
      setText(headLabel,cfg.headers[0]);headValue.textContent=vals[0]||'—';head.appendChild(headLabel);head.appendChild(headValue);card.appendChild(head);
      var values=document.createElement('div');values.className='hs-sms-list-card-values';
      for(var i=1;i<cfg.headers.length&&i<vals.length;i++){var item=document.createElement('div'),label=document.createElement('span'),value=document.createElement('strong');if(cfg.wide.indexOf(i)!==-1)item.classList.add('hs-sms-list-card-wide');setText(label,cfg.headers[i]);var link=cells[i]&&cells[i].querySelector?cells[i].querySelector('a[href]'):null;if(link){var cloned=link.cloneNode(true);cloned.textContent=vals[i]||'—';value.appendChild(cloned);}else value.textContent=vals[i]||'—';item.appendChild(label);item.appendChild(value);values.appendChild(item);}
      card.appendChild(values);host.appendChild(card);
    });
    if(!host.children.length){var empty=document.createElement('p');setText(empty,'Nic nenalezeno');host.appendChild(empty);}
  }
  function localizeDataTableUi(table,api){
    var wrap=table&&table.closest?table.closest('.dataTables_wrapper'):null;if(!wrap)return;
    var filter=wrap.querySelector('.dataTables_filter'),input=filter&&filter.querySelector('input');
    if(input){setAttr(input,'placeholder',cfg.placeholder);setAttr(input,'aria-label',cfg.placeholder);}
    var exportButton=wrap.querySelector('.dt-buttons .buttons-collection');
    if(exportButton){var exportLabel=exportButton.querySelector('span');if(exportLabel)setText(exportLabel,'Export');}
    var previous=wrap.querySelector('.paginate_button.previous'),next=wrap.querySelector('.paginate_button.next'),first=wrap.querySelector('.paginate_button.first'),last=wrap.querySelector('.paginate_button.last');
    if(previous)setText(previous,'Předešlá');if(next)setText(next,'Další');if(first)setText(first,'První');if(last)setText(last,'Poslední');
    var empty=wrap.querySelector('td.dataTables_empty');if(empty)setText(empty,'Nic nenalezeno');
    var infoEl=wrap.querySelector('.dataTables_info');
    if(infoEl&&api&&api.page&&api.page.info){
      var info=api.page.info(),page=info.pages?info.page+1:0,pages=info.pages||0;
      while(infoEl.firstChild)infoEl.removeChild(infoEl.firstChild);
      var pageSpan=document.createElement('span');setText(pageSpan,'Strana '+page+' z '+pages);infoEl.appendChild(pageSpan);
      if(info.recordsDisplay<info.recordsTotal){var spacer=document.createTextNode(' ');infoEl.appendChild(spacer);var totalSpan=document.createElement('span');setText(totalSpan,'(Celkem '+info.recordsTotal+' záznamů)');infoEl.appendChild(totalSpan);}
    }
  }
  function enhance(){
    var table=preparePanel();if(!table||!window.jQuery||!jQuery.fn.dataTable||!jQuery.fn.dataTable.isDataTable(table)||table.dataset.hsSmsList)return;
    table.dataset.hsSmsList='1';var api=jQuery(table).DataTable(),wrap=table.closest('.dataTables_wrapper');if(!wrap)return;
    var toolbar=document.createElement('div');toolbar.className='hs-sms-list-toolbar';wrap.insertBefore(toolbar,wrap.firstChild);
    var filter=wrap.querySelector('.dataTables_filter'),buttons=wrap.querySelector('.dt-buttons');
    if(filter){toolbar.appendChild(filter);var label=filter.querySelector('label'),input=filter.querySelector('input');if(label&&input){Array.prototype.slice.call(label.childNodes).filter(function(n){return n.nodeType===3;}).forEach(function(n){n.remove();});var ico=document.createElement('span');ico.className='hs-sms-list-search-icon';ico.innerHTML=svg('search');label.insertBefore(ico,input);setAttr(input,'placeholder',cfg.placeholder);setAttr(input,'aria-label',cfg.placeholder);}}
    if(buttons){toolbar.appendChild(buttons);var exportButton=buttons.querySelector('.buttons-collection');if(exportButton){exportButton.innerHTML=svg('export')+'<span></span>';setText(exportButton.querySelector('span'),'Export');}}
    var oldScroll=table.closest('.dataTables_scroll');if(!oldScroll){
      var scroll=document.createElement('div');scroll.className='hs-sms-list-scroll';table.parentNode.insertBefore(scroll,table);scroll.appendChild(table);
      var top=document.createElement('div'),track=document.createElement('div');top.className='hs-sms-list-scroll-top';top.tabIndex=0;top.setAttribute('role','region');setAttr(top,'aria-label','Posun tabulky');top.appendChild(track);scroll.parentNode.insertBefore(top,scroll);
      top.addEventListener('scroll',function(){if(scroll.scrollLeft!==top.scrollLeft)scroll.scrollLeft=top.scrollLeft;});scroll.addEventListener('scroll',function(){if(top.scrollLeft!==scroll.scrollLeft)top.scrollLeft=scroll.scrollLeft;});
      function sizeScroll(){track.style.width=scroll.scrollWidth+'px';top.classList.toggle('hs-sms-list-scroll-unneeded',scroll.scrollWidth<=scroll.clientWidth+1);top.scrollLeft=scroll.scrollLeft;}
      if(window.ResizeObserver){var observer=new ResizeObserver(sizeScroll);observer.observe(table);observer.observe(scroll);}window.addEventListener('resize',sizeScroll);jQuery(table).on('draw.dt.hsSmsScroll',sizeScroll);requestAnimationFrame(sizeScroll);
    }
    localizeDataTableUi(table,api);mobileCards(table,api);jQuery(table).on('draw.dt.hsSmsCards',function(){localizeDataTableUi(table,api);mobileCards(table,api);});
  }
  preparePanel();
  if(window.jQuery)jQuery(document).on('init.dt.hsSmsLists',function(e,settings){if(settings&&settings.nTable&&settings.nTable.id===cfg.id)enhance();});
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',enhance);else enhance();
  window.addEventListener('load',function(){setTimeout(enhance,0);},{once:true});
  document.addEventListener('hs:languagechange',function(){var table=preparePanel();if(table&&window.jQuery&&jQuery.fn.dataTable.isDataTable(table)){var api=jQuery(table).DataTable();localizeDataTableUi(table,api);mobileCards(table,api);}});
}());
