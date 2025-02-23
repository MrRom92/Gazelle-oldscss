<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_periodic_task_manage')) {
    error(403);
}

echo $Twig->render('admin/scheduler/edit.twig', [
    'err'       => $err ?? null,
    'task_list' => (new TaskScheduler())->getTasks(),
    'viewer'    => $Viewer,
]);
