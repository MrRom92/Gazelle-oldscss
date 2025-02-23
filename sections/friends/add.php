<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$friend = (new Manager\User())->findById((int)($_GET['friendid'] ?? 0));
if (!$friend) {
    error("no such user found");
}

if ($friend->id() === $Viewer->id()) {
    error("you cannot add yourself as a friend");
}

if (!(new User\Friend($Viewer))->add($friend)) {
    error("you are already friends with {$friend->username()}");
}

header('Location: friends.php');
