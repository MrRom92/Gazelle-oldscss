<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_whitelist')) {
    error(403);
}

echo $Twig->render('admin/client-whitelist.twig', [
    'list'   => (new Manager\ClientWhitelist())->list(),
    'viewer' => $Viewer,
]);
