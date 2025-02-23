<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$user = (new Manager\User())->findById((int)$_GET['userid']);
if (is_null($user)) {
    error(403);
}
if (!$Viewer->permitted('users_mod') && $user->id() != $Viewer->id()) {
    error(403);
}
authorize();

(new Manager\Notification())->push([$user->id()],
    'Push!', 'You have been pushed by ' . $Viewer->username());

header('Location: ' . $user->location() . '&action=edit');
