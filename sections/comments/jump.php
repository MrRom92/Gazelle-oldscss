<?php

declare(strict_types=1);

namespace Gazelle;

$comment = (new Manager\Comment())->findById((int)($_REQUEST['postid'] ?? 0));
if (is_null($comment)) {
    Error404::error();
}
header('Location: ' . $comment->location());
