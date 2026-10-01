# HairSoft Klient V125 – Sklad

Kompletní balíček navazující na V124. Vychází z dodaných Sklad.php a SkladXml.php.

## Změny

- Sjednocené písmo, nadpisy, zaoblené tabulky, výběr skladu, vyhledávání s ikonou a Export bez původního dodatku „pouze Web rozhraní“.
- Obě tabulky mají vlastní hledání podle názvu, PLU i dalších textů záznamu. Filtr ovlivňuje mobilní karty i exportované záznamy.
- Na mobilu a při otočení dotykového zařízení se tabulky mění na karty zboží se všemi původními sloupci. Šířka odpovídá tlačítku Export. Upravené stránkování.
- Předposlední sekce používá vzhled Správy dat. Původní formuláře, skryté hodnoty a potvrzení mazání zachovány.
- Patička používá společnou V124, včetně opravené ikony synchronizace.
- PDF má hlavičku HairSoft, pobočku a vybraný sklad, čitelné sloupce a číslování stránek. Zachovány Copy, CSV, XLS, PDF a Tisk; všechny skladové hodnoty zůstávají v exportu.
- Doplněny překlady popisků skladu a hledání.
- V původním Sklad.php opraven zdroj tabulky Aktuální stav zásob: používala omylem seznam chybějícího zboží. Nyní používá sklady_akt_stav_zasob, zatímco druhá tabulka nadále používá sklady_seznam_chyb_zbozi. Inicializováno počítadlo seznamu skladů.
- Zachován přechod z původního Skladu na SkladXml, pokud jsou dostupná XML data, a kontroly přístupu.

## Nahrání

Nahrajte složky hs-client-ui a str do kořene webu, potom Ctrl+F5. Původní str/strana/Sklad.php a SkladXml.php na serveru ponechte; společný vstup načte moderní varianty z hs-client-ui.

Při kompletní V124 stačí:

- hs-client-ui/php/strana/Sklad.php
- hs-client-ui/php/strana/SkladXml.php
- hs-client-ui/php/index.php
- hs-client-ui/js/stock.js
- hs-client-ui/js/stock-pdf.js
- hs-client-ui/css/stock.css
- hs-client-ui/js/i18n.js
- hs-client-ui/VERSION.txt

## Ověření

PHP/JS syntaxe a lokální modelové načtení obou PHP variant se zapnutou kontrolou varování. Chrome s původními DataTables: 375, 932 a 1366 px; hledání PLU, nenalezený výsledek, odstranění filtru, karty a export na stejnou šířku, společná patička, Správa dat a absence chyb JS. Samostatně prázdná klientská tabulka. Vygenerována PDF obou tabulek v obou variantách a vizuálně ověřen vzhled PDF. Živá databáze ani produkční server nebyly testovány nebo změněny. Mazání dat nebylo spouštěno.
