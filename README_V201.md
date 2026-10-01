# V201 – Soubory a technické datum Timeline při kopii firmy

Navazuje přímo na V200.

## 1. Soubory – návrat po uploadu
- Upload formulář na kartě zákazníka má nově explicitní cílovou URL s `NavratSoubory=1`.
- Po nahrání souboru se proto karta vrátí do záložky **Soubory**, i když byla původní URL otevřena s `NavratTimeline=1`.
- Stav záložek se na začátku požadavku explicitně inicializuje a `NavratSoubory=1` má při konfliktu prioritu.

## 2. Soubory – synchronizační stav
- Nově nahraný soubor se zapisuje do `soubory` explicitně se `stav=0`.
- Význam zůstává podle existujícího HS Klient: `0` = čeká / nestaženo, `1` = staženo HairSoftem, `9` = smazáno.
- Fyzické uložení souboru, povolené typy, limit 30 MB a mazání zůstávají beze změny.

## 3. Timeline při TEST kopii A → B
- Historické `timelineDatumCas` se **nemění**. Datum, které uživatel vidí u původní poznámky, zůstane původní.
- Do tabulky `timeline` se doplňuje nový nullable sloupec `timelineDoPCVlozeno`.
- Každý Timeline řádek vytvořený kopírováním zákazníka dostane:
  - `timelineDatumCas` = původní historické datum,
  - `timelineDoPCVlozeno` = `CURRENT_TIMESTAMP` v okamžiku kopie,
  - `timelineDoPCZnak` = `N`,
  - `timelineDoPCSynchro` = `NULL`,
  - `timelineIDHS` = `NULL`,
  - `timelineSwID` = cílová pobočka,
  - `timelineValid` = `1`.
- Sloupec se při první TEST kopii bezpečně ověří a případně vytvoří **před zahájením transakce**, takže DDL nerozbije rollback zákazníka/timeline/fotek.
- Ruční servisní SQL je přiložen v `sql/SQL_V201_TIMELINE_SYSTEMOVE_DATUM.sql`.

## Důležité omezení synchronizace
V projektu HS Klient nemáme zdroj synchronizačního EXE/API HairSoftu. V201 tedy vytváří správně oddělené technické datum, ale teprve test ukáže, zda současná synchronizace tento nový údaj umí použít. Pokud starý synchronizační proces filtruje Timeline jen podle `timelineDatumCas`, bude nutná malá změna na jeho straně. Viditelné historické datum kvůli tomu V201 záměrně nepřepisuje.

## Galerie
Produkční `NahratSoubor.php`, který byl dodán k analýze, už při nové fotografii zapisuje `obrazek_stazeno=0`. Galerie proto ve V201 nemění svůj příznak ani uploader; problém přenosu fotografie je za webovým uložením a bude se řešit až se synchronizační částí HairSoftu.

## Beze změny
- zdrojová firma A při TEST kopii,
- základní zákazník a jeho nový GUID v B,
- kopie fotografií a rollback V195/V198,
- multi-firma V198,
- vzhled V200,
- Hodnocení / Google / dotaznik.net.
