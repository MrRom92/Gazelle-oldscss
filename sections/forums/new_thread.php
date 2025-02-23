<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$forum = (new Manager\Forum())->findById((int)($_GET['forumid'] ?? 0));
if (!$forum) {
    error(404);
}
if (!$Viewer->writeAccess($forum) || !$Viewer->createAccess($forum)) {
    error(403);
}

echo $Twig->render('forum/new-thread.twig', [
    'id'        => $forum->id(),
    'name'      => $forum->name(),
    'textarea'  => new Util\Textarea('body', '', 90, 8),
    'viewer'    => $Viewer,
]);
