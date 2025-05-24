<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$poll = new Manager\ForumPoll()->findById((int)($_POST['threadid'] ?? 0));
if (is_null($poll)) {
    Error404::error();
}
if (!$Viewer->permitted('site_moderate_forums') && !$poll->hasRevealVotes()) {
    Error403::error();
}

$vote = (int)$_GET['vote'];
if (!$vote) {
    Error404::error();
}
$poll->modifyVote($Viewer, $vote);

header("Location: " . $poll->location());
