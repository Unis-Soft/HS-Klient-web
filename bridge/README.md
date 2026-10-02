# HairSoft Bridge

Tato část repozitáře obsahuje všechny tři části propojení BSC ↔ HairSoft:

- `windows/` – lokální HSVoucherBridge, aktuálně V016,
- `directory/` – centrální pairing directory pro `bridge.bonfero.com`, aktuálně V001 / protocol 2,
- `tools/repair_k964z_a/` – jednorázový historický opravný nástroj pro testovací voucher K964Z-A. Není určen pro běžný provoz.

Webová část protokolu je v kořenovém BSC projektu (`api/bridge.php`, `app/bridge.php`, `bridge-admin.php`).


V014: okamžitý mirror jednoho potvrzeného čerpání do HS Klient; plný PROGRAMS sync každých 10 minut.