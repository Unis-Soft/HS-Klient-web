# HairSoft Klient V146

V146 je opravná verze překladů pro sekci **SMS a hovory**.

## Oprava překladů
- opraveno verzování `i18n.js`: V145 obsahovala nové překlady, ale hlavní aplikace stále načítala asset s cache klíčem `v=143`, takže prohlížeč mohl používat starý slovník bez nových textů,
- `i18n.js` se nyní načítá s verzí `v=146`,
- společná vrstva Příchozích hovorů, Příchozích SMS a Odchozích SMS po každém překreslení DataTables znovu lokalizuje vyhledávání, Export, stránkování, prázdný stav a informaci o aktuální stránce,
- přepnutí CZ / SK / EN / DE funguje i bez nového načtení stránky a lokalizace se zachová po hledání a stránkování,
- doplněn překlad textu `záznamů na stranu`.

## Beze změny
- horní KPI karty, tabulkový vzhled, PDF exporty, SQL, databázová data a spodní patička se nemění.

Po nasazení doporučeno jednou provést Ctrl+F5, protože právě tato verze opravuje starý cache klíč překladového souboru.
