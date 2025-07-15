<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_mod')) {
    Error403::error();
}

$torrent = new Manager\Torrent()->findById((int)($_GET['torrentid'] ?? 0));
if (is_null($torrent)) {
    Error404::error();
}
$tlog = new Manager\TorrentLog()->findById($torrent, (int)($_GET['logid'] ?? 0));
if (is_null($tlog)) {
    Error404::error();
}

echo $Twig->render('torrent/edit-log.twig', [
    'adjuster' => new Manager\User()
        ->findById($tlog->adjustedByUserId())?->link() ?? 'System',
    'tlog'     => $tlog,
    'torrent'  => $torrent,

]);
