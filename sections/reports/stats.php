<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permittedAny('admin_reports', 'site_moderate_forums')) {
    error(403);
}

echo $Twig->render('report/stats.twig', [
    'stats'  => new Stats\Report(),
    'viewer' => $Viewer,
]);
