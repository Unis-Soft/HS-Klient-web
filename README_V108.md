# HairSoft Klient V108

Opravy proti dodané V107:

- Navigace používá konkrétní měsíc a rok. Obnovení již neopakuje posun. Adresa uchovává vybrané období i při změně session v jiném okně. Staré relativní adresy zobrazí aktuální období ze session; nové šipky používají absolutní výběr.
- Zachována kontrola dostupnosti ročních tabulek a původní samostatné ovládání měsíce a roku (prosinec/leden nemění rok).
- Sloupcové i čárové grafy používají režim label podporovaný Chart.js 2.3.0. Zobrazení legendy, tooltipy myší i dotykem a samostatný kontejner pro sloupcový graf.
- Kruhové grafy mají čtvercový kontejner a shodné proporce kreslicí plochy. Rozložení vychází z denních tržeb: prstenec 72 %, stejná paleta, částka uprostřed, legenda se při nedostatku místa skládá nad graf. Bez překrývání částek a grafů.
- Před změnou rozměrů se zastaví původní animace, aby starší Chart.js nekreslil neúplně přepočítanou legendu.
- Zachován Open Sans s náhradními bezpatkovými fonty.

Ověření:

- PHP 8.3.33: kontrola syntaxe obou změněných PHP souborů.
- Běhové testy skutečného bloku výběru období v PHP: 5 obnovení, změna session jiným oknem, neplatné vstupy, nedostupný rok, přechody leden/prosinec a prázdná session. Databázová dostupnost simulována.
- Chrome se skutečnou veřejnou knihovnou Chart.bundle.js 2.3.0 z klient.hairsoft.cz: šířky 1920, 1366, 768 a 375 px; kontrola kruhových proporcí, nepřekrytých legend, tooltipů myší i dotykem, počtu instancí a chyb JavaScriptu. Použita testovací stránka se strukturou měsíčních panelů a ukázkovými daty.

Omezení: nejde o test přihlášené produkční stránky proti skutečné databázi. Balíček nebyl nasazen na server. Historické README_V107.md a KLIENT_HANDOFF_V107.txt jsou přiloženy jako záznam předchozí verze.
