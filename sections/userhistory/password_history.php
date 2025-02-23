<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_view_keys')) {
    error(403);
}

$user = (new Manager\User())->findById((int)$_GET['userid']);
if (is_null($user)) {
    error(404);
}

echo $Twig->render('user/password-history.twig', [
    'list' => $user->passwordHistory(),
    'user' => $user,
]);
