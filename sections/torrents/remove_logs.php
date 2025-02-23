<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();
if (!$Viewer->permitted('torrents_delete')) {
    error(403);
}

$torrent = (new Manager\Torrent())->findById((int)($_GET['torrentid'] ?? 0));
if (is_null($torrent)) {
    error(404);
}

$torrent->removeAllLogs(
    $Viewer,
    new File\RipLog(),
    new File\RipLogHTML(),
);
header('Location: ' . $torrent->location());
