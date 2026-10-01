# V207 – skutečně ruční Timeline mimo produkční tabulku

V207 navazuje přímo na V206.

## Co bylo ve V206 chybně
- Historická Timeline TEST kopie se už při vytvoření kopie fyzicky vložila do běžné tabulky `timeline` a pouze dostala příznak `H`.
- To nebyla skutečná izolace. Legacy synchronizace HairSoft stav `H` zjevně nerespektuje spolehlivě a řádky mohla načíst ještě před ručním kliknutím.
- Modal `Dokončit synchronizaci do HairSoft` byl renderovaný jen pokud zákazník ještě neměl reálné HairSoft ID. Jakmile HairSoft ID doplnil sám, modal mohl zmizet dříve, než uživatel ID ručně potvrdil.
- Dohledání cílové TEST kopie bylo příliš svázané s owner kontextem aktuální session.

## V207
- Při NOVÉ TEST kopii se historická Timeline vůbec nevkládá do produkční tabulky `timeline`.
- Její snapshot se uloží do izolované tabulky `k_klient_customer_copy_timeline_stage`, kterou starý HairSoft nezná.
- HS Klient tyto čekající řádky stále zobrazí v běžné tabulce Timeline.
- Před ručním potvrzením HairSoft ID je synchronizační ikona neaktivní.
- `Dokončit synchronizaci do HairSoft` zůstane dostupné i když HairSoft už sám doplnil reálné ID; známé ID se předvyplní.
- Po potvrzení ID se uvolní fotografie a soubory. Timeline zůstává v izolovaném meziskladu.
- Klik na synchronizační ikonu konkrétního řádku teprve v ten okamžik vytvoří právě JEDEN skutečný řádek v `timeline` s běžným příznakem `N`.
- Ostatní historické záznamy zůstávají fyzicky mimo tabulku `timeline`, takže je HairSoft nemůže vzít dříve.
- Historické datum/text/obsluha se zachovávají; technické `timelineDoPCVlozeno` se nastaví na aktuální čas při kliknutí.
- Cílová kopie se nově dohledá primárně jako dříve a bezpečným fallbackem podle globálně unikátního `target_guid`; následně se vždy ověřuje oprávnění k cílové pobočce.

## Starší testovací kopie
V207 automaticky neopravuje Timeline, kterou už V202–V206 vložily do běžné tabulky nebo kterou už HairSoft zpracoval. Bez zpětného ACK nelze bezpečně určit, co desktop už převzal. Pro čistý test použijte novou kopii vytvořenou až po V207.

## Databáze
Přibývá pouze izolovaná tabulka `k_klient_customer_copy_timeline_stage`. Struktura produkční tabulky `timeline` se V207 nemění. Tabulku aplikace vytvoří při prvním použití; servisní SQL je v `sql/SQL_V207_TIMELINE_STAGING.sql`.
