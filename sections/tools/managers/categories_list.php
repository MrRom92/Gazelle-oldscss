<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_forums')) {
    error(403);
}

echo $Twig->render('admin/forum-category.twig', [
    'list'   => (new Manager\ForumCategory())->usageList(),
    'viewer' => $Viewer,
]);
