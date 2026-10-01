# HairSoft Klient V134 – rozbalení poznámky

Navazuje na V133, všechny předchozí opravy jsou zahrnuty.
Karty v levém sloupci se při rozbalování poznámky již nesmršťují. Menu zachovává celou výšku, poznámka se rozšiřuje dolů a obsah delší než sloupec lze posouvat. Vypnuto automatické ukotvování posuvu tohoto sloupce při změnách jeho obsahu.

Nahrajte hs-client-ui a str. Původní str/strana ponechte. Potom Ctrl+F5.
Ověřeno lokálně v Chrome při šířkách 390, 844 a 1366 px: výška a poloha menu i horní hrana poznámky se při rozbalení/sbalení nemění. Obsah poznámky zachován. Změna pouze CSS a verze jeho načítání; ukládání poznámky se nemění. Na server nebylo nasazeno.
