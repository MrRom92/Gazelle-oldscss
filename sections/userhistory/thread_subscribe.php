<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if ($Viewer->disableForums()) {
    error(403);
}
authorize();

$thread = (new Manager\ForumThread())->findById((int)($_GET['threadid'] ?? 0));
if (is_null($thread)) {
    error(404);
}
if (!$Viewer->readAccess($thread->forum())) {
    error(403);
}

json_print('success', (new User\Subscription($Viewer))->subscribe($thread));
