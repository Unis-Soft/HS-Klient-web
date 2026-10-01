# HairSoft Klient V155 – vlastní kompozice detailu obsluhy

V155 navazuje na V154 a řeší pouze horní detail vybrané obsluhy. Oprávnění z V154 zůstávají beze změny.

## Změny
- profil již není samostatná karta ani velká plocha; je to kompaktní identita s fotografií, jménem a rolí v jednom řádku,
- přístupové údaje jsou nativní moderní formulář se dvěma poli vedle sebe na PC,
- nastavení hodnocení je samostatný kompaktní blok se dvěma řádky a přepínači,
- všechny tři části jsou na širokém PC v jedné vyvážené řadě s omezenou maximální šířkou,
- při menší šířce se layout skládá řízeně, bez Bootstrap přesouvání a bez hluchých ploch,
- interní login/hash zůstává skrytý,
- duplicitní ID tabulky hodnocení bylo odstraněno tím, že hodnocení už není renderováno jako tabulka,
- oprávnění a jejich mřížka z V154 se nemění.

## Bezpečnost změny
Zachovány jsou formulářové `action` a `method`, hidden hodnoty, názvy a ID polí pro jméno/heslo, názvy a ID obou checkboxů hodnocení a automatické odesílání při změně přepínačů. SQL dotazy, ukládání hesla, práva, archivace, mazání a import se nemění.

Databáze se nemění.
