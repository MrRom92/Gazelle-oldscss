<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$torrent = new Manager\Torrent()->findById((int)($_GET['id'] ?? 0));
if (is_null($torrent)) {
    json_error('bad parameters');
}
if ($torrent->uploaderId() != $Viewer->id() && !$Viewer->permitted('admin_add_log')) {
    json_error('Not your upload.');
}
if (empty($_FILES) || empty($_FILES['logfiles'])) {
    json_error('no log files uploaded');
}

echo new Json\AddLog(
    $torrent,
    $Viewer,
    new Manager\TorrentLog(new File\RipLog(), new File\RipLogHTML()),
    new LogfileSummary($_FILES['logfiles']),
)
    ->setVersion(1)
    ->response();
