# HSBridge -> HS Klient server V002

This folder contains the first server endpoint for the HS Klient module.

## Target path

Copy `hsbridge.php` to the HS Klient server as:

`/str/api/hsbridge.php`

It uses the existing HS Klient MySQL connection from:

`/cfg/nastaveni.php`

## Identity mapping

The endpoint never trusts `sw_id` or group ID sent by the PC.

1. HSBridge authenticates with its existing Bridge ID/token.
2. The endpoint validates that token against the central Directory.
3. Directory returns the SoftRC identity bound to that Bridge:
   - RC V1 `Setting3`
   - RC V1 `Setting10`
4. HS Klient resolves:
   - `sw_info.sw_sn = Setting3`
   - `sw_info.sw_dodatkove = Setting10`
5. `sw_info.sw_skupina_id` is the authoritative group.
6. Server additionally checks `sw_email_pobocka` for that `sw_id`.

Therefore changing only the local `HS SYS\Dashboard` registry value is not sufficient to enable the module.

## V002 test scope

Only customer `492` is accepted.

CUSTOMER:
- `klient_lidi.lidi_hs_note`
- `klient_lidi.lidi_hs_loyalityPoints`

STATISTICS:
- `klient_lidi_statistika.stat_PosledniNavsteva`
- `klient_lidi_statistika.stat_PristiNavsteva`
- `klient_lidi_statistika.stat_updated`

If both sides have a non-empty GUID, `customer.KlientGuid` must equal `klient_lidi.lidi_guid`.

The endpoint creates `hsbridge_sync_audit` and `hsbridge_statistics_audit` automatically. Existing HS Klient tables are not created or migrated.

## Routes

- `GET ?route=status`
- `POST ?route=customer/test`
- `POST ?route=statistics/test`

Both require:
- `X-HS-Bridge-ID`
- `X-HS-Bridge-Token`


## V011 - reverse PROGRAMS actions

For HS Klient V237 also copy `hsbridge-program-actions.php` to:

`/str/api/hsbridge-program-actions.php`

It uses the same Directory/SoftRC identity mapping as the HS Klient bridge endpoint.
The endpoint never accepts `sw_id` or group ID from the Windows client. It resolves
them from the authenticated Bridge identity and `sw_info`.

Routes:

- `GET ?route=next` - lease one pending PROGRAMS consumption command for the authenticated PC/group.
- `POST ?route=result` - acknowledge the command as `done`, `retry`, or `failed`.

The queue lives in HS Klient MySQL table `hsbridge_program_commands`. No table is
added to the HairSoft database. The Windows Bridge performs the local HairSoft
`program_visits` INSERT and uses a zero SQLite busy timeout so HairSoft has priority.


## V243 - PROGRAMS persistent local authorization

`hsbridge-program-actions.php` no longer revalidates a healthy Bridge against the external Directory on a timer.
The first successful Directory verification stores `bridge_id`, SHA-256(token) and `sw_id` in the local HS Klient MySQL table `hsbridge_program_pc_bindings`.
Subsequent ACTION/PHOTO polls authenticate locally; the current group and HS Klient license are still read locally on every request. Directory is used again only when the matching local binding is missing (for example first use or token change).