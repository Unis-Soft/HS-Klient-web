# V216 – Nastavení hodnocení: obnovená mezera pod horními bloky

V216 navazuje přímo na V215. Opravuje pouze desktopový layout horních bloků Nastavení hodnocení.

## Změny
- Bloky `Výběr střediska` a `Odeslání dotazníku` zůstávají na desktopu stejně vysoké.
- Byla obnovena standardní svislá mezera 22 px mezi horní dvojicí bloků a následující sekcí `Seznam otázek / Nová otázka`.
- Oprava odstraňuje nevhodné `height:100%` z vnořených flex prvků, které ve V215 způsobilo, že spodní margin panelů přetékal mimo grid řadu a vizuálně se ztratil.
- `Test dotazníku` zůstává defaultně sbalený stejně jako ve V215.
- Cache klíč `feedback.css` je zvýšen na 216.

## Beze změny
- poměr horních bloků 35/65,
- kompaktní layout otázek, emailu a SMS z V213,
- nastavení střediska, způsobu a termínu odeslání,
- interní a Google SMS větev,
- validace a odesílání testovacího emailu/SMS,
- databáze, SQL a cron,
- ostatní funkce HS Klient.
