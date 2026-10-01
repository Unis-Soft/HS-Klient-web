(function(){'use strict';
var route=new URLSearchParams(location.search).get('strana')||'';if(route!=='Dochazka')return;
var tr=window.hsTranslate||function(s){return s;};
function translateUi(root){
 var exportBtn=root&&root.querySelector('.hs-attendance-toolbar .buttons-collection');
 if(exportBtn){exportBtn.innerHTML='<i class="fa fa-download" aria-hidden="true"></i><span>'+tr('Export')+'</span>';}
}
function buildCards(table,api){
 var host=document.querySelector('.hs-attendance-mobile-cards');if(!host)return;host.textContent='';
 var headers=Array.prototype.map.call(table.querySelectorAll('thead th'),function(n){return n.textContent.trim();});
 api.rows().every(function(){var node=this.node();if(!node)return;var cells=node.querySelectorAll('td');if(!cells.length)return;var card=document.createElement('article');card.className='hs-attendance-mobile-card';var head=document.createElement('div');head.className='hs-attendance-mobile-card__head';head.innerHTML='<span>'+tr('Den')+'</span><strong>'+cells[0].textContent.trim()+'</strong>';card.appendChild(head);var body=document.createElement('div');body.className='hs-attendance-mobile-card__body';for(var i=1;i<cells.length;i++){var row=document.createElement('div');row.className='hs-attendance-mobile-card__row';row.innerHTML='<span class="hs-attendance-mobile-card__label"></span><strong class="hs-attendance-mobile-card__value"></strong>';row.querySelector('.hs-attendance-mobile-card__label').textContent=headers[i]||'';row.querySelector('.hs-attendance-mobile-card__value').textContent=cells[i].textContent.trim()||'—';body.appendChild(row);}card.appendChild(body);host.appendChild(card);});
 var foot=table.querySelector('tfoot tr');if(foot){var cells=foot.querySelectorAll('th,td');var totalCard=document.createElement('article');totalCard.className='hs-attendance-mobile-card hs-attendance-mobile-card--total';var totalHead=document.createElement('div');totalHead.className='hs-attendance-mobile-card__head';totalHead.innerHTML='<strong>'+tr('Celkem')+'</strong>';totalCard.appendChild(totalHead);var totalBody=document.createElement('div');totalBody.className='hs-attendance-mobile-card__body';for(var j=1;j<cells.length;j++){var totalRow=document.createElement('div');totalRow.className='hs-attendance-mobile-card__row';totalRow.innerHTML='<span class="hs-attendance-mobile-card__label"></span><strong class="hs-attendance-mobile-card__value"></strong>';totalRow.querySelector('.hs-attendance-mobile-card__label').textContent=headers[j]||'';totalRow.querySelector('.hs-attendance-mobile-card__value').textContent=cells[j].textContent.trim()||'—';totalBody.appendChild(totalRow);}totalCard.appendChild(totalBody);host.appendChild(totalCard);}
}
function prepare(){
 var main=document.getElementById('main-content');var table=document.getElementById('dochazka_tabulka');if(!main||!table||!window.jQuery||!jQuery.fn.DataTable)return;
 var api=jQuery.fn.DataTable.isDataTable(table)?jQuery(table).DataTable():null;if(!api)return;
 var wrapper=table.closest('.dataTables_wrapper');var toolbar=main.querySelector('.hs-attendance-toolbar');
 if(wrapper&&toolbar&&!toolbar.dataset.hsReady){toolbar.dataset.hsReady='1';var buttons=wrapper.querySelector('.dt-buttons');if(buttons)toolbar.appendChild(buttons);}
 translateUi(main);buildCards(table,api);
 jQuery(table).off('draw.dt.hsAttendance').on('draw.dt.hsAttendance',function(){buildCards(table,api);translateUi(main);});
 main.classList.add('hs-attendance-ready');
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',function(){setTimeout(prepare,0);});else setTimeout(prepare,0);
window.addEventListener('load',function(){setTimeout(prepare,0);});
document.addEventListener('hs:languagechange',function(){setTimeout(function(){var t=document.getElementById('dochazka_tabulka');if(t&&window.jQuery&&jQuery.fn.DataTable.isDataTable(t)){translateUi(document.getElementById('main-content'));buildCards(t,jQuery(t).DataTable());}},0);});
}());
