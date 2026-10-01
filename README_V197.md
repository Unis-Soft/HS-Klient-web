# V197 - Menu a stabilni hover grafu

Navazuje primo na V196.

## 1. Leve menu
- Globalni Tahoma zustava v celem HS Klient.
- Pouze textove polozky leve navigace jsou vraceny na puvodni font z V195:
  `Open Sans, Helvetica, Arial, sans-serif`.
- Velikost, tucnost, barvy, rozestupy, ikony a Odhlasit se nemeni.

## 2. Grafy - odstraneni kratkeho poskoceni tooltipu
- Tahoma se zapisuje do `Chart.defaults.global` na kazde strance jeste pred vytvorenim legacy Chart.js grafu.
- Puvodni problem vznikal tim, ze mimo Dashboard se Tahoma casto nastavila az modernizacni vrstvou po `window.onload`; prvni tooltip tak mohl byt zmeren puvodnim fontem a nasledne prepocitan Tahomou.
- Globalni hover animace Chart.js je bez prodlevy.
- Denni trzby jiz neprepisuji hover animaci na 160 ms.
- Vlastni HTML tooltip Dashboardu nema svisly transform; zobrazuje se pouze kratkym fade bez zmeny polohy/velikosti.

## Beze zmeny
- Tahoma ve vsech ostatnich sekcich V196.
- velikosti a font-weight textu,
- databaze a SQL,
- synchronizace HairSoft,
- multi-firma V191-V194,
- TEST kopie zakaznika V195,
- Google hodnoceni a dotaznik.net.
