# HairSoft Klient V123 – Meziroční změny a nová patička

Navazuje na kompletní V122. Změny pouze pro Tržby od počátku věků:

- Zelená šipka nahoru pro kladnou změnu, červená dolů pro zápornou. Procento zůstává viditelné. Nulová změna je neutrální, nezjistitelné procento zůstává pomlčka.
- Stejné indikátory v mobilních kartách. Exportované procentní hodnoty zůstávají beze změny.
- Nová patička s ikonami: synchronizace, automatické načítání každých 15 minut a doba načtení. Obsah vychází z ročních tržeb. Na PC vedle sebe, na mobilu pod sebou.
- Čas načtení je měřen na serveru jako u ročních tržeb; nevytváří nový časovač automatického načítání. Při neznámém datu synchronizace se zobrazí pomlčka. Roční patička ani ostatní stránky se nemění.

## Nahrání

Nahrajte složky hs-client-ui a str do kořene webu a proveďte Ctrl+F5. Původní soubory str/strana na serveru ponechte.
Při kompletní V122 stačí:

- hs-client-ui/php/strana/TrzbyOdPocatku.php
- hs-client-ui/js/lifetime-revenue.js
- hs-client-ui/css/lifetime-revenue.css
- hs-client-ui/js/i18n.js
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

## Ověření

PHP/JavaScript syntaxe, modelový běžný a prázdný stav. Chrome v šířkách 375, 932 a 1366 px: kladná, záporná, nulová a neurčitelná změna, tabulka i mobilní karty, export procent, patička bez přetečení. Vizuálně zkontrolováno. Na živý server nebylo nasazeno.
