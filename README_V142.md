# HairSoft Klient V142

Kompletní balíček navazuje na produkční V141.

Sekce **SMS a hovory – Přehled SMS a hovorů** má nově vlastní moderní override `hs-client-ui/php/strana/Sms.php`. Datové dotazy, význam metrik, formuláře a databáze zůstávají zachované.

## Vzhled a UX
- sjednocený Open Sans, panely, radiusy a stíny podle V141,
- moderní výběr pobočky a filtr počtu SMS,
- čtyři responzivní KPI karty: SMS kredit, SMS fronta, celkem odeslané a tarif SMS,
- moderní čárový graf za poslední tři měsíce a prstencový graf typů SMS,
- stabilní barevná paleta grafů místo náhodných barev při každém načtení,
- moderní přehled dobití kreditu a mobilní karty dobíjení,
- moderní přehled odchozích zpráv a příchozích hovorů/SMS,
- kompaktní tlačítko **Dobít kredit**,
- mobilní rozložení bez přetékání tabulek,
- překlady CZ/SK/EN/DE přes společnou jazykovou vrstvu.

## Opravy
- odstraněny dva aktivní debug výpisy SQL dotazů,
- email uživatele je inicializovaný ještě před použitím ve filtru,
- výpočty kreditu a ceny SMS jsou v PHP 8.3 chráněné proti nulovému i nenumerickému tarifu; pokud tarif není nastaven, používá se pro výpočty bezpečně 0,
- opravený HTML tag canvasu,
- odstraněna zavádějící synchronizační patička, která používala datum z tabulky tržeb; stránka nyní uvádí pouze skutečný stav načtení aktuálních SMS/hovorových dat.

Nahrajte `hs-client-ui` a `str`, původní soubory ponechte. Potom Ctrl+F5.
