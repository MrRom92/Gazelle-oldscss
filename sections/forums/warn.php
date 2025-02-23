<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_warn')) {
    error(403);
}
authorize();

$post = (new Manager\ForumPost())->findById((int)($_POST['postid'] ?? 0));
if (is_null($post)) {
    error(404);
}
$user = (new Manager\User())->findById((int)($_POST['userid'] ?? 0));
if (is_null($user)) {
    error(404);
}

echo $Twig->render('forum/warn.twig', [
    'post'   => $post,
    'user'   => $user,
    'viewer' => $Viewer,
]);
