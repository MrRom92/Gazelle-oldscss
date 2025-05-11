<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

// Remove MFA. Users have to enter their password, moderators skip this step.
$user = (new Manager\User())->findById((int)($_GET['userid'] ?? 0));
if (is_null($user)) {
    Error404::error();
}
if (!$user->MFA()->enabled()) {
    Error400::error('No MFA configured');
}

if (!$Viewer->permitted('users_edit_password')) {
    if ($user->id !== $Viewer->id) {
        Error403::error();
    } elseif (empty($_POST['password'])) {
        include_once 'confirm.php';
        exit;
    } elseif (!$user->validatePassword($_POST['password'])) {
        header("Location: user.php?action=mfa&do=remove&invalid=1&userid={$user->id}");
        exit;
    }
}
$user->MFA()->remove();

header("Location: {$user->location()}");
