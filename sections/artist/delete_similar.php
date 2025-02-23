<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

if (!$Viewer->permitted('site_delete_tag')) {
    error(403);
}

$artistMan = new Manager\Artist();
$artist    = $artistMan->findById((int)($_GET['artistid'] ?? 0));
$similar   = $artistMan->findById((int)($_GET['similarid'] ?? 0));
if (is_null($artist) || is_null($similar)) {
    error(404);
}

$artist->similar()->removeSimilar($similar, $Viewer);

header("Location: " . redirectUrl($artist->location()));
