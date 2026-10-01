# HairSoft Klient V118 – Roční tržby

Roční tržby používají stejný vzhled a chování jako dokončené Měsíční tržby V117. Základem roční stránky je dodaný TrzbyDleObsluhy.php; výpočty, přístupová práva a formuláře pro načtení a odstranění dat jsou zachovány.

## Co je připravené

- Sjednocené horní karty, nadpisy, tabulka, legendy a grafy.
- Vycentrovaný výběr roku pod horními kartami, bez nadbytečných nadpisů. Odkazy obsahují konkrétní rok; obnovení stránky jej neposouvá.
- Výběr konkrétní obsluhy pro měsíční přehled služeb a prodeje. Neplatný výběr se upraví na existující obsluhu.
- Sloupcový graf, vývoj služeb a prodeje všech obsluh, souhrnné kruhové grafy, vývoj účtenek středisek a rozdělení plateb.
- Správně kruhové grafy, součty uprostřed a tooltipy pro myš i dotyk. Data se předávají bezpečně jako JSON, včetně názvů s apostrofem.
- Prázdný rok má nulové grafy, nulové horní částky a jednotné upozornění u tabulky. Nevzniká fiktivní podíl v grafu ani datum nejlepšího měsíce.
- Mobilní karty obsluh přes celou vnitřní šířku jako Export, zaoblení bez původního hranatého obalu. Široké tabulky a časové grafy mají vlastní vodorovný posuv.
- Na telefonu jsou ovládací ikony maximalizace, sbalení a zavření panelu skryté i v prázdném roce a při otočení telefonu.
- Tabulka zůstává do dokončení úpravy skrytá, aby neproblikl původní vzhled. Správa dat je rovnou umístěna dole.
- Export PDF ve stylu měsíčních tržeb: samostatné bloky obsluh, všech pět částek s DPH i bez DPH, měna, rok, pobočka a číslování stran. Zachovány CSV, XLSX, kopírování a tisk.
- Překlady ročních prvků pro češtinu, slovenštinu, angličtinu a němčinu.

## Nahrání

Nahrajte složky `hs-client-ui` a `str` z tohoto balíčku do kořene webu, stejně jako u předchozích verzí. Poté obnovte stránku pomocí Ctrl+F5.

Pokud je na serveru kompletní V117, stačí těchto šest souborů:

1. `hs-client-ui/php/strana/TrzbyDleObsluhy.php`
2. `hs-client-ui/css/annual-revenue.css`
3. `hs-client-ui/js/annual-revenue.js`
4. `hs-client-ui/js/annual-revenue-pdf.js`
5. `hs-client-ui/js/i18n.js`
6. `hs-client-ui/php/index.php`

Nový roční PHP soubor patří do uvedené složky moderní vrstvy. Není potřeba přepisovat původní soubor ve `str/strana`; stávající směrování načte moderní variantu.

Soubory denních a měsíčních tržeb jsou shodné s V117. Ve společném indexu přibylo načtení ročních stylů a skriptů, roční PDF export a označení stránky pro mobilní ovládací prvky; společný slovník získal roční překlady.

## Ověření a rozsah

Lokálně ověřeno s PHP 8.3.33 a skutečnou knihovnou Chart.js 2.3.0 používanou webem. Testy s připravenými databázovými výsledky pokryly naplněný a prázdný rok, demo, změnu roku a obsluhy, neplatné parametry, součty a všech 12 měsíců. Běh s E_ALL je bez varování.

V prohlížeči ověřeny šířky 375, 932, 1366 a 1920 px, kruhový poměr grafů, nulové stavy, šířka mobilních karet, tooltipy, opakované překreslení tabulky, přepínání jazyků a absence chyb JavaScriptu. Ověřeny skutečné exporty PDF, CSV a XLSX; čtyřstránkové PDF bylo také vizuálně zkontrolováno.

Balíček nebyl nasazen na produkční server. Výsledky se skutečnou produkční databází je potřeba ověřit po nahrání.
