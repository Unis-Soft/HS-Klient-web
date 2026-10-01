# HairSoft Klient V138 – doladění Hodnocení

Navazuje na V137 a obsahuje všechny předchozí opravy.
- Počet reakcí je uvnitř každého ze šesti grafů. U prázdného grafu je pod textem Nehodnoceno.
- Samostatně přeložitelné popisky Reakce a Otázka s ponechaným číselným údajem; CS/SK/EN/DE.
- Původní odkazy pro změnu měsíce a roku jsou znovu dostupné v samostatném vycentrovaném ovládání nad poslední tabulkou. Nejsou součástí skrytých nástrojů panelu. Po načtení je z adresy odstraněn příkaz změny období, aby F5 znovu neposunulo měsíc/rok.
- Hvězdy u hodnocení obsluh jsou zarovnané s číslem pomocí flex rozložení, hvězdy kategorií mají drobnou optickou korekci.

Nahrajte hs-client-ui a str, původní soubory ponechte. Potom Ctrl+F5.
Ověřeno v Chrome na 390 a 1366 px: šest popisků uvnitř grafů, čtyři viditelné původní odkazy období, překlady Reakce a Otázka do angličtiny a žádný vodorovný přesah stránky. PHP a databázové dotazy se nemění. Na server nebylo nasazeno.
