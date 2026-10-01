# V193 – přesný návrat Odhlásit z V190 + typografický standard

Navazuje na V192. Multi-firma logika a dlouhodobé přihlášení se nemění.

## 1. Levé Odhlásit
- V192 používala formulářový `<button>`, což obcházelo původní CSS pravidla V190 navázaná na `li.hs-menu-logout > a`.
- V193 je v levém menu znovu skutečný původní `<a>` prvek z V190 se stejným SVG a stejným CSS.
- Bezpečná funkce „odhlásit pouze aktuální firmu“ zůstává zachována: odkaz pouze odešle skrytý CSRF POST formulář na multi-company endpoint.
- Vizuál, rozměry, červený gradient, ikona, hover i sbalené menu tak opět řídí původní `client-ui.css` z V190.

## 2. Typografie multi-firma
Celý nový modul používá explicitně standard HairSoft:
- `Open Sans, Helvetica, Arial, sans-serif`
- vyhlazování textu
- pouze standardní řezy 400 / 600 / 700
- hlavní běžný text 13 px
- modalové nadpisy 20 px / 700 podle ostatních moderních dialogů HairSoft
- formulářové hodnoty 14 px / 400 a tlačítka 13 px / 600

Vynucení platí pro:
- horní tlačítko firmy,
- panel Přepnout firmu,
- názvy účtů a role,
- Přidat firmu,
- Odhlásit tuto / všechny firmy,
- modal Přidat další firmu,
- login, heslo a jeho tlačítka,
- systémové hlášky modulu.

## Beze změny
- více zapamatovaných firem,
- 365denní tokeny,
- poslední pobočka každé firmy,
- práva administrátora / manažera / obsluhy,
- databáze,
- Google recenze V190,
- biometrie.
