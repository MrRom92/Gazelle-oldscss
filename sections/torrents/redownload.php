<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Enum\UserTorrentSearch;

if (!$Viewer->permitted('zip_downloader')) {
    error(403);
}
$user = (new Manager\User())->findById((int)($_GET['userid'] ?? 0));
if (is_null($user)) {
    error(404);
}
if ($user->id() != $Viewer->id() && !$Viewer->isStaff()) {
    error(403);
}

switch ($_GET['type']) {
    case 'seeding':
        if (!$user->propertyVisible($Viewer, 'seeding')) {
            error(403);
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::seeding);
        break;
    case 'snatches':
        if (!$user->propertyVisible($Viewer, 'snatched')) {
            error(403);
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::snatched);
        break;
    default:
        if (!$user->propertyVisible($Viewer, 'uploads')) {
            error(403);
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::uploaded);
        break;
}

$title = "{$user->username()}-{$userTorrent->label()}";
$collector = new Collector\TList($Viewer, new Manager\Torrent(), $title, 0);
$collector->setList($userTorrent->idList());
$collector->prepare([]);
$collector->emitZip(Util\Zip::make($title));
