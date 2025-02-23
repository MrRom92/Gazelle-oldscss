<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

// Remove 2FA. Users have to enter their password, moderators skip this step.
$user = (new Manager\User())->findById((int)($_GET['userid'] ?? 0));
if (is_null($user)) {
    error(404);
}
if (!$user->MFA()->enabled()) {
    error($Viewer->permitted('users_edit_password') ? 'No 2FA configured' : 404);
}

$userId = $user->id();
if (!$Viewer->permitted('users_edit_password')) {
    if ($userId !== $Viewer->id()) {
        error(403);
    } elseif (empty($_POST['password'])) {
        include_once 'confirm.php';
        exit;
    } elseif (!$user->validatePassword($_POST['password'])) {
        header('Location: user.php?action=2fa&do=confirm=invalid&userid=' . $userId);
        exit;
    }
}
$user->MFA()->remove($Viewer);

header("Location: {$user->location()}");
