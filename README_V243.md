# HS Klient V243 – trvalá lokální autorizace PROGRAMS Bridge

V243 navazuje přímo na V242 a odstraňuje 60sekundové opakované ověřování PROGRAMS action/photo endpointu proti externímu Bridge Directory.

## Nové chování
- Při prvním požadavku, pro který na HS Klient serveru ještě neexistuje lokální vazba, se Bridge jednou ověří proti Directory stejně jako dosud.
- Po úspěšném ověření se do lokální MySQL HS Klient uloží pouze:
  - `bridge_id`,
  - SHA-256 hash Bridge tokenu,
  - `sw_id`,
  - čas posledního Directory ověření.
- Samotný Bridge token se na HS Klient serveru v této tabulce neukládá.
- Následující pravidelné `ACTION` a `PHOTO` požadavky se ověřují pouze lokálně podle `bridge_id + hash(token)`.
- Skupina se při každém požadavku čte z aktuálního `sw_info.sw_skupina_id`, takže přeřazení PC do jiné skupiny se projeví bez kontaktování Directory.
- Aktivní HS Klient licence se při každém požadavku dál kontroluje lokálně v `sw_email_pobocka`.

## Kdy se Directory použije znovu
Directory se v běžném pollingu nepoužívá. Použije se pouze tehdy, když pro dané `bridge_id + token` není platná lokální vazba – typicky při prvním použití nebo po změně tokenu / novém spárování.

## Databáze
Endpoint si automaticky vytvoří izolovanou tabulku:

`hsbridge_program_pc_bindings`

Tabulka nijak nemění databázi HairSoft a nezasahuje do běžné práce programu HairSoft.

## Bridge
Windows HSBridge se nemění. Provozní verze zůstává **V013**.

## Nasazení
Pro změnu stačí nahrát:

`/str/api/hsbridge-program-actions.php`

Po prvním úspěšném lokálním svázání už pravidelný 10sekundový polling ACTION/PHOTO není závislý na dostupnosti externího Directory.