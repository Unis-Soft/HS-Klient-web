# HairSoft Klient V113 – nulové grafy

V existujících grafech měsíčních tržeb se při prázdných datech zobrazí šedý prstenec s 0 Kč, u počtu účtenek s 0. Platí pro služby, prodej, dny týdne, účtenky a existující grafy typů plateb. Pokud nejsou známa žádná střediska či obsluhy, nevytvářejí se smyšlené legendy ani nové grafy neexistujících středisek.

Prázdný prstenec se kreslí přímo bez přidávání fiktivní hodnoty do dat. Tooltip je pro takový graf vypnutý. Původní zprávy o chybějících datech v grafových panelech jsou odstraněny. Sloupcový a čárový graf se vykreslují také v prázdném období, čárový graf bez datových sad dostává nulovou zobrazovací řadu za všechny dny období.

Změny oproti V112: hs-client-ui/php/strana/MesicniTrzby.php, hs-client-ui/js/monthly-revenue.js a hs-client-ui/php/index.php (cache verze měsíčního JS 113).

Ověření: PHP 8.3.33 a syntaxe JS; Chrome s Chart.js 2.3.0 na 1920/1366/768/375 px. Testy prázdných datových polí i existujících nulových hodnot, vykreslených prstenců, nulových součtů, vypnutých prázdných tooltipů a 31 nulových bodů časové řady. Regresní kontrola běžných grafů včetně tooltipů myší a dotykem. Použity lokální testovací stránky s ukázkovými daty, nikoli přihlášená produkční databáze.

Nahrajte složky hs-client-ui a str do kořene webu a přepište soubory. Při nasazené V112 stačí tři uvedené soubory. Poté Ctrl+F5.
