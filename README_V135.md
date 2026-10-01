# HairSoft Klient V135 – celá rozbalená poznámka

Navazuje na V134 a obsahuje předchozí opravy.
Odstraněn výškový limit, vnitřní posouvání a sticky pozice levého sloupce karty zákazníka na PC. Sloupec nyní roste s celou poznámkou a posouvá se společně se stránkou. Sekce Možnosti je pod celým obsahem i při krátké pravé záložce. Menu se při rozbalení nesmršťuje.

Nahrajte hs-client-ui a str, původní str/strana ponechte. Potom Ctrl+F5.
Ověřeno lokálně v Chrome na 390/844/1366 px: rozbalení a sbalení bez změny pozice menu, celá karta poznámky uvnitř sloupce a nad sekcí Možnosti i s krátkým pravým obsahem. Hodnota poznámky zůstává zachována. Změna pouze CSS a verze jeho načítání, bez změn ukládání dat. Na server nebylo nasazeno.
