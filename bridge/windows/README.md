# HSBridge V006 PROGRAMS

HSBridge je modulární Windows agent mezi HairSoftem a cloudovými službami UnisSoft.

## Kompatibilita V006

V005 vychází přímo z produkčního HSVoucherBridge V016 a zachovává stávající Cooper voucher flow:
- fronta voucherů / konfigurace: 600 s,
- HairSoft katalog: 1 hodina,
- stejné párování přes https://bridge.bonfero.com,
- stejné mapování střediska, uživatele, plateb a voucherů,
- stejné bezpečné číslování billnumber v rámci id_centre,
- stejná cena prodaná na webu.

Při prvním spuštění HSBridge se automaticky převezme existující
%LOCALAPPDATA%\UnisSoft\HSVoucherBridge\config.json, takže již spárovaný Cooper
nemusí být znovu párován.

## Nová architektura

Datová vrstva je oddělená od modulů přes HairSoftDataProvider.
- SQLite provider: implementován.
- MySQL provider: připraven jako budoucí backend stejného rozhraní.

Exportní moduly otevírají SQLite pouze READ-ONLY a nikdy nečekají na zámek HairSoftu.

Identita PC se čte tiše z:
HKCU\SOFTWARE\VB and VBA Program Settings\TMKSW\RC V1

HairSoft se hledá pouze ve standardních kořenech <disk>:\HairSoft\Settings.xml,
bez prohledávání celých disků.

Umístění programu:
<HairSoft>\HSBridge.exe

HSBridge používá existující HairSoft adresář pro logy:
<HairSoft>\log\HSBridge_YYYY-MM-DD.log

Datum v názvu se určí při startu HSBridge. Pokud PC/HSBridge běží přes půlnoc,
pokračuje se do stejného logu i další den. Po novém spuštění v jiný den se
použije nový soubor. Samostatný adresář HSBridge pro logy se nevytváří.

## Build

Zdroj je čisté Go pro Windows a používá systémové winsqlite3.dll.

```bat
set GOOS=windows
set GOARCH=amd64
go build -trimpath -ldflags="-s -w -H=windowsgui" -o HSBridge.exe .
```

Poznámka: V003 je diagnostická vývojová větev. Modul HS Klient se aktivuje až po nasazení
serverového endpointu a ověření mapování PC/skupina v Admin Klient.

## CUSTOMER V001 test

První end-to-end test je omezen na HairSoft customer.id=492.

- lokální podmínka: HS SYS\\Dashboard=1,
- serverová podmínka: PC je přes RC V1 nalezeno v sw_info a sw_id existuje v sw_email_pobocka,
- identita PC: RC V1 Setting3/Setting10 je při registraci svázána s Bridge ID,
- server nikdy nepřijímá sw_id ani skupinu z PC,
- SQLite se pro export otevírá READ-ONLY a busy_timeout=0,
- HSBridge serializuje vlastní čtení/zápisy do HairSoft DB,
- odesílají se jen note a loyalityPoints (+ ID/GUID/updated pro kontrolu),
- server kontroluje KlientGuid, pokud je vyplněn na obou stranách,
- server vede idempotentní audit podle eventId.

Endpoint:
https://klient.hairsoft.cz/str/api/hsbridge.php


## HS Klient / SoftSYS feature flags

HSBridge čte pouze pro rozhodnutí o aktivaci modulů:
HKCU\SOFTWARE\VB and VBA Program Settings\TMKSW\HS SYS

- Dashboard=1: na PC je aktivní HS Klient; moduly CUSTOMER/STATISTICS/TIMELINE mohou být povoleny.
- Dashboard=0 nebo chybí: HS Klient synchronizační moduly neběží.
- DashboardVzdalenyHash: pouze detekujeme, zda existuje vzdálený Dashboard. Hodnota se neloguje ani se nepoužívá jako autentizace Bridge.

HSBridge do HS SYS registru nikdy nezapisuje; vlastníkem těchto hodnot zůstává SoftSYS.


## STATISTICS V002 test

Druhy end-to-end test zustava omezen na HairSoft customer.id=492.

Prenasi se pouze:
- posledni skutecne dokoncena navsteva z HairSoft bill.endbill,
- nejblizsi budouci aktivni rezervace z HairSoft orders.order_date.

Filtry:
- posledni navsteva: bill.valid=1 a Discarted=0,
- budouci rezervace: orders.valid=1, Break=0, Canceled=0,
- cteni HairSoft SQLite je pouze READ-ONLY a busy_timeout=0.

Na serveru se meni jen:
- klient_lidi_statistika.stat_PosledniNavsteva,
- klient_lidi_statistika.stat_PristiNavsteva,
- klient_lidi_statistika.stat_updated.

Pocet navstev ani pocet zrusenych rezervaci se ve V002 zatim nemeni.


## STATISTICS V003 diagnostika

Pokud u customer.id=492 neni nalezena budouci navsteva, V003 jednou po startu
zapise do logu az 10 poslednich radku z HairSoft tabulky orders pro tohoto
zakaznika. Loguje pouze technicka pole:

- orders.id
- id_customer
- order_date
- order_end_date
- valid
- Canceled
- Break
- inserted
- changed

Nezapisuje jmeno, telefon, e-mail ani poznamku zakaznika.


## PROGRAMS V004

V004 synchronizuje pouze data, která nyní neposílá SoftKW ani SoftSYS:

- aktivní definice z `programs` (`valid=1`),
- nákupy / přidělené návštěvy z `program_payments`,
- čerpání z `program_visits`,
- aktivní vlastní položky z `program_values`,
- hodnoty zákazníků z `program_values2customer`.

Programový export otevírá HairSoft SQLite pouze READ-ONLY, používá `busy_timeout=0`
a při zámku databáze cyklus přeskočí. Neprovádí žádný zápis do HairSoft DB.

Cílový endpoint:
`https://klient.hairsoft.cz/str/api/hsbridge.php?route=programs/snapshot`

CUSTOMER / STATISTICS / TIMELINE kód zůstává zachován pro budoucí použití, ale
V005 spouští pouze `runProgramsModule()`.

## Cooper vouchery

Produkční Cooper voucher flow z HSVoucherBridge V016 / HSBridge V003 zůstává
beze změny: stejné párování, katalog, konfigurace, fronta jobů a bezpečný zápis
voucherů do HairSoft. PROGRAMS je samostatný read-only modul a do voucherové
větve nezasahuje.


## V005 – oddělení PROGRAMS od Voucherů

Na novém PC určeném pouze pro HS Klient / PROGRAMS se již nezobrazuje
voucherové párovací okno.

Voucherový modul se spustí pouze tehdy, když má PC konkrétní existující
voucherovou konfiguraci (např. migrovaný Cooper/HSVoucherBridge nebo již
uložený cílový voucher web). Tím zůstává Cooper provoz beze změny, ale běžné
HS Klient PC nevyžaduje žádné propojení s Vouchery.

Budoucí Bonfero voucher integrace není ve V005 aktivována a bude přidána až
samostatně.


## V006 – PROGRAMS po 15 minutách

PROGRAMS kontroluje HairSoft databázi 4× za hodinu, tedy každých 15 minut.
Snapshot se na server odešle pouze při změně dat.

## V007 – MySQL síťová HairSoft instalace

PROGRAMS podporuje dva zdroje dat:

- SQLite: původní `DatabaseFile` / `data.sdb`,
- MySQL: parametry `SQLHost`, `SQLPort`, `SQLDatabase`, `SQLUser` a
  `SQLPasword` v `Settings.xml`.

Pokud jsou SQL parametry v `Settings.xml` přítomné, MySQL má pro PROGRAMS
přednost před starou hodnotou `DatabaseFile`. Je to záměrné: po převodu
salonu ze SQLite na síťovou MySQL může `DatabaseFile` v XML stále ukazovat
na původní lokální soubor.

`OpenSQLsettings` se nepoužívá jako aktivační příznak. Reálná síťová
konfigurace může obsahovat hodnotu 0 a přesto používat MySQL.

Bridge nečte fyzické `.ibd` soubory z `C:\HairSoft\MySQL\data\data`.
Připojuje se standardně přes MySQL TCP podle `SQLHost:SQLPort`. Přihlašovací
heslo se nekopíruje do HSBridge `config.json` a nevypisuje se do logu.

MySQL podpora je ve V007 přidána pouze do read-only PROGRAMS datové vrstvy.
Cooper voucher flow a jeho stávající SQLite zápisová větev se nemění.
CUSTOMER / STATISTICS / TIMELINE zůstávají neaktivní.
