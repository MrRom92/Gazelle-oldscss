<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$torMan = new Manager\Torrent();
$torrent = $torMan->findById((int)$_GET['torrentid']);
if (is_null($torrent)) {
    $torrent = $torMan->findDeletedById((int)$_GET['torrentid']);
}
if (is_null($torrent)) {
    Error404::error();
}

echo $Twig->render('torrent/riplog.twig', [
    'id'        => $torrent->id(),
    'list'      => $torrent->logfileList(new File\RipLog(), new File\RipLogHTML()),
    'log_score' => $torrent->logScore(),
    'viewer'    => $Viewer,
]);
