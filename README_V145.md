# HairSoft Klient V145

V145 navazuje na V144 a dokončuje společný vzhled sekce **SMS a hovory** pro stránky **Příchozí hovory**, **Příchozí SMS** a **Odchozí SMS**.

## Přehled SMS a hovorů
- odstraněn samostatný informační pruh „Data SMS a hovorů jsou načtena z aktuální databáze“,
- ponechána pouze společná spodní stavová patička HairSoft používaná napříč systémem,
- KPI karty ani jejich globální logika se nemění.

## Příchozí hovory / Příchozí SMS / Odchozí SMS
- horní KPI karty a původní databázové součty zůstávají beze změny,
- původní spodní informace o poslední změně zůstává zdrojem pro společnou globální patičku,
- tabulky mají jednotný vzhled podle Voucherů: zaoblený panel, čistou hlavičku, střídání řádků a stejné spodní stránkování,
- všechny tři tabulky mají moderní pole Hledat a společné tlačítko Export,
- široké tabulky používají samostatný horizontální posuv pouze kolem tabulky, takže nezasahuje do stránkování,
- Odchozí SMS mají na desktopu dostatečnou minimální šířku pro všech 11 sloupců,
- na mobilu se aktuální stránka DataTables převádí do čitelných karet bez změny zdrojových dat,
- doplněny překlady CZ / SK / EN / DE pro názvy stránek, karty, nadpisy tabulek, sloupce a vyhledávání.

## Export a PDF
- Copy / Excel / CSV / PDF / Tisk zůstávají dostupné v jedné nabídce Export,
- PDF používá stejný reportový systém jako hotové Vouchery a Tržby: HairSoft hlavičku, sekci SMS a hovory, pobočku, datum vytvoření a číslovanou patičku,
- Příchozí hovory a Příchozí SMS používají čitelnou A4 tabulku,
- široký export Odchozích SMS používá A4 na šířku a skládá jednotlivé zprávy do dvousloupcových reportových karet, aby se všech 11 údajů vešlo čitelně,
- Excel export obsahuje stejné bezpečnostní ošetření OOXML stylů jako Vouchery.

## Technicky
- přidány `hs-client-ui/css/sms-lists.css`, `hs-client-ui/js/sms-lists.js` a `hs-client-ui/js/sms-lists-pdf.js`,
- upraveny pouze DataTables konfigurace tří SMS/call tabulek v moderním `index.php`,
- legacy PHP stránky a jejich SQL dotazy nejsou přepisovány; moderní vrstva dál využívá původní fallback architekturu,
- databáze beze změny.

Nahrajte obsah balíčku na server stejně jako předchozí verze. Původní soubory nemažte. Po nasazení proveďte Ctrl+F5.
