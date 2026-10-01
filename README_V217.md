# V217 – Nastavení hodnocení: stejné výšky otázek + scroll seznamu

V217 navazuje přímo na V216 a upravuje pouze desktopový layout dvojice `Seznam otázek` a `Nová otázka / Editace otázky`.

## Změny
- Na desktopu mají bloky `Seznam otázek` a `Nová otázka / Editace otázky` stejnou výslednou výšku.
- Referenční výškou je přirozená výška pravého formuláře nové/editované otázky, takže se dvojice zbytečně nenatahuje podle počtu existujících otázek.
- Pokud je otázek více, svisle se posouvá pouze oblast tabulky v levém bloku.
- Hledání a Export zůstávají nad posuvnou oblastí a informace/stránkování pod ní.
- Hlavička tabulky otázek zůstává při svislém posunu viditelná.
- Pod 992 px se pevné dorovnání vypíná a bloky se chovají responzivně jako dříve.
- Cache klíče `feedback.css` a `feedback.js` jsou zvýšeny na 217.

## Beze změny
- poměr bloků Seznam otázek / Nová otázka z V213,
- horní 35/65 bloky a jejich stejná výška z V214–V216,
- defaultně sbalený `Test dotazníku`,
- ukládání, editace a mazání otázek,
- email, interní SMS a Google SMS větev,
- testovací email/SMS,
- databáze, SQL, cron a ostatní funkce HS Klient.
