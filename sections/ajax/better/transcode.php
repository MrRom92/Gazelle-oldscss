<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!in_array((int)($_GET['type'] ?? 0), range(0, 3))) {
    json_error('Unknown transcode type');
}

$search = new Search\Transcode($Viewer, (new Manager\Torrent())->setViewer($Viewer));
if (isset($_GET['search'])) {
    $search->setSearch($_GET['search']);
}

echo (new Json\Better\Transcode($Viewer->announceKey(), $search))
    ->setVersion(2)
    ->response();
