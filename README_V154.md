# HairSoft Klient V154 – kompaktní profil a oprávnění obsluhy

V154 navazuje na V153 a mění pouze vizuální uspořádání detailu vybrané obsluhy a oprávnění. Přístupové údaje zůstávají funkčně i datově stejné.

## Změny
- profilová fotografie, jméno a role jsou v malém kompaktním bloku přímo vedle přístupových údajů, bez velké prázdné plochy,
- přístupové údaje si zachovávají rozložení V153,
- nastavení hodnocení je na širokém PC ve stejné horní řadě; při menší šířce se skládá pod přístupové údaje,
- interní skrytý login/hash se již nezobrazuje kvůli CSS přepisu `display`,
- oprávnění obsluhy jsou na PC ve dvousloupcové mřížce kompaktních položek místo dlouhých řádků přes celou obrazovku,
- na mobilu se oprávnění skládají do jednoho sloupce,
- anonymizace citlivých dat je vizuálně podřazená přímo pod oprávnění Zákazníci,
- cache verze `users.css`, `users.js` a `i18n.js` zvýšena na 154.

## Bezpečnost změny
Zachovány jsou původní názvy formulářů, `action`, `method`, POST názvy, ID checkboxů, hidden hodnoty a automatické odesílání formuláře při změně přepínačů. SQL dotazy, ukládání hesla, oprávnění, anonymizace, archivace, mazání a import se nemění.

Databáze se nemění.
