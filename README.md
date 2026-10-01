# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V229**
- HSBridge: **V010**
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


## V010 – MySQL PROGRAMS

Podle diagnostiky reálné MySQL databáze HairSoft používají tabulky
`programs` a `program_values` pro název sloupec `name1`.
V010 proto používá:
- `programs.name1`
- `program_values.name1`

Ostatní MySQL PROGRAMS tabulky a vazby zůstávají beze změny.
Dočasná V009 schema diagnostika byla odstraněna.

## V228 – plynulá navigace shellu

Při přechodu mezi sekcemi z levého menu zůstává horní hlavička a levé menu
viditelné. Načítání se signalizuje pouze v hlavní obsahové části. Globální
`hs-ui-preparing` maska byla odstraněna; page-specific ochrany jako Vouchery
zůstávají zachované.


## V229 – stabilní přechody mezi moduly

Při klasické PHP navigaci se v podporovaných Chromium prohlížečích drží na
obrazovce předchozí horní hlavička a levé menu až do připravení dalšího
dokumentu. Hlavní obsah už nepoužívá legacy `fadeInUp`, takže odpadá druhé
probliknutí po načtení.
