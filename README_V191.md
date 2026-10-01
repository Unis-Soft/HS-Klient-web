# V191 – Přepínání firem + dlouhodobé přihlášení

Navazuje na **KLIENT_HAIRSOFT_V190_GOOGLE_KRATKY_ODKAZ**. Google recenze a V190 se touto verzí nemění.

## Nové chování
- V horní liště je přepínač firmy.
- Aktuální firma je označena a další uložené firmy lze otevřít jedním kliknutím bez opětovného zadávání hesla.
- `Přidat firmu` jednorázově ověří login + heslo, založí bezpečné dlouhodobé přihlášení a ihned na firmu přepne.
- Každá firma si pamatuje poslední vybranou pobočku.
- `Odhlásit tuto firmu` odebere pouze aktivní účet; pokud je na zařízení uložen další účet, Klient na něj bezpečně přejde.
- `Odhlásit všechny firmy` zruší všechny dlouhodobé tokeny tohoto zařízení a ukončí session.
- Z přepínače lze jednotlivou neaktivní firmu také odpojit.

## Dlouhodobé přihlášení
- Platnost remember tokenu je 365 dní.
- Heslo se **nikdy neukládá do cookie ani do nové tabulky**.
- V prohlížeči je Secure + HttpOnly + SameSite=Lax cookie s náhodným tokenem.
- V databázi je pouze SHA-256 hash validatoru tokenu.
- Změna hesla konkrétního účtu jeho dlouhodobý token automaticky zneplatní díky `credential_fingerprint`.
- Při každém switchi se účet znovu načte z DB a kontroluje se aktivní stav hlavního účtu.
- Stavové POST akce používají CSRF token a po změně identity se regeneruje PHP session ID.

## Typy účtů
Přepínač podporuje:
- hlavního administrátora,
- manažera / poduživatele,
- obsluhu.

DEMO účet se dlouhodobě neukládá.

## Databáze
Nová izolovaná tabulka:
`k_klient_remember_tokens`

Aplikace ji umí vytvořit automaticky. Stejný CREATE je také v:
`sql/SQL_V191_MULTI_FIRMA_PRIHLASENI.sql`

## Nové soubory
- `hs-client-ui/php/multi_company_auth.php`
- `hs-client-ui/php/company-switch.php`
- `str/company-switch.php`
- `hs-client-ui/css/company-switch.css`
- `hs-client-ui/js/company-switch.js`
- `sql/SQL_V191_MULTI_FIRMA_PRIHLASENI.sql`

## Upravené soubory
- `hs-client-ui/php/login.php` – automatické obnovení dlouhodobého přihlášení.
- `hs-client-ui/php/index.php` – switch UI, registrace aktuálního účtu, obnova a uložení poslední pobočky, bezpečné odhlášení.

## Biometrie
V191 biometrii **neřeší**. Tokenová architektura je ale připravena tak, aby na ni šlo později navázat bez ukládání hesel.

## Doporučený test po nasazení
1. Otevřít Klient pod již přihlášenou firmou A – V191 ji automaticky zařadí mezi zapamatované účty.
2. V horní liště zvolit `Přepnout firmu` → `Přidat firmu` a jednou zadat login + heslo firmy B.
3. Přepnout A → B → A bez dalšího zadávání hesla.
4. V A vybrat jinou pobočku, v B jinou pobočku a ověřit, že návrat do každé firmy obnoví její poslední pobočku.
5. Zavřít prohlížeč, znovu otevřít `klient.hairsoft.cz` a ověřit automatické přihlášení naposledy aktivní firmy.
6. `Odhlásit tuto firmu` musí zrušit pouze aktivní firmu; `Odhlásit všechny firmy` musí vyčistit všechna zapamatovaná přihlášení tohoto zařízení.

Poznámka: změna hesla nebo odvolání tokenu zneplatní dlouhodobé přihlášení. Již běžící PHP session se tímto násilně neukončuje, ale sama si bez nového zadání hesla nevytvoří nový remember token.
