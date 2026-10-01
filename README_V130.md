# HairSoft Klient V130 – překlady sumáře a načítání voucherů

Vychází z V129; obsahuje její kalendář a všechny předchozí úpravy.

- Doplněn překlad záhlaví Přehled a přesné původní varianty textu Zůstatek v Depozitu, která v HTML používá dvě jednoduché uvozovky. Ověřeno CS/EN/SK/DE a návrat do češtiny.
- Ochrana proti probliknutí se zapíná na začátku hlavičky a používá přímo označený obal voucherové stránky. Skryje i samotné záhlaví během postupného přijímání HTML.
- Odstraněno automatické odskrytí po osmi sekundách. Stránka se zobrazí až po dokončení úprav vzhledu. Pokud po dokončení načítání skript selže, zobrazí se zpráva pro obnovení stránky, nikoliv původní rozložení.
- Výpočty, dotazy, filtr Expirováno a formuláře jsou funkčně beze změny.

## Nahrání

Nahrajte celé složky **hs-client-ui** a **str**, potom Ctrl+F5. **Nestačí pouze CSS a JavaScript**: nové označení obalu je také v PHP stránkách Voucher.php, VoucherHistorie.php a VoucherZnpeplatneny.php pod hs-client-ui/php/strana.
Původní str/strana ponechte na serveru.

Změny oproti V129:
- hs-client-ui/php/index.php
- hs-client-ui/php/strana/Voucher.php
- hs-client-ui/php/strana/VoucherHistorie.php
- hs-client-ui/php/strana/VoucherZnpeplatneny.php
- hs-client-ui/js/voucher.js
- hs-client-ui/js/i18n.js
- hs-client-ui/VERSION.txt

Lokální kontroly: zpoždění skriptu přes 9 sekund, samostatné záhlaví ještě před doručením obsahu, odskrytí hotové stránky, překlady sumáře ve čtyřech jazycích a regresní kontroly přehledu, historie, formulářů, mobilních karet a posuvníků. PHP tokeny výpočtů a databázových operací zůstávají shodné s dodanými originály (mimo cesty společných souborů).

Na živý server nebylo nasazeno.
