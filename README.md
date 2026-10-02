# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V234**
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


## V231 – Programy vizuální opravy

V231 vychází ze stabilní V227. Odstraňuje pouze druhý `fadeInUp` flash,
opravuje šířku/typografii názvu Programu a odstraňuje deformaci SVG textů
v obou grafech Programů. HSBridge V010 zůstává beze změny.


## V232 – Programy: názvy a grafy

Opraveno zobrazení názvu jednoho Programu v seznamu i detailu. Graf zůstatku
slučuje více událostí stejného dne do jednoho vizuálního bodu a drží krajní
datumy uvnitř grafu. Graf frekvence zobrazuje datum pod každým sloupcem při
běžném počtu intervalů.


## V233 – česká diakritika v názvu Programu

Opraveno vertikální ořezávání velkých českých znaků v názvu Programu, zejména
Ě. Logika jednoho programu, zkracování dlouhého názvu a combo pro více programů
zůstává beze změny.


## V234 – Programy: KPI, diakritika a překlady

V seznamu zákazníků přibyla souhrnná karta Programy s celkovým počtem
nevyčerpaných vstupů napříč aktivními programy. Při více programech karta
zůstává bez comboboxu; konkrétní program se dál vybírá pouze v hlavičce tabulky.

Opraven byl skutečný důvod špatného vykreslení velkého Ě – globální Tahoma
`!important` přebíjela předchozí override. Programová sekce je zároveň nově
zapojena do i18n a název ZÓNY TĚLA se zobrazuje jako Zóny těla / Zóny tela /
Body zones / Körperzonen.
