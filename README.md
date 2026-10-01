# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V227**
- HSBridge: **V009 DIAG**
- PROGRAMS synchronizace: **každých 15 minut**, snapshot se odesílá pouze při změně dat.
- HairSoft databáze pro PROGRAMS: **SQLite i MySQL síťová verze**.

## Síťové pravidlo V008

HSBridge je aktivní pouze na jednom PC pro jednu HairSoft databázi:

- SQLite instalace → aktivní Bridge,
- MySQL server PC → `SQLHost=localhost` nebo loopback → aktivní Bridge,
- MySQL klient PC → vzdálená IP/hostname v `SQLHost` → **pasivní Bridge**.

Pasivní MySQL klientské PC neposílá ani nepřijímá žádnou HSBridge komunikaci.
Pravidlo platí globálně pro současné i budoucí Bridge moduly, tedy nejen pro
PROGRAMS.

## MySQL HairSoft

HSBridge pozná síťovou instalaci podle SQL parametrů v `HairSoft\Settings.xml`:
`SQLHost`, `SQLPort`, `SQLDatabase`, `SQLUser` a `SQLPasword`.

Pokud jsou SQL parametry přítomné, PROGRAMS používá MySQL přes TCP. Pokud chybí,
zůstává původní SQLite detekce přes `DatabaseFile` / `data.sdb`.
Hodnota `OpenSQLsettings` se jako přepínač nepoužívá.

MySQL heslo se nekopíruje do HSBridge `config.json` a neloguje se.

## Struktura

- `hs-client-ui/` – webové rozhraní HS Klientu
- `str/` – původní/serverová část HS Klientu
- `bridge/windows/` – Windows HSBridge
- `bridge/hsklient-server/` – serverový endpoint HSBridge pro HS Klient
- `bridge/directory/` – Bridge Directory / identita a párování

CUSTOMER / STATISTICS / TIMELINE zůstávají v HSBridge neaktivní; tyto oblasti
nadále řeší SoftKW / SoftSYS. Cooper voucher flow zůstává zachovaný. Na MySQL
klientském PC je však V008 centrálně pasivní, takže ani voucherová ani jiná
Bridge komunikace z takového PC neprobíhá. Bonfero bude řešeno samostatným
repozitářem.


## V009 – MySQL PROGRAMS schema diagnostika

Na aktivním MySQL server PC HSBridge při startu jednou načte metadata z
`information_schema` a `SHOW CREATE TABLE` pro tabulky:

- `programs`
- `program_payments`
- `program_visits`
- `program_values`
- `program_values2customer`

Do logu se zapisují názvy a typy sloupců, indexy, cizí klíče a CREATE TABLE.
Nečtou ani nelogují se řádky zákazníků, programová data ani MySQL heslo.
Diagnostika slouží pouze k přesnému mapování MySQL schématu proti SQLite verzi.
