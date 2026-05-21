<?php

declare(strict_types=1);

require __DIR__ . '/includes/config.inc.php';

$oldal = trim((string) ($_SERVER['QUERY_STRING'] ?? ''), '/');
if ($oldal === '') {
    $oldal = '/';
}

$keres = app_resolve_route($oldal, $oldalak, $hiba_oldal);

if (($keres['fajl'] ?? '404') === '404') {
    header('HTTP/1.0 404 Not Found');
}

include __DIR__ . '/templates/index.tpl.php';
