# V211 – sjednocená Správa dat a jemnější KPI detailu zákazníka

V211 navazuje přímo na V210. Synchronizace kopie zákazníka, Timeline staging, ruční HairSoft ID i Bonfero produkční adresa zůstávají beze změny.

## 1. Správa dat – jeden standard v celém HS Klient
Nová společná UI vrstva `data-management.css` + `data-management.js` sjednocuje všechny aktuální sekce Správa dat:
- Seznam zákazníků,
- detail zákazníka,
- Sklad a Sklad XML,
- Denní tržby,
- Měsíční tržby,
- Roční tržby,
- Celkové tržby od počátku,
- Vouchery.

Vizuální standard vychází ze schváleného vzhledu Měsíčních tržeb:
- Tahoma, Arial, sans-serif,
- nadpis 15 px / 700 na desktopu,
- podnadpis 11.5 px / 400 na desktopu,
- akční tlačítka 12.5 px / 600,
- jednotná ikonka nastavení v 42px světlém boxu,
- jednotný chevron pro rozbalení,
- výchozí stav vždy sbalený,
- na mobilu 14 px nadpis a 10.5 px podnadpis podle existujícího responzivního vzoru Měsíčních tržeb.

Existující formuláře, hidden inputy, POST akce, potvrzení a serverová logika zůstávají na původních místech. Společná vrstva pouze sjednocuje hlavičku, sbalení a typografii.

## 2. Detail zákazníka – Volat / SMS / Bonusové body
Číselné hodnoty horních karet Volat, SMS a Bonusové body mají nově `font-weight: 500`, stejně jako zjemněné KPI hodnoty Dashboardu od V203.

Zůstává:
- Tahoma,
- stejná velikost textu,
- stejné barvy,
- stejné rozměry a layout karet.

## Databáze
Beze změny.
