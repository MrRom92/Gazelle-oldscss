<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use OrpheusNET\Logchecker\Logchecker;

if (!$Viewer->permitted('users_mod')) {
    Error403::error();
}

$torrent = new Manager\Torrent()->findById((int)$_GET['torrentid']);
$logId = (int)$_GET['logid'];
if (is_null($torrent) || !$logId) {
    Error404::error();
}

$logpath = new File\RipLog($torrent->id, $logId)->path();
$logfile = new Logfile($logpath, basename($logpath));
new File\RipLogHTML($torrent->id, $logId)->put($logfile->text());

$torrent->rescoreLog($logId, $logfile, Logchecker::getLogcheckerVersion());

header('Location: ' . $torrent->location());
