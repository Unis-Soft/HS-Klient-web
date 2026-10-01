# HairSoft Klient V177 – moderní Přístupy

Výchozí stav: V176.

## Co je nové
- nativní moderní override `NastaveniPristupy.php`
- nativní moderní override `NastaveniDetailUzivatele.php`
- nový `access.css` a `access.js`
- přehled uživatelů: Voucher-style tabulka, Search vlevo, bez Exportu
- klikací řádky + mobilní karty
- moderní správa profilovky manažera
- moderní založení nového manažera
- detail manažera: identita, pobočka, změna hesla, 2sloupcová mřížka oprávnění
- vlastní přepínače oprávnění bez legacy Switchery/duplicitních ID
- CZ/SK/EN/DE překlady
- přímý vstup do Přístupů omezen stejně jako menu jen na hlavního administrátora

## Zachované funkce
- databázové tabulky a názvy polí beze změny
- založení a smazání manažera
- odesílání přihlašovacích údajů e-mailem
- SHA-1 ukládání hesla dle stávající logiky
- upload a smazání profilovky přes existující `NahratSoubor.php`
- práva podle pobočky v `k_poduzivatele_prava`
- změna hesla včetně e-mailu

## Bez Exportu
Toolbar seznamu obsahuje pouze vyhledávání vlevo, podle zadání.
