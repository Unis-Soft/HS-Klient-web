# HairSoft Klient V143

V143 navazuje na produkční V142 a upravuje hlavní stránku **SMS a hovory – Přehled SMS a hovorů** podle sjednocených vzorů, které už používá zbytek HS Klient.

## Opravy podle kontroly V142
- **Počet odeslaných SMS** už není vložený jako samostatný štítek mezi výběr pobočky a tlačítko. Tři filtry jsou v jednom řádku a pod nimi je čistý výsledkový řádek s hodnotou vlevo a akcí **Zjistit počet** vpravo.
- **KPI karty** už nemají žádný lokální SMS redesign ani druhé ikony. V143 odstraňuje vlastní `hs-sms-kpi` vrstvu a nechává karty plně na globálním systému `hs-system-kpi-card`, který je používán napříč HS Klient od starších verzí.
- **Grafy** přebírají vizuální pravidla z Ročních tržeb: stejnou paletu, typografii, legendu, osu a tooltip čárového grafu, prstencový graf s 72% výřezem, středovou celkovou hodnotou, bílými mezerami mezi segmenty a stejným rozvržením legendy/grafu.
- **Přehled dobití kreditu** nemá pole Hledat. Tabulka používá vzhled Voucherů včetně hlavičky, řádků, exportu, informačního textu a spodního stránkování.
- Opraveno skutečné DataTables stránkování tabulky `#example` na SMS stránce; mobilní karty nyní zobrazují jen řádky aktuální stránky stejně jako desktopová tabulka.

## Technicky
- databáze, SQL dotazy, výpočty SMS, ceny a formuláře zůstávají beze změny,
- doplněny překlady `Počet odeslaných SMS` a `Celkem SMS` pro CZ/SK/EN/DE,
- zvýšeny cache verze `sms-overview.css`, `sms-overview.js` a `i18n.js` na V143.

Nahrajte `hs-client-ui` a `str`, původní soubory ponechte. Potom Ctrl+F5.
