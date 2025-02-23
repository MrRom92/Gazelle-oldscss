<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_moderate_requests')) {
    error(403);
}
$torrent = (new Manager\Torrent())->findById((int)$_GET['torrentid']);
if (is_null($torrent)) {
    error(404);
}

echo $Twig->render('torrent/masspm.twig', [
    'textarea' => new Util\Textarea('message', "[pl]{$torrent->id()}[/pl]", 60, 8),
    'torrent'  => $torrent,
    'viewer'   => $Viewer,
]);
