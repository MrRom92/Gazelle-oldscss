<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_invite_source')) {
    error(403);
}

$user = (new Manager\User())->find(trim($_POST['user'] ?? ''));
if ($user) {
    header("Location: {$user->location()}#invite_source");
    exit;
}

echo $Twig->render('admin/invite-source.twig', [
    'list'   => (new Manager\InviteSource())->summaryByInviter(),
    'viewer' => $Viewer,
]);
