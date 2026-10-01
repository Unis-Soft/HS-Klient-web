# V202 – dvoufázová synchronizace zákazníka a jeho dat

V202 navazuje přímo na V201 a řeší nové zjištění z reálného HairSoft PC: fotografie i dokumenty z HS Klient se fyzicky stáhly, ale u nového zákazníka skončily ve složce odpovídající dočasnému ID `11111111` místo skutečného lokálního HairSoft ID (např. `796`).

## Příčina
Nový zákazník založený z HS Klient používá legacy hodnotu:

- `klient_lidi.lidi_hs_id = 11111111`
- `klient_lidi.lidi_web_pc = N`

Hodnota `11111111` je pouze dočasný identifikátor do okamžiku, než HairSoft zákazníka vytvoří ve své lokální databázi a vrátí webu skutečné lokální ID.

V předchozí verzi byly Timeline, fotografie a soubory označené ke stažení okamžitě. Synchronizace je proto mohla převzít ještě před návratem skutečného ID zákazníka a vytvořit jejich lokální složku pod dočasným ID.

## Řešení V202 – dvě fáze
### Fáze 1 – nejprve pouze zákazník
Dokud má zákazník `lidi_hs_id = 11111111`, návazná data jsou v HS Klient viditelná, ale nejsou nabízena HairSoftu:

- Timeline: `timelineDoPCZnak = H` (HOLD)
- fotografie: `obrazek_stazeno = 2` (HOLD)
- soubory: `stav = 2` (HOLD)

Samotný zákazník zůstává standardně `lidi_web_pc = N` a může být vytvořen existující synchronizací HairSoft.

### Fáze 2 – HairSoft vrátí skutečné ID
Jakmile `klient_lidi.lidi_hs_id` obsahuje reálné kladné ID jiné než `11111111` (například `796`), V202 automaticky uvolní návazná data:

- Timeline `H -> N`
- fotografie `2 -> 0`
- soubory `2 -> 0`

Při tomto uvolnění se u Timeline nastaví `timelineDoPCVlozeno = CURRENT_TIMESTAMP`. Historické datum `timelineDatumCas`, které uživatel vidí u poznámky, zůstává beze změny.

HairSoft tedy dostane návazná data až v dalším synchronizačním cyklu, kdy už je známé správné lokální ID zákazníka.

## Automatické uvolnění
V202 používá malou izolovanou tabulku `k_klient_customer_sync_hold`. Neprohledává při každém požadavku celé tabulky Timeline/fotek/souborů.

Uvolnění probíhá:

1. při běžném načtení HS Klient,
2. okamžitě při otevření karty konkrétního zákazníka,
3. pomocí lehkého AJAX heartbeat každých 30 sekund, dokud je HS Klient otevřený.

Heartbeat zpracuje nejen aktuální firmu, ale i další HairSoft firmy bezpečně uložené v novém multi-firma switchi. Kopie A -> B tedy může čekat na ID firmy B, i když uživatel zůstane zrovna ve firmě A.

## TEST kopie zákazníka A -> B
Kopie mezi firmami nyní vytváří:

- cílového zákazníka s novým GUID a dočasným `lidi_hs_id=11111111`,
- historickou Timeline rovnou v HOLD stavu `H`,
- fyzicky zkopírované fotografie rovnou v HOLD stavu `2`,
- záznam ve frontě čekající na skutečné HairSoft ID.

Zdrojová firma A zůstává stále úplně beze změny.

## Běžně nový zákazník v HS Klient
Stejná ochrana platí i mimo cross-company kopii:

- nová Timeline před prvním HairSoft ID se drží jako `H`,
- nová fotografie se drží jako `obrazek_stazeno=2`,
- nový dokument se drží jako `stav=2`,
- editace zákazníka před prvním syncem, včetně zdravotních údajů, ponechá `lidi_web_pc=N` místo předčasného přepnutí na `U`.

Tím se všechny změny přibalí k prvotnímu vytvoření zákazníka.

## Galerie – produkční uploader
Součástí V202 je také upravený produkční soubor:

`/str/strana/NahratSoubor.php`

Je založen přímo na dodaném aktuálním produkčním uploaderu. U fotografie nyní podle stavu zákazníka zapisuje buď běžné `obrazek_stazeno=0`, nebo HOLD `2`.

## Soubory
V201 oprava zůstává zachována:

- po uploadu zůstává otevřená záložka Soubory,
- u zákazníka s reálným HairSoft ID je nový soubor `stav=0`,
- u zákazníka bez reálného ID je `stav=2` a v UI se zobrazí „Čeká na ID HairSoft“.

## Ochrana záznamů vytvořených před V202
Při otevření karty zákazníka, který stále používá dočasné ID, V202 převede dosud neodeslané legacy záznamy do HOLD stavu:

- Timeline s `timelineIDHS IS NULL` a příznakem `N/U` -> `H`,
- fotografie `0 -> 2`,
- soubory `0 -> 2`.

To chrání i rozpracovaného zákazníka vytvořeného těsně před nasazením V202, pokud ho HairSoft ještě nepřevzal.

## Databáze
Nová je pouze izolovaná tabulka:

`k_klient_customer_sync_hold`

V201 sloupec `timeline.timelineDoPCVlozeno DATETIME NULL` zůstává. Struktura `klient_lidi`, `klient_lidi_obrazky` a `soubory` se nemění.

Ruční servisní SQL je v `sql/SQL_V202_DVOUFAZOVA_SYNCHRONIZACE.sql`. Aplikace si frontu umí vytvořit sama při prvním použití.

## Co V202 neřeší
Již dříve chybně stažené soubory/fotografie ve složce dočasného ID se automaticky nepřesouvají – pro ověření použijte novou testovací kopii nebo nová data po nasazení V202.

Bonusové body HairSoft -> HS Klient nejsou součástí této opravy; přijímací synchronizační kód není v projektu HS Klient.
