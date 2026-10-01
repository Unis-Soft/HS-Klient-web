/* HairSoft V124: shared page status; Bonfero is deliberately excluded. */
(function () {
  'use strict';
  var route = new URLSearchParams(location.search).get('strana') || 'AktivniPlocha';
  if (/bonfero/i.test(route)) return;
  var syncIcon = '<path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16M3 22v-6h6M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8M15 8h6V2"></path>';
  var icons = [syncIcon, '<circle cx="12" cy="12" r="9"></circle><path d="M12 8v4l3 2"></path>', '<path d="M5 17a8 8 0 1 1 14 0M12 13l4-5"></path>'];
  function text(node, value) { if(window.hsSetTranslatedText) window.hsSetTranslatedText(node,value); else node.textContent=value; }
  function item(label, value, icon, translateValue) {
    var node=document.createElement('div');node.className='hs-page-status__item';
    node.innerHTML='<span class="hs-page-status__icon" aria-hidden="true"><svg viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet">'+icons[icon]+'</svg></span><div><span class="hs-page-status__label"></span><strong></strong></div>';
    text(node.querySelector('.hs-page-status__label'),label);
    if(translateValue)text(node.querySelector('strong'),value);else node.querySelector('strong').textContent=value;
    return node;
  }
  function prepare() {
    var main=document.getElementById('main-content');if(!main||main.querySelector('.hs-page-status'))return;
    var source=main.querySelector('.hs-lifetime-status'), old=source, sync='', duration='', automatic=false;
    var timeOnly=route==='KartaOsoby'||route==='Dochazka';
    if(source) {
      var values=source.querySelectorAll('strong');sync=values[0]?values[0].textContent:'';duration=values[2]?values[2].textContent:'';automatic=true;
    } else {
      var candidates=main.querySelectorAll('font, .panel-body, p');
      for(var i=0;i<candidates.length;i++) {
        var value=candidates[i].textContent.replace(/\s+/g,' ').trim();
        if(value.length>650 || !/Strana načtena za|Stránka načítaná za|Page loaded in|Seite geladen in/.test(value))continue;
        if(candidates[i].querySelector('input,button,table,canvas'))continue;
        source=candidates[i];
        var date=value.match(/\d{1,2}\.\d{1,2}\.\d{4}\s+(?:v\s+)?\d{1,2}:\d{2}/), elapsed=value.match(/(?:Strana načtena za|Stránka načítaná za|Page loaded in|Seite geladen in)\s*([\d.,]+\s*s)/);
        sync=date?date[0]:'';duration=elapsed?elapsed[1]:'';automatic=/15\s*(?:minut|minút|minutes|Minuten)/.test(value);
        var voucherChange=(main.classList.contains('hs-voucher-page')||main.classList.contains('hs-feedback-stats'))&&/Poslední změna|Posledná zmena|Last change|Letzte Änderung/.test(value);
        timeOnly=timeOnly||!(/Synchroniz|Synchronized|Synchronisiert/.test(value)||voucherChange);
        var panel=source.closest('.panel');old=panel&&panel.textContent.trim()===source.textContent.trim()?panel:source;
        break;
      }
    }
    var supported=/^(AktivniPlocha|Zakaznici|KartaOsoby|CelkoveTrzby|MesicniTrzby|TrzbyDleObsluhy|TrzbyOdPocatku|Sklad|SkladXml|Voucher|VoucherHistorie|VoucherZnpeplatneny|SpokojenostStatistika|SpokojenostNastaveni|Sms|PrichoziHovory|PrichoziSMS|OdchoziSMS|Uzivatele|Dochazka|Cenik)$/.test(route);
    if(!source&&!supported)return;
    if(!duration && typeof window.hsPageRenderSeconds==='number')duration=window.hsPageRenderSeconds.toFixed(3).replace('.',',')+' s';
    var footer=document.createElement('footer');footer.className='hs-page-status';
    if(!timeOnly) {
      footer.appendChild(item(voucherChange?'Poslední změna:':'Synchronizováno:',sync||'—',0,false));
      // Preserve the original interval; do not invent a schedule where none was reported.
      if(automatic)footer.appendChild(item('Automatické načítání dat','Každých 15 minut',1,true));
    }
    footer.appendChild(item('Strana načtena za',duration||'—',2,false));
    footer.style.setProperty('--hs-status-columns',footer.children.length);
    if(old)old.replaceWith(footer);else main.appendChild(footer);
  }
  if(document.readyState==='complete')setTimeout(prepare,0);else window.addEventListener('load',function(){setTimeout(prepare,0);},{once:true});
}());

