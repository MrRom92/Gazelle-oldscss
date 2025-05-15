<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_periodic_task_view')) {
    Error403::error();
}

$scheduler = new TaskScheduler();
$taskId    = (int)($_REQUEST['id'] ?? 0);

if ($taskId && $_REQUEST['mode'] === 'run_now') {
    if (!$Viewer->permitted('admin_schedule')) {
        Error403::error();
    }
    authorize();
    $scheduler->runNow($taskId);
}

echo $Twig->render('admin/scheduler/view.twig', [
    'heading'   => $scheduler->heading(),
    'task_list' => $scheduler->taskDetailList(),
    'viewer'    => $Viewer,
]);
