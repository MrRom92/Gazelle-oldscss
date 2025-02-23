<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_mod')) {
    error(403);
}

$torrent = (new Manager\Torrent())->findById((int)($_REQUEST['torrentid'] ?? 0));
if (is_null($torrent)) {
    error(404);
}
$torrent->regenerateFilelist(
    new File\Torrent(),
    new \OrpheusNET\BencodeTorrent\BencodeTorrent()
);

header("Location: " . $torrent->location());
