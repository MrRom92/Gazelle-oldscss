<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

require_once 'do_warn.php';
[$post, $body] = handleWarningRequest(new Manager\ForumPost());
$post->edit($Viewer, $body);
if ($post->isPinned()) {
    $post->thread()->flush();
}
