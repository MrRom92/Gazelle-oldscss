<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_torrents_notify')) {
    error(403);
}
authorize();

$artist = (new Manager\Artist())->findById((int)$_GET['artistid']);
if (is_null($artist)) {
    error(404);
}
$Viewer->removeArtistNotification($artist);

header("Location: " . redirectUrl("artist.php?id=" . $artist->id()));
