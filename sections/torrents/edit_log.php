<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_mod')) {
    error(403);
}

$torrent = (new Manager\Torrent())->findById((int)($_GET['torrentid'] ?? 0));
if (is_null($torrent)) {
    error(404);
}
$tlog = (new Manager\TorrentLog(new File\RipLog(), new File\RipLogHTML()))->findById($torrent, (int)($_GET['logid'] ?? 0));
if (is_null($tlog)) {
    error(404);
}

echo $Twig->render('torrent/edit-log.twig', [
    'adjuster' => (new Manager\User())->findById($tlog->adjustedByUserId())?->link() ?? 'System',
    'tlog'     => $tlog,
    'torrent'  => $torrent,

]);
