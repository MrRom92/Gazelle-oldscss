<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$user = (new Manager\User())->findById((int)($_REQUEST['userid'] ?? 0));
if (is_null($user)) {
    Error404::error();
}
if ($user->MFA()->enabled()) {
    Error400::error('MFA is already configured');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start(['read_and_close' => true]);
}
if (empty($_SESSION['private_key'])) {
    Error404::error();
}

$recoveryKeys = $user->MFA()->create(new Manager\UserToken(), $_SESSION['private_key'], $Viewer);
if (!$recoveryKeys) {
    Error400::error('failed to create MFA');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['private_key']);
session_write_close();

echo $Twig->render('user/2fa/complete.twig', [
    'keys' => $recoveryKeys,
]);
