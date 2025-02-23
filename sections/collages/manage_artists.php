<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_collages_create')) {
    error(403);
}
$collage = (new Manager\Collage())->findById((int)$_GET['collageid']);
if (is_null($collage)) {
    error(404);
}
if ($collage->isPersonal() && !$collage->isOwner($Viewer) && !$Viewer->permitted('site_collages_delete')) {
    error(403);
}
if (!$collage->isArtist()) {
    error(404);
}

echo $Twig->render('collage/manage-artists.twig', [
    'collage' => $collage,
    'viewer'  => $Viewer,
]);
