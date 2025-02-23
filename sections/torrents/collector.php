<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('zip_downloader')) {
    error(403);
}

if (empty($_GET['title'])) {
    error('Collector type not specified');
}
$title = trim($_GET['title']);

switch ($title) {
    case 'better':
        if ($Viewer->hashHmac('collector', $_GET['ids']) !== ($_GET['sig'] ?? '')) {
            error('Better signature mismatch');
        }
        $ids = array_filter(explode(',', $_GET['ids'] ?? '0'), fn($id) => (int)$id > 0);
        break;
    case 'seedbox':
        authorize();
        $user = (new Manager\User())->findById((int)($_GET['userid'] ?? 0));
        if (is_null($user)) {
            error(404);
        }
        $ids = (new User\Seedbox($user))
            ->setSource($_GET['s'] ?? '')
            ->setTarget($_GET['t'] ?? '')
            ->setUnion($_GET['m'] === 'union')
            ->idList();
        $title = "$title-" . $user->username();
        break;
    default:
        error('Unknown collector type');
}

if (!$ids) {
    error('No groups found to collect');
}

$collector = new Collector\TList($Viewer, new Manager\Torrent(), $title, 0);
$collector->setList($ids);
if (!$collector->prepare([])) {
    error("Nothing to gather, choose some encodings and media!");
}

$collector->emitZip(Util\Zip::make($title));
