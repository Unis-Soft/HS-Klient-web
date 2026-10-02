# HS Klient V234 – Programy: KPI, diakritika a překlady

V234 navazuje na V233.

## Horní karta Programy

- v seznamu zákazníků přibývá třetí horní karta **Programy**;
- hodnota je součet všech kladných nevyčerpaných vstupů napříč všemi aktivními programy firmy;
- pokud má firma více programů, karta nemá combo – zůstává čistý souhrn;
- rozpad podle jednotlivých programů je dostupný v tooltipu / detailním textu karty;
- výběr konkrétního programu zůstává pouze v hlavičce sloupce tabulky.

## Název programu

- dlouhý název v hlavičce sloupce se nadále zkracuje výpustkou;
- při více programech zůstává combo v hlavičce sloupce;
- nalezena skutečná příčina problému s velkým **Ě**: globální CSS používalo Tahoma s `!important`, takže předchozí font override z V233 se vůbec neuplatnil;
- V234 používá pro název programu vyšší specificitu a Segoe UI/Arial;
- známý název `ZÓNY TĚLA` se pro české zobrazení normalizuje na **Zóny těla** bez změny hodnoty v DB.

## Překlady

- Programová část detailu je nově povolena pro i18n i uvnitř dříve chráněného `.profile-body`;
- doplněny překlady základních Programových labelů;
- známý název **Zóny těla**: CS / SK / EN / DE;
- ostatní uživatelsky definované názvy programů zůstávají beze změny, dokud pro ně není explicitní překladová mapa.

HSBridge V010 se nemění.
