# V206 – oprava ručního potvrzení ID a absolutní blokace automatické Timeline

V206 navazuje přímo na V205. Nevrací automatiku V204.

## Co odhalil reálný test V205
- HairSoft může po založení zákazníka zapsat skutečné `lidi_hs_id` zpět do webové databáze ještě před ručním potvrzením uživatelem.
- V205 proto chybně považovala pouhou existenci reálného ID za stav „ručně dokončeno“ a tlačítko **Dokončit synchronizaci do HairSoft** se mohlo skrýt dříve, než na něj uživatel klikl.
- Starší TEST kopie vytvořené ve V202/V203 mohou mít ve frontě stále `status=waiting`. V205 heartbeat filtroval pouze podle tohoto statusu, takže takovou kopii mohl po návratu reálného ID automaticky uvolnit.

## Oprava V206
1. Ruční dokončení se už neurčuje podle samotného `lidi_hs_id`. Autoritativní je výhradně `status=manual_ready`, který vznikne až po explicitním potvrzení uživatelem.
2. **Dokončit synchronizaci do HairSoft** zůstává viditelné i tehdy, když HairSoft mezitím sám zapsal skutečné ID. Známé ID se v modalu předvyplní k ověření.
3. Heartbeat má absolutní pojistku: GUID aktivní TEST kopie nikdy automaticky neuvolní. Starý `waiting` stav normalizuje na `manual`.
4. Timeline tlačítka jsou aktivní až po ručním potvrzení ID (`manual_ready`).
5. Fotografie a soubory přidané před ručním potvrzením zůstávají HOLD i při již známém HairSoft ID.
6. Již dříve uvolněné Timeline řádky se automaticky nevracejí do HOLD, protože bez ACK z HairSoft nelze bezpečně určit, co už bylo zpracováno.

## Test
Pro čisté ověření použijte novou TEST kopii vytvořenou až po nasazení V206. Nejprve nechte HairSoft založit samotného zákazníka. Potom v cílovém HS Klient potvrďte ID ve Správě dat a následně posílejte Timeline jednotlivě ikonou u konkrétního řádku.

## Databáze
Bez změny struktury databáze.
