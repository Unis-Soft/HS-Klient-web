# HairSoft Klient V148

V148 je čisté UI doladění sekce **Uživatelé** nad V147. Databázová a ukládací logika se nemění.

## Rozložení
- Výběr pobočky je na desktopu v jednom řádku s názvem panelu.
- Import obsluh z programu je přesunut do stejného horního panelu vedle výběru pobočky.
- Původní samostatný panel **Akce** je po přesunu formuláře skryt.
- Na mobilu se výběr pobočky a import skládají pod sebe.

## Seznamy obsluh
- Hledání aktivních obsluh je vlevo v samostatné liště nad tabulkou.
- Stejné řešení používá seznam archivovaných obsluh.
- DataTables Bootstrap řádek, který ve V147 vytvářel velkou mezeru a tlačil hledání doprava, se po přesunu filtru skryje.
- Tabulky, práva, akce, stránkování a jejich data zůstávají beze změny.

## Překlady
- doplněny překlady položek bočního menu **Přehled uživatelů** a **Docházka** pro CZ / SK / EN / DE,
- titulky obou položek menu jsou sjednoceny se zobrazeným textem,
- `i18n.js`, `users.css` a `users.js` používají cache verzi V148.

## Bezpečnost změny
- `hs-client-ui/php/strana/Uzivatele.php` je vůči V147 beze změny,
- SQL, POST/GET parametry, změna hesla, práva, anonymizace, archivace, obnovení, trvalé smazání a import se nemění,
- V148 mění pouze prezentační CSS/JS a texty menu.
