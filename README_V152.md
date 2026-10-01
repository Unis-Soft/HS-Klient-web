# HairSoft Klient V152 – kompaktní detail obsluhy

V152 opravuje pouze rozložení detailu obsluhy v sekci Uživatelé. Databázová a ukládací logika se nemění.

## Změny
- Detail již nespoléhá na původní Bootstrap sloupce 2/6/4. Panely se pouze vizuálně přesunou do vlastní bezpečné obálky.
- Profilová fotografie je malý kompaktní blok vlevo bez vlastní velké karty.
- Přihlašovací údaje jsou zarovnané v pravé části s plnohodnotnou šířkou polí.
- Nastavení hodnocení zůstává součástí detailu, ale má kompaktní šířku a řádky.
- Oprávnění obsluhy jsou pod detailem jako samostatná karta přes celou šířku.
- Mobilní layout skládá profil do krátkého horizontálního řádku a formulář pod něj.
- U nadpisu detailu je opravený odstup mezi názvem a jménem obsluhy.
- Cache verze `users.css` a `users.js` zvýšena na 152.

## Bezpečnost změny
`hs-client-ui/php/strana/Uzivatele.php` není ve V152 měněn. SQL, POST/GET názvy, hesla, práva, anonymizace, archivace, mazání ani import nejsou upraveny.
