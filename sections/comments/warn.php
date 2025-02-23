<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_warn')) {
    error(403);
}

$comment = (new Manager\Comment())->findById((int)($_POST['postid'] ?? 0));
if (is_null($comment)) {
    error(404);
}

echo $Twig->render('comment/warn.twig', [
    'comment' => $comment,
    'viewer'  => $Viewer,
]);
