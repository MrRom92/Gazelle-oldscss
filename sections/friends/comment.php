<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$friend = (new Manager\User())->findById((int)($_POST['friendid'] ?? 0));
if (!$friend) {
    Error404::error("no such user found");
}

$viewerFriend = new User\Friend($Viewer);
if (!$viewerFriend->isFriend($friend)) {
    Error400::error("you are not friends with {$friend->username()}");
}

$viewerFriend->addComment($friend, trim($_POST['comment']));

header('Location: friends.php');
