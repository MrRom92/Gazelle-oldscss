<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$userMan = new Manager\User();
if (isset($_GET['userid']) && $Viewer->permitted('users_override_paranoia')) {
    $user = $userMan->findById((int)$_GET['userid']);
    if (is_null($user)) {
        Error404::error();
    }
} else {
    $user = $Viewer;
}

if (($_GET['method'] ?? '') === 'single') {
    $filter = 'all';
    $type   = 'single';
} else {
    $filter = $_GET['filter'] ?? 'all';
    $type   = $_GET['type'] ?? 'artwork';
}

$better = match ($type) {
    'artistcollage' => new Better\ArtistCollage($user, $filter, new Manager\Artist()),
    'artistdesc'    => new Better\ArtistDescription($user, $filter, new Manager\Artist()),
    'artistdiscogs' => new Better\ArtistDiscogs($user, $filter, new Manager\Artist()),
    'artistimg'     => new Better\ArtistImage($user, $filter, new Manager\Artist()),
    'artwork'       => new Better\Artwork($user, $filter, (new Manager\TGroup())->setViewer($Viewer)),
    'checksum'      => new Better\Checksum($user, $filter, (new Manager\Torrent())->setViewer($Viewer)),
    'single'        => new Better\SingleSeeded($user, $filter, (new Manager\Torrent())->setViewer($Viewer)),
    'files', 'folders', 'lineage', 'tags', 'trumpable'
                    => (new Better\Bad($user, $filter, new Manager\Torrent()))->setBadType($type),
    default         => Error404::error(),
};

if (isset($_GET['remove']) && $better instanceof Better\Bad && $Viewer->permitted('admin_reports')) {
    $torrent = (new Manager\Torrent())->findById((int)$_GET['remove']);
    if ($torrent) {
        $torrent->removeFlag($better->torrentFlag());
    }
}

$uploader = null;
if ($type == 'single') {
    $uploader = $userMan->find($_GET['uploader'] ?? '');
    if ($uploader) {
        $better->setUploader($uploader);
    }
}

$search = $_GET['search'] ?? '';
if ($search) {
    $better->setSearch($search);
}

$paginator = new Util\Paginator(TORRENTS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($better->total());

echo $Twig->render('better/better.twig', [
    'better'    => $better,
    'filter'    => $filter,
    'search'    => $search,
    'type'      => $type,
    'paginator' => $paginator,
    'uploader'  => $uploader,
    'viewer'    => $Viewer,
]);
