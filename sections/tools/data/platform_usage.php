<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_debug')) {
    error(403);
}

$stats = new Stats\Users();

echo $Twig->render('admin/platform-usage.twig', [
    'os_list'      => $stats->operatingSystemList(),
    'browser_list' => $stats->browserList(),
]);
