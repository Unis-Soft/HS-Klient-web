# HairSoft Klient V120 – sloupcové grafy ročních tržeb podle Dashboardu

Roční sloupcový graf nyní používá přímo společné vykreslování z Dashboardu: zaoblené vršky sloupců, barevný přechod, jemnou přerušovanou mřížku, formátování os a vlastní tooltip pro myš a dotyk. Zachovány jsou měsíce, výběr obsluhy, hodnoty služeb a prodeje a jejich legenda. Prázdný rok zůstává nulový bez tooltipu s fiktivními daty.

Kruhové a spojnicové grafy se nemění. Součástí balíčku zůstává oprava načítání z V119.

## Nahrání při kompletní V119

Stačí přepsat tyto tři soubory z balíčku:

- hs-client-ui/js/annual-revenue.js
- hs-client-ui/js/dashboard-charts.js
- hs-client-ui/php/index.php

Poté Ctrl+F5. Lze také nahrát celé složky hs-client-ui a str jako u předchozích verzí.

Lokálně ověřen společný renderer s Chart.js 2.3.0, gradienty a zaoblení, osy, tooltipy myší na PC a dotykem na mobilu, přepnutí jazyka, nulový rok a zachování počtu grafů. Změna nebyla nasazena na produkční server.

Doplnění V120: vnitřní šipky výběru roku jsou nyní SVG vycentrovaná v tlačítku, bez posunu způsobeného textovým znakem. Oproti první V120 navíc přepište hs-client-ui/php/strana/TrzbyDleObsluhy.php, hs-client-ui/css/annual-revenue.css a hs-client-ui/php/index.php.
