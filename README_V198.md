# V198 – Multi-firma: Bonfero + obousměrná TEST kopie zákazníka

Navazuje přímo na V197.

## 1. Paměť Rezervace → Bonfero

V194 ukládal poslední bezpečnou pracovní sekci, ale `RezervaceBonfero` nebyla v povoleném seznamu. Proto se tato stránka neuložila do `last_page`.

V198 přidává `RezervaceBonfero` mezi bezpečné obnovitelné sekce. Po přepnutí firmy A → B → A se firma A vrátí přímo na Rezervace → Administrace Bonfero, pokud tam byla naposledy.

Neukládá se URL ani stav uvnitř Bonfero iFrame; ukládá se pouze bezpečný název sekce HS Klient.

## 2. Proč V197 neviděl DC Jihlava jako cílovou firmu

V195 byla TEST kopie záměrně první bezpečnostní verzí a striktně povolovala pouze účty typu `admin` jako zdroj i cíl.

Ve switchi je DC Jihlava přihlášena jako `manager`, takže byla ve switchi správně vidět, ale V195/V197 ji z nabídky TEST kopie záměrně odfiltrovaly. Proto Barber Shop Cooper hlásil, že není připojena další administrátorská firma.

## 3. V198 – administrátor i manažer

Zdrojovou firmou je vždy právě aktivní firma ve switchi. Neexistuje žádná pevná firma A.

Povoleno:
- administrátor,
- manažer, pokud má na konkrétní pobočce právo `NovyZakaznik` a přístup k pobočce.

Nepovoleno:
- obsluha/staff.

Cílové firmy se berou ze všech ostatních bezpečně zapamatovaných účtů ve switchi. U každého cílového manažera se zobrazí jen pobočky, kde má právo pracovat se zákazníkem.

Prakticky tedy funguje:
- Barber Shop Cooper → DC Jihlava,
- DC Jihlava → Barber Shop Cooper,
- A → B/C/D,
- B/C/D → A nebo mezi sebou,

pokud jsou obě firmy ve switchi a účet má potřebné oprávnění.

## Bezpečnost

- zdrojový zákazník se stále nikdy nemění ani nemaže,
- cílová kopie používá nový GUID,
- duplicity, transakce, rollback, fotografie a timeline z V195 zůstávají beze změny,
- cleanup TEST kopie nyní umí najít cílovou firmu i přes zapamatovaný manažerský login s odpovídajícím právem,
- databázová struktura se nemění.

## Beze změny

- V197 typografie menu a oprava hoveru grafů,
- V196 Tahoma mimo levé menu,
- V195 logika samotného vytváření dat zákazníka/timeline/fotek,
- V190 Google hodnocení a dotaznik.net.
