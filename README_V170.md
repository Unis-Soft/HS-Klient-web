# HairSoft Klient V170 – moderní Změna obsluhy

Výchozí stav: V169.

## Změny
- Nový moderní override `hs-client-ui/php/strana/ZmenaObsluhy.php`.
- Responzivní bílé karty obsluh s kruhovým avatarem, jménem a akcí Přepnout obsluhu; bez dalšího duplicitního horního panelu.
- Kliknutí na kartu/avatar otevře vlastní moderní dialog bez legacy Bootstrap modalu.
- Dialog zobrazuje vybranou fotografii a jméno; uživatel zadává pouze heslo.
- Chybné heslo znovu otevře dialog u stejné obsluhy s moderní chybovou hláškou.
- Přihlášení používá připravený SQL dotaz a kontroluje, že obsluha je aktivní a má povoleno přehlášení.
- Při úspěšné změně se aktualizují identifikátory obsluhy, fotografie, privacy flag a před načtením nových práv se vyčistí staré seznamy oprávnění.
- CZ/SK/EN/DE překlady.
- Samostatné `staff-switch.css` a `staff-switch.js`.

## Beze změny
- Databázová struktura.
- Ostatní sekce aplikace.
- V169 oprava načítání zákazníků obsluhy.
