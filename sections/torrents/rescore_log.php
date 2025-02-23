<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use OrpheusNET\Logchecker\Logchecker;

if (!$Viewer->permitted('users_mod')) {
    error(403);
}

$torrent = (new Manager\Torrent())->findById((int)$_GET['torrentid']);
$logId = (int)$_GET['logid'];
if (is_null($torrent) || !$logId) {
    error(404);
}

$logpath = (new File\RipLog())->path([$torrent->id(), $logId]);
$logfile = new Logfile($logpath, basename($logpath));
(new File\RipLogHTML())->put($logfile->text(), [$torrent->id(), $logId]);

$torrent->rescoreLog($logId, $logfile, Logchecker::getLogcheckerVersion());

header('Location: ' . $torrent->location());
