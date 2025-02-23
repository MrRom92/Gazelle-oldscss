<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$inviteKey = trim($_GET['invite'] ?? '');
$user = (new Manager\Invite())->findUserByKey($inviteKey, new Manager\User());
if (is_null($user)) {
    error(404);
}
if ($user->id() != $Viewer->id()) {
    error(403);
}

$user->invite()->revoke($inviteKey);
header('Location: user.php?action=invite');
