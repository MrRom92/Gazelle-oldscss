<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_navigation')) {
    error(403);
}

echo $Twig->render('admin/user-navigation.twig', [
    'list'   => (new Manager\UserNavigation())->fullList(),
    'viewer' => $Viewer,
]);
