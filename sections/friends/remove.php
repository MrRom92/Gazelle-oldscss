<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$friend = (new Manager\User())->findById((int)($_POST['friendid'] ?? 0));
if (!$friend) {
    error("no such user found");
}

(new User\Friend($Viewer))->remove($friend);

header('Location: friends.php');
