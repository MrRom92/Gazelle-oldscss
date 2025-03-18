<?php

declare(strict_types=1);

namespace Gazelle;

$collageMan = new Manager\Collage();
$Collage = $collageMan->findById((int)($_GET['id'] ?? 0));
if (is_null($Collage)) {
    Error404::error();
}

if ($Collage->isDeleted()) {
    header("Location: log.php?search=Collage+" . $Collage->id());
    exit;
}

require_once $Collage->isArtist() ? 'collage_artists.php' : 'collage_torrent.php';

View::show_footer();
