# V183 – Hodnocení: Google recenze / fáze 2

Vychází z V182.

## Hlavní princip
Google recenze jsou přidány jako samostatná SMS větev. Původní interní hodnocení zůstává zachované jako výchozí a bezpečný fallback.

## Klient HairSoft
- přepínač Interní hodnocení / Google recenze je perzistentní pro pobočku + středisko,
- Google režim ukládá samostatně Google URL a samostatnou Google SMS šablonu,
- `%ODKAZ_SPOKOJENOST%` se v Google režimu nahrazuje uloženým Google URL,
- interní SMS nadále používá původní tabulku `HodnoceniTextSMS`,
- uložení Google nastavení do `HodnoceniTextSMS` vůbec nezapisuje,
- návrat na Interní hodnocení pouze vypne Google příznak; původní interní text zůstává zachovaný,
- testovací SMS používá právě uložený aktivní režim,
- testovací email zůstává vždy interní.

## Databáze
Nová konfigurace je izolovaná v tabulce `HodnoceniGoogleSMS`.
Akční PHP ji při prvním uložení Google nastavení umí vytvořit samo přes `CREATE TABLE IF NOT EXISTS`.
Pro ruční založení je přiložen `sql/SQL_V183_GOOGLE_RECENZE.sql`.

## dotaznik.net
Soubor `cron_hodnoceni.php` má pouze paralelní Google větev uvnitř SMS části:
- pokud tabulka neexistuje, cron jede původní interní logikou,
- pokud záznam neexistuje nebo Google není aktivní, jede původní interní logikou,
- pokud je Google aktivní, ale URL/text nejsou validní, jede původní interní logikou,
- pouze při validním aktivním Google nastavení se pro SMS přepne šablona a odkaz,
- emailová větev je beze změny.

## Nasazení
1. Nahrajte klientský balíček V183 na klient.hairsoft.cz stejně jako předchozí verze.
2. Nahrajte `cron_hodnoceni.php` z balíčku DOTAZNIK_NET_V183 do kořene dotaznik.net.
3. SQL není nutné spouštět ručně, pokud DB účet klienta smí CREATE TABLE; pro kontrolované nasazení lze nejprve spustit přiložený SQL soubor.
4. Nejdřív otestujte Interní hodnocení testovací SMS, poté Google režim.

## Bezpečnost původní větve
Původní SELECT cronu, původní `HodnoceniTextSMS`, interní unikátní odkaz `www.dotaznik.net/<kod>`, nahrazování proměnných a emailová větev nejsou nahrazeny. Google se zapíná až jako následný override SMS hodnot po splnění všech podmínek.
