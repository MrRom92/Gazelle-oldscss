<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$torrent = new Manager\Torrent()->findById((int)($_GET['id'] ?? 0));
if (is_null($torrent)) {
    json_error('bad parameters');
}
if ($torrent->uploaderId() != $Viewer->id && !$Viewer->permitted('admin_add_log')) {
    json_error('Not your upload.');
}
if (empty($_FILES) || empty($_FILES['logfiles'])) {
    json_error('no log files uploaded');
}

$manager = new Manager\TorrentLog();

echo new Json\AddLog(
    $torrent,
    $Viewer,
    $manager,
    new LogfileSummary($_FILES['logfiles'], $torrent->logfileHashList()),
)
    ->setVersion(1)
    ->response();
