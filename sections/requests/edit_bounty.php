<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_admin_requests')) {
    error(403);
}

$request = (new Manager\Request())->findById((int)$_GET['id']);
if (is_null($request)) {
    error(404);
}

echo $Twig->render('request/edit-bounty.twig', [
    'request' => $request,
    'viewer'  => $Viewer,
]);
