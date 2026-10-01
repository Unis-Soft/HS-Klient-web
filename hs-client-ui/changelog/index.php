<?php

$entries = require dirname(__DIR__) . '/data/changelog.php';
$cssFile = __DIR__ . '/changelog.css';
$cssVersion = is_file($cssFile) ? (string) filemtime($cssFile) : '1';
$latestVersion = isset($entries[0]['version']) ? $entries[0]['version'] : '';
$oldestEntry = !empty($entries) ? $entries[count($entries) - 1] : [];
$oldestVersion = isset($oldestEntry['version']) ? $oldestEntry['version'] : '';

function hsChangelogEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function hsChangelogBadgeClass($type)
{
    $classes = [
        'Design' => 'badge--design',
        'Oprava' => 'badge--fix',
        'Test' => 'badge--test',
        'Staženo' => 'badge--removed',
        'Architektura' => 'badge--architecture',
    ];

    return isset($classes[$type]) ? $classes[$type] : '';
}
?><!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>HairSoft Klient – changelog</title>
    <link rel="icon" href="/hs-client-ui/img/favicon.ico?v=41">
    <link rel="stylesheet" href="changelog.css?v=<?php echo hsChangelogEscape($cssVersion); ?>">
</head>
<body>
    <main class="changelog-shell">
        <header class="changelog-header">
            <div>
                <p class="changelog-kicker">Online HairSoft</p>
                <h1>Changelog klient.hairsoft.cz</h1>
                <p class="changelog-subtitle">Přehled designových změn bez zásahů do databáze a obchodní logiky.</p>
            </div>
            <div class="version-summary" aria-label="Rozsah verzí">
                <strong><?php echo hsChangelogEscape($oldestVersion . '–' . $latestVersion); ?></strong>
                <span><?php echo count($entries); ?> zaznamenaných verzí</span>
            </div>
        </header>

        <section class="table-card" aria-label="Historie verzí">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Verze</th>
                            <th>Datum</th>
                            <th>Typ</th>
                            <th>Co se změnilo</th>
                            <th>Kde</th>
                            <th>Databáze</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <tr>
                            <td class="version"><?php echo hsChangelogEscape($entry['version']); ?></td>
                            <td class="date"><?php echo hsChangelogEscape($entry['date']); ?></td>
                            <td><span class="badge <?php echo hsChangelogEscape(hsChangelogBadgeClass($entry['type'])); ?>"><?php echo hsChangelogEscape($entry['type']); ?></span></td>
                            <td><?php echo hsChangelogEscape($entry['change']); ?></td>
                            <td class="area"><?php echo hsChangelogEscape($entry['where']); ?></td>
                            <td class="database"><?php echo hsChangelogEscape($entry['database']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <p class="changelog-note">Stránka není součástí navigace aplikace a je dostupná pouze přes přímou adresu.</p>
    </main>
</body>
</html>
