# V195 – TEST kopie zákazníka mezi firmami

Navazuje přímo na V194. Tato verze je záměrně testovací krok před případným budoucím skutečným přesunem zákazníka.

## Cíl testu
Ověřit celý řetězec:

1. HS Klient – firma A → vytvoření samostatné kopie ve firmě B,
2. kontrola kopie v HS Klient firmy B,
3. stávající synchronizace firmy B → HairSoft PC / skupina B,
4. kontrola zákazníka, timeline a fotografií v HairSoft.

Firma A se při této operaci nikdy nemaže, nedeaktivuje ani jinak neupravuje.

## Kde je funkce
Karta zákazníka → **Možnosti** → **Zkopírovat do jiné firmy** s viditelným štítkem **TEST**.

Funkce je ve V195 z bezpečnostních důvodů dostupná jen hlavnímu administrátorovi. Cílové firmy se berou pouze z firem, které jsou v tomto prohlížeči bezpečně přidané v novém přepínači **Přepnout firmu**. Aktuální firma se nenabízí.

Po výběru cílové firmy se nabídnou pouze její pobočky/skupiny, ke kterým má daný administrátorský účet právo.

## Co se kopíruje
Do cílové firmy vzniká nový zákazník s novým GUID a se stavem nového webového záznamu pro existující synchronizaci HairSoft:

- jméno, příjmení, titul, pohlaví,
- e-mail, telefon, mobil a adresa,
- datum narození,
- firemní údaje a kontakty,
- hlavní poznámka,
- pojišťovna, ostatní/zdravotní údaje a rodné číslo,
- stav zákazu SMS se z bezpečnostních důvodů zachovává,
- platná timeline,
- aktivní fotografie včetně profilové fotografie.

Timeline se v cíli vytváří jako nové záznamy (`timelineDoPCZnak='N'`, nové PC ID je NULL) a používá cílové SW ID. Cizí interní ID obsluhy ze zdrojové firmy se nepřenáší; používá se bezpečné systémové ID 1, stejně jako při novém zápisu hlavním administrátorem bez navázané obsluhy.

Fotografie se fyzicky duplikují pod novými náhodnými názvy. Metadata dostanou nový GUID zákazníka a `obrazek_stazeno=0`, aby je cílový HairSoft viděl jako nové ke stažení. Profilová fotografie zůstává profilovou.

## Co se záměrně nekopíruje
- číslo zákaznické karty,
- bonusové / loyalty body,
- blacklist,
- tržby a účtenky,
- rezervace,
- SMS a hovory,
- hodnocení,
- soubory/dokumenty.

Tyto údaje historicky patří zdrojové firmě nebo vyžadují samostatný návrh synchronizace.

## Ochrana před duplicitami
Před vytvořením kopie se kontroluje cílová HairSoft skupina. Pokud v ní existuje aktivní zákazník se stejným mobilem, telefonem nebo e-mailem, kopie se nevytvoří. Stejně tak se nedovolí vytvořit druhou aktivní TEST kopii stejného zákazníka do stejné cílové pobočky.

Zdrojová a cílová pobočka se stejnou HairSoft skupinou jsou z bezpečnostních důvodů blokovány.

## Databáze – oddělení a rollback
Stávající struktura tabulek `klient_lidi`, `timeline` a `klient_lidi_obrazky` se **nemění**.

V195 přidává pouze samostatnou pomocnou tabulku:

`k_klient_customer_copy_test_log`

Ta obsahuje auditní vazbu zdrojový GUID → cílový GUID, cílovou firmu/pobočku, počty přenesených položek a stav testu. Tabulka nemění běžnou synchronizaci HairSoft a lze ji později odstranit bez změny stávajících zákaznických tabulek.

Tabulka se vytvoří automaticky až při prvním reálném pokusu o TEST kopii. Stejné SQL je přiloženo v `sql/SQL_V195_TEST_KOPIE_ZAKAZNIKA.sql`.

Samotné vytvoření kopie používá databázovou transakci. Navíc je implementován kompenzační úklid pro případ legacy netransakčních tabulek: při chybě se podle nového cílového GUID odstraní pouze právě vytvářená cílová data a fyzicky nakopírované fotografie. Zdrojový GUID se nikdy nemaže ani nemění.

## Odstranit testovací kopii
Na zdrojové kartě se zobrazuje historie TEST kopií.

- Pokud cílový zákazník stále čeká na první synchronizaci (`lidi_web_pc='N'`), lze TEST kopii odstranit přímo z webové DB včetně její timeline a duplikovaných fotografií.
- Pokud už ji HairSoft pravděpodobně převzal, nepoužije se tvrdé smazání. Cílový zákazník se označí standardním existujícím způsobem `lidi_web_pc='D'`, `lidi_aktivni=0`, aby odstranění proběhlo přes běžnou synchronizaci cílové firmy.
- Zdrojová firma A zůstává v obou případech beze změny.

## Co V195 nemění
- V194 paměť poslední pobočky/sekce,
- V193 vzhled a Open Sans font přepínače firem,
- původní červené tlačítko Odhlásit z V190/V193,
- V190 Google recenze a krátké odkazy,
- dotaznik.net,
- biometrii.

## Test po nasazení
1. Přihlásit ve switch režimu firmu A a B.
2. Ve firmě A otevřít testovacího zákazníka.
3. Karta zákazníka → Možnosti → Zkopírovat do jiné firmy (TEST).
4. Vybrat firmu B a cílovou pobočku.
5. Přepnout do B a zkontrolovat zákazníka, timeline a fotografie v HS Klient.
6. Spustit / nechat proběhnout běžnou synchronizaci HairSoft B.
7. Zkontrolovat zákazníka, timeline a fotografie v cílovém HairSoft PC/skupině.
8. Ověřit, že zákazník ve firmě A zůstal zcela beze změny.

Druhou fázi – skutečný přesun se smazáním A – nezapínat, dokud tento test neprojde.
