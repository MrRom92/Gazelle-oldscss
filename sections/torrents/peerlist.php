<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$torrent = new Manager\Torrent()->findById((int)$_GET['torrentid']);
if (is_null($torrent)) {
    Error404::error();
}

$paginator = new Util\Paginator(PEERS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($torrent->seederTotal());

echo $Twig->render('torrent/seederlist.twig', [
    'is_admin'   => $Viewer->permitted('users_mod'),
    'list'       => $torrent->seederList($Viewer, $paginator->limit(), $paginator->offset()),
    'paginator'  => $paginator,
    'torrent_id' => $torrent->id(),
    'url_stem'   => new User\Stylesheet($Viewer)->imagePath(),
    'user_id'    => $Viewer->id(),
]);
