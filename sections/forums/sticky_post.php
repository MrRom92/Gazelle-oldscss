<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_moderate_forums')) {
    error(403);
}
authorize();

$post = (new Manager\ForumPost())->findById((int)($_GET['postid'] ?? 0));
if (is_null($post)) {
    error(404);
}
$post->pin($Viewer, empty($_GET['remove']));

header('Location: ' . $post->location());
