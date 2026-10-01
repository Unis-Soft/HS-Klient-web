# HairSoft Klient V114 – sjednocení horních karet

Denní tržby nyní zachovávají společné KPI karty vytvářené menu.js, stejně jako měsíční tržby. Odstraněn přepis na samostatný kompaktní denní vzhled. Sdílené karty mají barevný horní okraj, ikonu vpravo, popisek nahoře a zvýrazněnou hodnotu dole. Hodnoty, překlady popisků a nápověda s celou hodnotou zůstávají zachovány.

Proti V113 se mění pouze hs-client-ui/js/daily-revenue.js a hs-client-ui/php/index.php (cache verze denního JS 114), plus dokumentace verze.

Ověření: kontrola syntaxe JS a PHP 8.3.33. Lokální Chrome při 1600, 768 a 375 px porovnal denní a sdílené měsíční karty: výška, zaoblení, horní okraj, barva a velikost hodnoty, velikost popisku, přítomnost ikony a původní hodnoty jsou shodné. Testy používají ukázková data; balíček nebyl nasazen na server.

Nasazení: nahrajte složky hs-client-ui a str do kořene webu. Při nasazené kompletní V113 stačí výše uvedené dva soubory. Poté Ctrl+F5.
