# HairSoft Klient V129 – společný kalendář a překlady voucherů

Kompletní balíček navazuje na V128.

- Nový vzhled společného kalendáře: zaoblení, stín, čisté šipky, označení dnešního a vybraného dne, větší dotykové plochy na mobilu.
- Vlastní kompletní lokalizace kalendáře pro češtinu, slovenštinu, angličtinu a němčinu. Překládají se měsíce, dny, zkratky, názvy dnů v nápovědě a ovládání. Jazyk lze měnit i při otevřeném kalendáři.
- Kalendář se již nepřekládá nahodilou výměnou textů obecným překladačem; používá jednotná nastavení jazyka.
- Zachované hodnoty polí, formát dd.mm.yyyy, navigace mezi měsíci a původní reakce formulářů včetně automatického odeslání data denních tržeb.
- Doplněno Vše, Všechny pobočky, text aktivního filtru včetně stavu, pobočky a prodejce, běžné platební metody a potvrzení úspěšného smazání. Jména poboček, prodejců a data ve složeném textu se nepřekládají.
- Logika filtru Expirováno se nemění. PHP stránek voucherů je totožné s V128.

## Nahrání

Nahrajte složky **hs-client-ui** a **str** do kořene webu, potom Ctrl+F5. Původní **str/strana ponechte**.

Změny oproti kompletní V128:
- hs-client-ui/css/calendar.css (nový)
- hs-client-ui/js/calendar.js (nový)
- hs-client-ui/js/i18n.js
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

## Kontroly

Lokální Chrome se stejným jQuery UI 1.12.1 jako v aplikaci: 390, 844 a 1440 px, všechny čtyři jazyky, překlad otevřeného kalendáře, přechod září/říjen a výběr dne, zachování formulářových hodnot při překladu, nepřetékající umístění kalendáře, předání českého formátu data a jediné vyvolání původního onSelect. Vizuálně ověřeno. PHP a JS syntaxe zkontrolována; výpočty a SQL voucherů nezměněny.

Na živý server nebylo nasazeno a skutečná data nebyla upravována.
