# HairSoft Klient V153 – nový nativní detail obsluhy

V153 opravuje rozbitý layout detailu obsluhy z V151/V152. Tentokrát se detail neskládá přes JavaScriptové přesouvání původních Bootstrap panelů. Moderní override `Uzivatele.php` obsahuje přímo čistou a validní strukturu detailu.

## Změny
- Detail obsluhy je jedna kompaktní moderní karta se třemi vyváženými zónami na PC:
  - profil a fotografie,
  - přístupové údaje,
  - nastavení hodnocení.
- Profil již nevytváří žádnou samostatnou velkou kartu ani prázdnou plochu.
- Přihlašovací pole používají stabilní vertikální formulář vhodný i pro delší EN/DE překlady.
- Hodnocení je v samostatné pravé části stejné karty, bez dalšího vnořeného panelu.
- Oprávnění obsluhy zůstávají pod detailem přes celou šířku.
- Na tabletu se hodnocení přesune pod přístupové údaje, na mobilu se celý detail skládá do jednoho sloupce.
- Doplněny překlady `Přístupové údaje` a `Nastavení hodnocení` pro CZ/SK/EN/DE.
- Cache verze `users.css`, `users.js` a `i18n.js` zvýšena na 153.

## Bezpečnost změny
V153 mění HTML strukturu moderního override `hs-client-ui/php/strana/Uzivatele.php` pouze v části detailu vybrané obsluhy. Důvodem je i odstranění původního nevalidního vnoření formuláře hodnocení do formuláře změny hesla, které prohlížeč mohl automaticky přeskládat.

Zachovány jsou stejné:
- `action` a `method` formulářů,
- POST názvy a hodnoty,
- ID ovládacích prvků,
- hidden hodnoty `Obsluha_ID`, `Obsluha_GUID_POST`, `VybranaPobockaID`, `NastavitHesloObsluze` a `ZmenaPravaHodnoceni`,
- checkboxy hodnocení,
- SQL dotazy, ukládání hesla, oprávnění, archivace, mazání i import.

Databáze se nemění.
