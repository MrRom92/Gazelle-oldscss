<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_collages_create')) {
    Error403::error();
}

authorize();

$artist = (new Manager\Artist())->findById((int)($_POST['artistid'] ?? 0));
if (is_null($artist)) {
    Error404::error();
}
$collage = (new Manager\Collage())->findById((int)$_POST['collageid']);
if (is_null($collage)) {
    Error404::error();
}
if (!$collage->isArtist()) {
    Error403::error();
}

if (isset($_POST['drag_drop_collage_sort_order'])) {
    $collage->updateSequence($_POST['drag_drop_collage_sort_order']);
} elseif ($_POST['submit'] === 'Remove') {
    $collage->removeEntry($artist);
} else {
    $sequence = (int)$_POST['sort'];
    if (!$sequence) {
        Error404::error();
    }
    $collage->updateSequenceEntry($artist, $sequence);
}
$collage->flush();

header("Location: collages.php?action=manage_artists&collageid={$collage->id()}");
