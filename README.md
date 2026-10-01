# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V227**
- HSBridge: **V007 PROGRAMS**
- PROGRAMS synchronizace: **každých 15 minut**, snapshot se odesílá pouze při změně dat.
- HairSoft databáze pro PROGRAMS: **SQLite i MySQL síťová verze**.

## MySQL HairSoft

HSBridge pozná síťovou instalaci podle SQL parametrů v `HairSoft\Settings.xml`:
`SQLHost`, `SQLPort`, `SQLDatabase`, `SQLUser` a `SQLPasword`.

Pokud jsou SQL parametry přítomné, PROGRAMS používá MySQL přes TCP. Pokud chybí,
zůstává původní SQLite detekce přes `DatabaseFile` / `data.sdb`.
Hodnota `OpenSQLsettings` se jako přepínač nepoužívá, protože reálná síťová
konfigurace může mít hodnotu 0.

MySQL heslo se nekopíruje do HSBridge `config.json` a neloguje se. Bridge čte
SQL připojení při synchronizaci přímo ze `Settings.xml`.

## Struktura

- `hs-client-ui/` – webové rozhraní HS Klientu
- `str/` – původní/serverová část HS Klientu
- `bridge/windows/` – Windows HSBridge
- `bridge/hsklient-server/` – serverový endpoint HSBridge pro HS Klient
- `bridge/directory/` – Bridge Directory / identita a párování

CUSTOMER / STATISTICS / TIMELINE zůstávají v HSBridge neaktivní; tyto oblasti
nadále řeší SoftKW / SoftSYS. Cooper voucher flow zůstává zachovaný a jeho
SQLite zápisová větev se ve V007 nemění. Bonfero bude řešeno samostatným
repozitářem.
