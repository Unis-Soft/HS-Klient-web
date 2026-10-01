# V215 – Nastavení hodnocení: stejné horní bloky a sbalený test

V215 navazuje přímo na V214. Změny jsou pouze prezentační; funkce hodnocení, databázová logika ani odesílání se nemění.

## Změny
- Desktopové bloky `Výběr střediska` a `Odeslání dotazníku` mají při zobrazení vedle sebe shodnou výšku.
- Obsah obou horních panelů se roztahuje uvnitř stejné grid řady bez změny poměru 35/65 z V214.
- Poslední sekce `Test dotazníku` je změněna na nativně sbalitelný blok a po načtení stránky je ve výchozím stavu zavřená.
- Hlavička `Test dotazníku` má jednoduchou šipku ve stylu sbalitelných sekcí typu `Správa dat`; po rozbalení zůstává původní obsah testovacího emailu a SMS beze změny.
- Cache klíč `feedback.css` je zvýšen na 215.

## Beze změny
- validace a odesílání testovacího emailu/SMS,
- interní a Google SMS větev,
- texty emailu/SMS a otázky,
- nastavení střediska, četnosti a termínu odeslání,
- databáze, SQL a cron,
- všechny změny V210–V214.
