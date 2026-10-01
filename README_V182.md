# V182 – Hodnocení: Google recenze / fáze 1 (design)

Vychází z V181.

## Rozsah
Pouze UI/UX fáze. Bez změny databáze, cronů, skutečného odesílání SMS nebo serverové logiky.

## Text SMS
- přidán přepínač cíle SMS: Interní hodnocení / Google recenze,
- interní režim zachovává původní form a jeho serverové ukládání,
- Google režim je pouze frontendový návrh,
- přidáno pole Odkaz na Google recenze,
- přidáno samostatné textové pole Text SMS pro Google recenze,
- zachována proměnná %ODKAZ_SPOKOJENOST%,
- zobrazeny dostupné proměnné,
- přidán návrh výchozí Google SMS šablony,
- tlačítko Uložit Google nastavení ve fázi 1 nic neukládá a pouze zobrazí informaci.

## Důležité
- Email zůstává výhradně na interním dotazníku.
- Testovací SMS zůstává ve V182 beze změny a stále používá stávající interní logiku.
- Nová Google pole nemají POST name a nemohou tedy omylem změnit existující backendová data.
- Přepnutí režimu není ve V182 perzistentní a po reloadu se vrátí na Interní hodnocení.

## Překlady
Nové UI texty CZ/SK/EN/DE.
