# HairSoft Klient V172

Výchozí stav: V171.

## Změny

1. **Oprava hesla při Změně obsluhy**
   - úvodní `loginObsluha.php` historicky ověřuje `TRIM(k_poduzivatele_obsluha_jmeno)`,
   - V170/V171 při výběru karty ořezaly jméno, ale SQL při přehlášení vyžadovalo přesnou rovnost původního DB jména,
   - účet s historickou mezerou v názvu proto mohl fungovat při úvodním loginu a selhat v modalu,
   - V172 při přehlášení ověřuje obsluhu podle `SW ID + interní ID + heslo + aktivní stav + povolení k přehlášení`; jméno zůstává pouze prezentační údaj.

2. **Povolit přepnutí na tuto obsluhu**
   - v detailu obsluhy v sekci Uživatelé je nový moderní přepínač,
   - používá již existující databázové pole `k_obsluha_moznostPrehlaset`,
   - po změně se vrátí na stejnou pobočku a stejný detail obsluhy,
   - doplněny překlady CZ/SK/EN/DE.

Databázové schéma se nemění.
