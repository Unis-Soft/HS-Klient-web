# HairSoft Klient V147

V147 modernizuje sekci **Uživatelé** s důrazem na zachování stávající logiky práv a účtů.

## Uživatelé
- přidán moderní override `hs-client-ui/php/strana/Uzivatele.php`,
- původní databázové dotazy, názvy POST/GET parametrů a ukládací akce jsou zachované,
- moderní výběr pobočky,
- detail vybrané obsluhy je sjednocen do karet Profil, Přístup a Oprávnění,
- profilová fotografie, heslo, hodnocení a přepínače práv používají jednotný vzhled HairSoft Klient,
- seznam aktivních a archivovaných obsluh používá stejný tabulkový styl jako Vouchery,
- doplněno hledání a spodní stránkování po 10 záznamech,
- práva v seznamu se zobrazují jako kompaktní štítky,
- akce Smazat / Archivovat / Obnovit mají moderní ikonová tlačítka,
- na mobilu se seznamy skládají do samostatných karet,
- import obsluh z programu je sjednocen se systémovými akčními tlačítky,
- prázdný legacy panel na konci stránky se skrývá.

## Potvrzení citlivých akcí
- společný moderní potvrzovací dialog nově umí neutrální variantu pro archivaci a obnovení,
- smazání nadále používá červenou destruktivní variantu,
- archivace a obnovení mají správný název i text akčního tlačítka; původní URL a akce zůstávají beze změny.

## Překlady a patička
- doplněny překlady CZ / SK / EN / DE pro celou sekci Uživatelé,
- společný `i18n.js` používá cache verzi V147,
- Uživatelé jsou napojeni na standardní spodní stavovou patičku.

## Beze změny
- databázová struktura,
- SQL pro ukládání práv, hesel, archivaci, obnovení, smazání a import,
- existující názvy polí formulářů a jejich automatické odesílání,
- stávající autentizační mechanismus.
