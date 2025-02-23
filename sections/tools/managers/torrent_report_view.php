<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_mod')) {
    error(403);
}

echo $Twig->render('admin/torrent-report-view.twig', [
    'list'   => (new Manager\Torrent\ReportType())->list(),
    'viewer' => $Viewer,
]);
