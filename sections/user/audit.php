<?php
/** @phpstan-var \Twig\Environment $Twig */
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permittedAny('admin_audit_edit', 'admin_audit_view')) {
    error(403);
}
$userMan = new Manager\User();
$user = $userMan->findById((int)($_GET['id'] ?? 0));
if (is_null($user)) {
    error(404);
}
$user->auditTrail()->migrate($userMan);

echo $Twig->render('user/audit.twig', [
    'edit'   => isset($_GET['edit']),
    'user'   => $user,
    'viewer' => $Viewer,
]);
