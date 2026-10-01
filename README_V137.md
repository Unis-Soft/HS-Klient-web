# HairSoft Klient V137 – Hodnocení

Navazuje na V136 a obsahuje všechny předchozí opravy. Výchozí PHP byla porovnána s oběma dodanými soubory; odpovídají původní záloze.

Přehled hodnocení:
- Moderní karty obsluh s původní fotografií uvnitř přesně kruhového grafu, jednotnou paletou a tooltipem.
- Responzivní rozložení karet a šesti kategorií, přepínání aktivních a archivovaných obsluh.
- Jednotný font, hvězdičky bez deformovaných obrázků, prázdné grafy s označením Bez hodnocení.
- Tabulka využívá původní ověřenou úpravu z karty zákazníka: hledání, exporty a mobilní karty s odpověďmi. Nad širokou tabulkou je druhý synchronizovaný posuvník.

Nastavení:
- Sjednocené panely, formuláře, přepínače, tlačítka a editory textu e-mailu a SMS.
- Zachována původní pole, hodnoty, adresy formulářů, střediska, otázky a akce odesílání.
- Doplněny překlady běžného rozhraní CS/SK/EN/DE. Uživatelské texty dotazníků se automaticky nepřekládají.
- Společná patička: statistika uvádí poslední změnu a původní interval; nastavení dobu načtení.

Ošetřeny nulové jmenovatele výpočtů při prázdných datech, které v PHP 8 mohou přerušit načítání. Nenulové výpočty a dotazy zůstávají stejné. Formát poslední změny je d.m.Y H:i; původní neescapované v v PHP 8 vypisovalo milisekundy.

Nasazení:
Nahrajte hs-client-ui a str, potom Ctrl+F5. Původní str/strana, str/akce, fce a fotografie ponechte. Balíček se nenasazuje automaticky. Synchronizace poznámek ani dat klientů nebyla měněna.

Ověření:
- Syntaxe PHP/JS a porovnání PHP tokenů s originálem (kromě upravených cest include, nulových guardů, inicializace čítače a formátu data).
- Lokální vykreslení obou stránek se simulovanými údaji, statistika také bez dat; žádná živá databáze nebyla použita.
- Chrome na šířkách 390/1366: bez vodorovného přesahu stránky, správné formuláře a identifikátory, překlady, archivace, šest viditelných grafů, nulový stav.
- Skutečné vykreslení v Chart.js 2.3, hover tooltip fotografie a správný čas poslední změny v patičce.
- Vizuální kontrola PC/mobil. Fotografie v testovacím prostředí nahrazeny neutrálním testovacím obrázkem; produkční cesty k fotografiím se nemění.

Na živém serveru nebylo testováno odesílání dotazníků, změny nastavení ani jiné zápisové akce.
