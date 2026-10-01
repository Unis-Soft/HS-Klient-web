# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V227**
- HSBridge: **V006 PROGRAMS**
- PROGRAMS synchronizace: **každých 15 minut**, snapshot se odesílá pouze při změně dat.

## Struktura

- `hs-client-ui/` – webové rozhraní HS Klientu
- `str/` – původní/serverová část HS Klientu
- `bridge/windows/` – Windows HSBridge
- `bridge/hsklient-server/` – serverový endpoint HSBridge pro HS Klient
- `bridge/directory/` – Bridge Directory / identita a párování

CUSTOMER / STATISTICS / TIMELINE zůstávají v HSBridge neaktivní; tyto oblasti nadále řeší SoftKW / SoftSYS. Cooper voucher flow zůstává zachovaný. Bonfero bude řešeno samostatným repozitářem.
