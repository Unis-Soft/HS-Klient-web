Bonfero HairSoft Bridge Directory V001
======================================

Cíl: https://bridge.bonfero.com

1. Nahrajte obsah tohoto balíčku do document root subdomény bridge.bonfero.com.
2. Vytvořte soubor .env podle .env.example.
3. DIRECTORY_APP_KEY musí být dlouhý náhodný secret (min. 32 znaků) a nesmí být stejný jako site key.
4. DIRECTORY_SITE_KEYS je JSON mapa důvěryhodných backendů.

Pro první test s BSC Web FIX20:
- BSC používá site ID `bsc`.
- Pokud na BSC webu nepřidáte HS_BRIDGE_DIRECTORY_SITE_KEY, použije se jeho stávající APP_KEY.
- Do DIRECTORY_SITE_KEYS tedy nastavte hodnotu `bsc` na STEJNOU hodnotu jako APP_KEY BSC webu.

Příklad:
DIRECTORY_SITE_KEYS={"bsc":"SEM_ZKOPIRUJTE_APP_KEY_Z_BSC"}

5. Složka storage musí být zapisovatelná PHP procesem a nesmí být veřejně přístupná.
6. Ověření: GET /api/pairing.php?route=health musí vrátit JSON s `ok:true` a `protocol:2`.

Endpointy:
- Bridge: POST /api/bridge.php?route=register
- Bridge: GET  /api/bridge.php?route=status
- Web:    POST /api/pairing.php?route=claim
- Web:    POST /api/pairing.php?route=bind
- Web:    POST /api/pairing.php?route=release

Nevystavujte .env ani storage veřejně.
