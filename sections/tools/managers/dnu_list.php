<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_dnu')) {
    error(403);
}

echo $Twig->render('admin/dnu.twig', [
    'list'   => (new Manager\DNU())->dnuList(),
    'viewer' => $Viewer,
]);
