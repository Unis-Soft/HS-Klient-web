# HS Klient V237 – čerpání Programů z detailu zákazníka

V237 navazuje přímo na V236.

## Čerpání Programů

- v detailu zákazníka / Programy je u aktuálně vybraného programu nové tlačítko **Čerpat**;
- modal obsahuje název programu, aktuální synchronizovaný zůstatek a jediné editované pole **Množství** s ovládáním − / +;
- HS Klient nekontroluje nárok ani zůstatek – požadované množství může v HairSoft vytvořit i záporný výsledný zůstatek;
- web nevytváří vlastní „virtuální“ čerpání. Pouze zařadí příkaz do fronty pro HSBridge;
- opravy chybného čerpání se dělají výhradně v HairSoft. Následující synchronizace vrátí HS Klient na stav HairSoft.

## Priorita HairSoft DB

HairSoft je vždy autorita a má vždy prioritu při práci s databází.

- SQLite zápis HSBridge používá `busy_timeout=0` a jediný krátký `INSERT` do `program_visits`;
- pokud je SQLite právě používána/zamčená HairSoftem, Bridge nečeká a příkaz vrátí do fronty pro další pokus;
- MySQL větev používá jediný krátký `INSERT`, bez dlouhé transakce, s krátkým lock wait limitem;
- žádný zápis z HS Klient nesmí držet HairSoft DB nebo čekáním omezovat práci programu;
- po úspěšném INSERTu Bridge databázi znovu nečte; nový stav převezme až běžná 15min synchronizace Programů, takže čerpání nepřidává žádný další okamžitý přístup k HairSoft DB.

## Fronta a idempotence

- nový serverový endpoint: `/str/api/hsbridge-program-actions.php`;
- nový uživatelský endpoint: `/str/program-consume.php`;
- fronta: `hsbridge_program_commands` (v MySQL databázi HS Klient, nikoli v HairSoft DB);
- Bridge uchovává poslední potvrzené příkazy v lokálním `hsklient-program-actions.json`, aby při ztraceném HTTP potvrzení nevytvořil stejné čerpání podruhé;
- Bridge kontroluje frontu každých 10 sekund.

## Záporné zůstatky

V237 odstraňuje staré nulové ořezy:

- detail Programu zobrazuje skutečný `prepaid - used`, i když je záporný;
- programový sloupec v seznamu zákazníků může zobrazit 0 i zápornou hodnotu;
- horní KPI Programy a rozpad programů pracují se skutečnými podepsanými hodnotami;
- graf vývoje zůstatku podporuje i osu pod nulou.

## Překlady

Nový modal a jeho stavy mají CZ / SK / EN / DE překlady.

## HSBridge

Zdroj HSBridge je zvýšen na **V011**. Vouchery a stávající voucherový flow nejsou změněny.