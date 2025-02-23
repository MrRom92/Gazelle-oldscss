<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_forum_post_delete')) {
    error(403);
}
authorize();

$post = (new Manager\ForumPost())->findById((int)($_GET['postid'] ?? 0));
if (is_null($post)) {
    error(404);
}

if (!$post->remove()) {
    error(404);
}
