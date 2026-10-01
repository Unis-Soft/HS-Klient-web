# HairSoft Klient V124 – společná patička

Navazuje na kompletní V123.

- Opravena geometrie synchronizační ikony a pevný čtvercový rozměr SVG.
- Schválený vzhled patičky je společný pro původní stavové patičky upravovaných sekcí: Dashboard, zákazníci, sklad, další přehledy a denní, měsíční, roční i celoživotní tržby.
- RezervaceBonfero je výslovně vyloučeno.
- KartaOsoby / Nový zákazník zobrazuje pouze dobu načtení, bez synchronizace a jejího intervalu.
- Nová patička nahrazuje původní panel, nikoliv přidává druhý. Přebírá datum, dobu načtení a původní interval automatického načítání. Chybějící synchronizace je pomlčka; interval se nezobrazuje, pokud jej původní stránka neuváděla.
- U hlavních přehledů bez původní patičky je k dispozici čas vykreslení změřený společným PHP vstupem. Čas na serveru nezahrnuje následné stahování prostředků v prohlížeči, stejně jako dosavadní údaj.
- Zachována lokalizace. Neprovádí nové dotazy do databáze ani nespouští novou synchronizaci.

## Nahrání

Nahrajte složky hs-client-ui a str do kořene webu a proveďte Ctrl+F5. Původní str/strana ponechte na serveru.
Při kompletní V123 stačí:

- hs-client-ui/js/page-status.js (nový)
- hs-client-ui/css/page-status.css (nový)
- hs-client-ui/php/index.php
- hs-client-ui/php/strana/TrzbyOdPocatku.php
- hs-client-ui/VERSION.txt

## Ověření

Kontrola PHP/JS syntaxe, dvacet modelových kontrol patiček v Chrome pro deset sekcí a dvě šířky (375 a 1366 px), včetně vyloučení bonfero, pouze času u zákazníka, zachování dat a odstranění původního panelu. Integrační kontrola celoživotních tržeb s ostatními skripty a exportem pro 375, 932 a 1366 px. Ikona a mobilní patička vizuálně zkontrolovány. Živý server nebyl testován ani změněn.
