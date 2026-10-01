# V218 – Nastavení hodnocení: čistý scroll + stejné výšky email/SMS

V218 navazuje přímo na V217.

## Změny
- Seznam otázek si ponechává vlastní svislý scroll, ale hlavička tabulky je nyní neprůhledná a nad řádky, takže obsah při posunu nezajíždí do názvů sloupců.
- Na desktopu mají panely `Text emailu` a `Text SMS` stejnou výslednou výšku podle vyššího z nich.
- Pod emailový editor je doplněn přehled stejných podporovaných proměnných jako u SMS: `%DATUM_NAVSTEVY%`, `%JMENO_PROVOZOVNY%`, `%ODKAZ_SPOKOJENOST%`.
- Tyto tři proměnné jsou již reálně používány emailovou šablonou i testovacím emailem; nedoplňuje se nová backendová funkcionalita, pouze jejich viditelné uvedení v UI.
- Cache klíč `feedback.css` je zvýšen na 218.

## Beze změny
- stejné výšky Seznam otázek / Nová otázka a scroll z V217,
- horní bloky a mezery z V214–V216,
- defaultně sbalený Test dotazníku,
- ukládání emailu, interní SMS a Google SMS,
- databáze, SQL, cron a ostatní funkce HS Klient.
