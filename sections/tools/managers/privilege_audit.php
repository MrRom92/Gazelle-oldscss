<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_permissions')) {
    Error403::error();
}

echo $Twig->render('admin/privilege-audit.twig', [
    'privilege_manager' => new Manager\Privilege(),
    'user_manager'      => new Manager\User(),
    'config'            => USERCLASS_AUDIT,
]);
