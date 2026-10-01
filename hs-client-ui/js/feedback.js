/* V137: presentation only; retain server calculations and original form actions. */
(function(){'use strict';
 var main=document.querySelector('.hs-feedback-page');if(!main)return;
 var palette=['#15a5a0','#536e97','#8878d6','#db628a','#509dc6','#67ad83'];
 function tr(s){return window.hsTranslate?window.hsTranslate(s):s;}
 function label(n,s){if(window.hsSetTranslatedText)window.hsSetTranslatedText(n,s);else n.textContent=tr(s);}
 main.querySelectorAll('img[src*="/star"]').forEach(function(img){var star=document.createElement('span');star.className='hs-feedback-star';star.setAttribute('aria-hidden','true');img.replaceWith(star);});
 var people=main.querySelector('.hs-feedback-people');if(people&&!people.querySelector('.hs-feedback-person')){var emptyPeople=document.createElement('p');label(emptyPeople,'Bez hodnocení');people.appendChild(emptyPeople);}
 var configs=[];var categories=null;
 function configure(c,i,portrait){if(!c||!c.data||!c.data.datasets)return;var empty=!portrait&&c.options.tooltips.enabled===false;
  c.options.responsive=true;c.options.maintainAspectRatio=false;c.options.cutoutPercentage=portrait?84:76;
  c.options.animation={duration:window.matchMedia('(prefers-reduced-motion: reduce)').matches?0:450};
  c.options.tooltips={enabled:!empty,bodyFontFamily:'Tahoma, Arial, sans-serif',titleFontFamily:'Tahoma, Arial, sans-serif',backgroundColor:'#29415c',cornerRadius:8,callbacks:{label:function(item,data){return data.labels[item.index]+': '+Number(data.datasets[item.datasetIndex].data[item.index]).toLocaleString(document.documentElement.lang||'cs',{maximumFractionDigits:2})+' / 5';}}};
  c.data.datasets.forEach(function(d){d.backgroundColor=[empty?'#eaf1f5':palette[i%palette.length],'#eaf1f5'];d.borderColor='#fff';d.borderWidth=2;});
  configs.push({config:c,labels:c.data.labels.slice()});
  return empty;
 }
 for(var i=1;i<=6;i++){
  var canvas=main.querySelector('#chart-area'+i);if(!canvas)continue;
  var empty=configure(window['config'+i],i-1,false);
  var host=document.createElement('div');host.className='hs-feedback-chart';canvas.parentNode.insertBefore(host,canvas);host.appendChild(canvas);canvas.style.removeProperty('display');
  var panel=host.closest('.panel');var column=panel&&panel.parentElement;
  if(column){if(!categories){categories=document.createElement('div');categories.className='hs-feedback-categories';column.parentNode.insertBefore(categories,column);}categories.appendChild(column);}
  var title=panel&&panel.querySelector('.panel-title');if(title){var parts=title.textContent.trim().split(':');if(parts.length===2){title.textContent='';var titleLabel=document.createElement('span');label(titleLabel,parts[0].trim());var score=document.createElement('span');score.className='hs-feedback-score';score.textContent=parts[1].trim();var star=document.createElement('span');star.className='hs-feedback-star';star.setAttribute('aria-hidden','true');score.appendChild(star);title.appendChild(titleLabel);title.appendChild(score);}}
  var center=document.createElement('div');center.className='hs-feedback-center';
  if(empty){var note=document.createElement('span');note.className='hs-feedback-empty';label(note,'Nehodnoceno');center.appendChild(note);}
  var reaction=panel&&panel.querySelector('center');
  if(reaction){var count=(reaction.textContent.match(/:\s*([\d\s.,]+)/)||[])[1]||'0';var line=document.createElement('span');line.className='hs-feedback-reactions';var caption=document.createElement('span');label(caption,'Reakce');line.appendChild(caption);line.appendChild(document.createTextNode(' '+count.trim()));center.appendChild(line);reaction.remove();panel.querySelectorAll('.panel-body br').forEach(function(br){br.remove();});}
  host.appendChild(center);
 }
 var table=main.querySelector('#TabulkaHodnoceni');var heading=table&&table.closest('.panel').querySelector('.panel-heading');var actions=heading&&heading.querySelector('.actions');
 if(actions){var controls=Array.from(actions.children).filter(function(n){return n.matches('a,span');});if(controls.length===6){var nav=document.createElement('nav');nav.className='hs-feedback-period';
  for(var group=0;group<2;group++){var segment=document.createElement('div');segment.className='hs-feedback-period__segment';controls.slice(group*3,group*3+3).forEach(function(n,i){if(n.tagName==='A'){var description=group===0?(i===0?'Předchozí měsíc':'Následující měsíc'):(i===0?'Předchozí rok':'Následující rok');if(window.hsSetTranslatedAttribute)hsSetTranslatedAttribute(n,'aria-label',description);n.innerHTML='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="'+(i===0?'m14 5-7 7 7 7':'m10 5 7 7-7 7')+'"/></svg>';}segment.appendChild(n);});nav.appendChild(segment);}heading.after(nav);
  var clean=new URL(location.href);clean.searchParams.delete('mesic');clean.searchParams.delete('rok_zmena');history.replaceState(history.state,'',clean.href);
 }}
 // Keep the static question label translatable separately from its number.
 if(table)table.querySelectorAll('thead th').forEach(function(cell){var match=cell.textContent.trim().match(/^Otázka\s*(\d+)$/);if(match){cell.textContent='';var caption=document.createElement('span');label(caption,'Otázka');cell.appendChild(caption);cell.appendChild(document.createTextNode(' '+match[1]));}});
 main.querySelectorAll('.hs-feedback-person center').forEach(function(n){if(n.querySelector('.hs-feedback-star'))n.classList.add('hs-feedback-person-score');});
 main.querySelectorAll('canvas[id^="chart-area-obsluha"]').forEach(function(canvas,i){configure(window['config_obsluha'+canvas.id.replace('chart-area-obsluha','')],i,true);});
 function translateCharts(){configs.forEach(function(c){c.config.data.labels=c.labels.map(tr);});if(window.Chart&&Chart.instances)Object.keys(Chart.instances).forEach(function(k){var chart=Chart.instances[k];if(chart.chart&&chart.chart.canvas&&main.contains(chart.chart.canvas))chart.update(0);});}
 var archive=main.querySelector('#tlacitkoArchivace');if(archive)archive.addEventListener('click',function(){setTimeout(function(){if(window.Chart&&Chart.instances)Object.keys(Chart.instances).forEach(function(k){var c=Chart.instances[k];if(c.chart&&main.contains(c.chart.canvas))c.resize();});},0);});
 translateCharts();document.addEventListener('hs:languagechange',translateCharts);
 main.querySelectorAll('.hs-feedback-photo-img').forEach(function(img){img.addEventListener('error',function(){if(img.dataset.hsFallback)return;img.dataset.hsFallback='1';img.src='../img/obsluhy/no_image.jpg';});});
 // A second scrollbar above a wide ratings table uses the same scroll position.
 function scrollbars(){var table=main.querySelector('#TabulkaHodnoceni');if(!table)return;var host=table.closest('.hs-ratings-table-scroll');if(!host)return;
  var top=document.createElement('div'),space=document.createElement('div');top.className='hs-feedback-top-scroll';top.appendChild(space);host.parentNode.insertBefore(top,host);
  top.addEventListener('scroll',function(){host.scrollLeft=top.scrollLeft;});host.addEventListener('scroll',function(){top.scrollLeft=host.scrollLeft;});
  function size(){space.style.width=host.scrollWidth+'px';top.style.display=host.scrollWidth>host.clientWidth+1?'block':'none';}
  if(window.ResizeObserver)new ResizeObserver(size).observe(host);if(window.jQuery)jQuery(table).on('draw.dt',size);size();
 }
 window.addEventListener('load',function(){setTimeout(scrollbars,100);});
}());

/* V139: keep the search and export outside the question table's scroll area. */
(function(){
 function prepare(){
  var table=document.querySelector('.hs-feedback-settings #TabulkaSeznamOtazek');if(!table||!table.closest('.dataTables_wrapper'))return;
  if(!table.parentNode.classList.contains('hs-settings-table-scroll')){var scroll=document.createElement('div');scroll.className='hs-settings-table-scroll';table.parentNode.insertBefore(scroll,table);scroll.appendChild(table);}
  var button=document.querySelector('#TabulkaSeznamOtazek_wrapper>.dt-buttons>.buttons-collection');if(button&&!button.dataset.hsCompact){button.dataset.hsCompact='1';button.textContent='Export';if(window.hsSetTranslatedText)hsSetTranslatedText(button,'Export');}
 }
 if(document.readyState==='complete')prepare();else window.addEventListener('load',prepare);
 if(window.jQuery)jQuery(document).on('init.dt',prepare);
}());

/* V141: after loading an existing question, reveal its first editable row. */
(function(){
 function revealQuestion(){
  var marker=document.querySelector('.hs-feedback-settings form>[name="ZalozitNovouOtazku"]');
  if(!marker)return;
  var form=marker.form,id=form.querySelector('[name="HodnoceniID"]'),field=form.querySelector('input[name="HodnoceniVeta"]');
  if(!id||!id.value.trim()||!field)return;
  requestAnimationFrame(function(){requestAnimationFrame(function(){
   var row=field.closest('.form-group')||field,header=document.getElementById('header'),offset=24;
   if(header){var box=header.getBoundingClientRect(),position=getComputedStyle(header).position;if((position==='fixed'||position==='sticky')&&box.bottom>0)offset+=box.bottom;}
   window.scrollTo({top:Math.max(0,window.scrollY+row.getBoundingClientRect().top-offset),behavior:'auto'});
  });});
 }
 if(document.readyState==='complete')revealQuestion();else window.addEventListener('load',revealQuestion,{once:true});
}());

/* V183: persisted SMS destination switch. Google is an SMS-only parallel branch. */
(function(){
 'use strict';
 var root=document.querySelector('.hs-feedback-settings [data-hs-sms-design]');
 if(!root)return;
 var buttons=Array.from(root.querySelectorAll('[data-hs-sms-mode]'));
 var panes=Array.from(root.querySelectorAll('[data-hs-sms-pane]'));
 var googleText=root.querySelector('#hsGoogleReviewSms');
 var googleDefault=root.querySelector('#hsGoogleDefaultTemplate');
 var defaultGoogle='Dobrý den, děkujeme za Vaši návštěvu %DATUM_NAVSTEVY%. Budeme rádi, když nás ohodnotíte na Google: %ODKAZ_SPOKOJENOST% %JMENO_PROVOZOVNY%';
 function activate(mode){
  if(mode!=='google')mode='internal';
  buttons.forEach(function(button){var active=button.getAttribute('data-hs-sms-mode')===mode;button.classList.toggle('is-active',active);button.setAttribute('aria-pressed',active?'true':'false');});
  panes.forEach(function(pane){var active=pane.getAttribute('data-hs-sms-pane')===mode;pane.hidden=!active;pane.classList.toggle('is-active',active);});
 }
 buttons.forEach(function(button){button.addEventListener('click',function(){activate(button.getAttribute('data-hs-sms-mode'));});});
 if(googleDefault&&googleText)googleDefault.addEventListener('click',function(){googleText.value=defaultGoogle;googleText.focus();});
 activate(root.getAttribute('data-hs-sms-initial')==='google'?'google':'internal');
}());

/* V217: match question-list height to the natural editor height on desktop. */
(function(){
 'use strict';
 var row=document.querySelector('.hs-feedback-settings .hs-feedback-settings-pair--questions');
 if(!row)return;
 var side=row.querySelector('.hs-feedback-settings-pair__side');
 var panel=side&&side.querySelector(':scope > .panel');
 if(!panel)return;
 var media=window.matchMedia('(min-width: 992px)');
 var pending=0;
 function equalize(){
  cancelAnimationFrame(pending);
  pending=requestAnimationFrame(function(){
   row.classList.remove('is-equalized');
   row.style.removeProperty('--hs-feedback-questions-panel-height');
   if(!media.matches)return;
   requestAnimationFrame(function(){
    var height=Math.ceil(panel.getBoundingClientRect().height);
    if(height>0){
     row.style.setProperty('--hs-feedback-questions-panel-height',height+'px');
     row.classList.add('is-equalized');
    }
   });
  });
 }
 if(document.readyState==='complete')equalize();else window.addEventListener('load',equalize,{once:true});
 window.addEventListener('resize',equalize);
 if(media.addEventListener)media.addEventListener('change',equalize);else if(media.addListener)media.addListener(equalize);
 if(window.jQuery)jQuery(document).on('init.dt',function(e,settings){if(settings&&settings.nTable&&settings.nTable.id==='TabulkaSeznamOtazek')equalize();});
}());
