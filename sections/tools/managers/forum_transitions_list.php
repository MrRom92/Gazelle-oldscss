<?php

declare(strict_types=1);

namespace Gazelle;

/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

if (!$Viewer->permitted('admin_manage_forums')) {
    error(403);
}

if (!isset($_REQUEST['userid'])) {
    $user = $Viewer;
} else {
    $user = (new Manager\User())->find((int)$_REQUEST['userid']);
    if (is_null($user)) {
        error(404);
    }
}

echo $Twig->render('admin/forum-transition.twig', [
    'class_list' => (new Manager\User())->classList(),
    'forum_list' => (new Manager\Forum())->forumList(),
    'user_list'  => (new Manager\ForumTransition())->userTransitionList($user),
    'user'       => $user,
    'viewer'     => $Viewer,
]);
