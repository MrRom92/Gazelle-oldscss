<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_view_torrent_snatchlist')) {
    error(403);
}
$torrent = (new Manager\Torrent())->findById((int)$_GET['torrentid']);
if (is_null($torrent)) {
    error(404);
}

$paginator = new Util\Paginator(PEERS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($torrent->downloadTotal());

echo $Twig->render('torrent/downloadlist.twig', [
    'list'       => $torrent->downloadList($Viewer, $paginator->limit(), $paginator->offset()),
    'paginator'  => $paginator,
    'torrent_id' => $torrent->id(),
    'url_stem'   => (new User\Stylesheet($Viewer))->imagePath(),
    'viewer_id'  => $Viewer->id(),
]);
