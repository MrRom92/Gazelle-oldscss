<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_view_flow')) {
    error(403);
}

echo $Twig->render('admin/economy.twig', [
    'info' => new Stats\Economic(),
]);
