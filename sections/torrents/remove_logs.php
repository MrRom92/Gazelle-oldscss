<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();
if (!$Viewer->permitted('torrents_delete')) {
    Error403::error();
}

$torrent = new Manager\Torrent()->findById((int)($_GET['torrentid'] ?? 0));
if (is_null($torrent)) {
    Error404::error();
}

$torrent->removeAllLogs(
    $Viewer,
    new File\RipLog(),
    new File\RipLogHTML(),
);
header('Location: ' . $torrent->location());
