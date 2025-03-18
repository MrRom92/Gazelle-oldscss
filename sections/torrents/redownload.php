<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Enum\UserTorrentSearch;

if (!$Viewer->permitted('zip_downloader')) {
    Error403::error();
}
$user = (new Manager\User())->findById((int)($_GET['userid'] ?? 0));
if (is_null($user)) {
    Error404::error();
}
if ($user->id() != $Viewer->id() && !$Viewer->isStaff()) {
    Error403::error();
}

switch ($_GET['type']) {
    case 'seeding':
        if (!$user->propertyVisible($Viewer, 'seeding')) {
            Error403::error();
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::seeding);
        break;
    case 'snatches':
        if (!$user->propertyVisible($Viewer, 'snatched')) {
            Error403::error();
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::snatched);
        break;
    default:
        if (!$user->propertyVisible($Viewer, 'uploads')) {
            Error403::error();
        }
        $userTorrent = new Search\UserTorrent($user, UserTorrentSearch::uploaded);
        break;
}

$title = "{$user->username()}-{$userTorrent->label()}";
$collector = new Collector\TList($Viewer, new Manager\Torrent(), $title, 0);
$collector->setList($userTorrent->idList());
$collector->prepare([]);
$collector->emitZip(Util\Zip::make($title));
