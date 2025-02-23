<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$post = (new Manager\ForumPost())->findById((int)($_GET['post'] ?? 0));
if (is_null($post)) {
    error(404);
}
if (!$Viewer->readAccess($post->thread()->forum())) {
    error(403);
}
header('Content-type: text/plain');
echo $post->body();
