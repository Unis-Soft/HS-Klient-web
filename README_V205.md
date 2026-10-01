# V205 – ruční HairSoft ID a Timeline po jednotlivých řádcích

V205 navazuje přímo na V203. Automatickou sériovou logiku z experimentální V204 nepoužívá.

## Proč je V205 ruční
Reálný test ukázal dvě vlastnosti legacy synchronizace HairSoft:
- fotografie a soubory lze přenést, ale musí už existovat správné lokální HairSoft ID zákazníka; jinak mohou skončit pod dočasným ID `11111111`,
- více Timeline záznamů lze v jednom přenosu vytvořit, ale legacy proces při testu zopakoval obsah jednoho záznamu do všech nově vytvořených řádků,
- HairSoft neposkytuje spolehlivé zpětné potvrzení, podle kterého by HS Klient mohl automaticky poznat dokončení každého Timeline řádku.

Proto je TEST kopie mezi firmami od V205 řízena administrátorem / oprávněným manažerem.

## Nový postup TEST kopie A → B
1. V HS Klient vytvořte TEST kopii zákazníka do cílové firmy B.
2. Základní zákazník se připraví pro HairSoft, ale Timeline, fotografie a soubory zůstanou v HOLD stavech.
3. Nechte proběhnout synchronizaci HairSoft B, aby se v desktopovém HairSoft vytvořil samotný zákazník.
4. V HairSoft zjistěte jeho skutečné lokální ID, například `798`.
5. V cílové kartě zákazníka v HS Klient otevřete **Správa dat → Dokončit synchronizaci do HairSoft**.
6. Zadejte skutečné HairSoft ID.
7. HS Klient:
   - uloží toto ID do cílového zákazníka,
   - zruší původní příznak nového zákazníka, aby se nemohl založit znovu,
   - uvolní fotografie z `2` na `0`,
   - uvolní soubory z `2` na `0`,
   - Timeline ponechá v `H`.
8. V záložce Timeline má každý čekající řádek vlastní synchronizační tlačítko. Kliknutí uvolní pouze konkrétní řádek `H → N`.
9. Po kliknutí nechte HairSoft provést synchronizaci a až poté připravte další Timeline řádek.

## Bezpečnost
- Zdrojová firma A se nemění.
- ID lze zadat pouze u aktivní TEST kopie v cílové firmě.
- ID musí být kladné číslo a nesmí být dočasné `11111111`.
- Pokud stejné HairSoft ID už používá jiný aktivní zákazník stejné cílové pobočky, operace se zastaví.
- Pokud už má kopie jiné skutečné HairSoft ID, V205 ho bezpečnostně nepřepíše.
- Timeline tlačítko funguje pouze pro řádek v HOLD stavu `H`; po jeho uvolnění se na tomto řádku znovu nezobrazí.
- Oprávněný manažer má stejnou možnost jako v obousměrné kopii V198 pouze na pobočkách, kde má právo pracovat se zákazníky.

## Běžní noví zákazníci
V205 mění ruční režim pouze pro zákazníky vytvořené funkcí TEST kopie mezi firmami. Běžní noví zákazníci založení přímo v HS Klient nadále používají ochranu / automatiku zavedenou ve V202.

## Databáze
V205 nepřidává žádnou další tabulku ani sloupec. Používá existující:
- `k_klient_customer_copy_test_log`,
- `k_klient_customer_sync_hold`,
- `timeline.timelineDoPCVlozeno`.

## Důležité
- **V204 nenasazovat. V205 ji nahrazuje.**
- V205 vychází z V203, takže obsahuje i V202 a UI změny V203.
- Produkční `/str/strana/NahratSoubor.php` z V202 zůstává součástí balíčku.
- Po nasazení použijte Ctrl+F5.
