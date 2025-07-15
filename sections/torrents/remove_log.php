<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();
if (!$Viewer->permitted('users_mod')) {
    Error403::error();
}

$torrent = new Manager\Torrent()->findById((int)$_GET['torrentid']);
$logId = (int)$_GET['logid'];
if (is_null($torrent) || !$logId) {
    Error404::error();
}

new File\RipLog($torrent->id, $logId)->remove();
$torrent->logger()->torrent(
    $torrent,
    $Viewer,
    "Riplog ID $logId removed from torrent {$torrent->id}",
);
$torrent->clearLog($logId);

header('Location: ' . $torrent->location());
