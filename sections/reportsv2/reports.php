<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_reports')) {
    error(403);
}

echo $Twig->render('reportsv2/outline.twig', [
    'viewer' => $Viewer,
]);
