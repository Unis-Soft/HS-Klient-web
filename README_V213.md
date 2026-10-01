# V213 – Nastavení hodnocení: kompaktní desktopové rozložení

V213 navazuje přímo na V212. Změna je pouze prezentační a přeuspořádává vybrané bloky Nastavení hodnocení na PC tak, aby lépe využívaly šířku obrazovky.

## Změny
- `Seznam otázek` a `Nová otázka / Editace otázky` jsou od šířky 992 px ve společném řádku v poměru Bootstrap 8/4 (cca 2/3 a 1/3).
- Formulář nové/editované otázky je v užším pravém panelu na PC skládaný do jednoho sloupce.
- `Text emailu` a `Text SMS` jsou od šířky 992 px vedle sebe 50/50.
- Google část v SMS panelu má URL a text SMS pod sebou a přepínač Interní hodnocení / Google recenze využívá celou šířku panelu.
- Pod 992 px se bloky skládají pod sebe na 100 % šířky.
- Cache klíč `feedback.css` je zvýšen na 213.

## Beze změny
- ukládání, editace a mazání otázek,
- ukládání emailové šablony,
- interní SMS a Google SMS logika,
- databáze a SQL,
- cron hodnocení,
- V210 ruční Timeline synchronizace,
- multi-firma, kopie zákazníka a Správa dat z V212.
