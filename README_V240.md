# HS Klient V240 – oprava odeslání fotografií Programu + vlastní výběr souborů

V240 navazuje přímo na V239.

## Opravy
- Opraveno ověření programu při odeslání fotografií: synchronizační tabulka `hsbridge_programs` používá sloupec `name`; V239 chybně dotazovala zdrojový HairSoft název `name1`, takže MySQL `prepare()` selhalo a UI zobrazilo „Program se nepodařilo ověřit.“
- Nativní prohlížečový ovladač „Vybrat soubor / Soubor nevybrán“ je nahrazen vlastním ovladačem ve stylu HS Klient.
- Vlastní „Vybrat fotografie“ podporuje výběr více fotografií najednou z galerie/souborů zařízení.
- Tlačítko „Vyfotit“ zůstává samostatné a na mobilu nadále vyvolává zadní fotoaparát přes `capture=environment`.
- Oba zdroje přidávají fotografie do stejné dávky a používají stejné náhledy/mazání/odeslání.

## Bridge
HSBridge se nemění; zůstává V012 GUI.

## Cache
`client-detail-programs.css` a `client-detail-programs.js` používají cache verzi `v=240`.