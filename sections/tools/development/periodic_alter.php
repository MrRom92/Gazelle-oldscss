<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_periodic_task_manage')) {
    Error403::error();
}

authorize();

$scheduler = new TaskScheduler();
$taskId    = (int)($_POST['id'] ?? 0);
if ($_POST['submit'] == 'Delete') {
    if (!$taskId) {
        $err = 'Unknown or missing task id for delete';
    } else {
        $scheduler->deleteTask($taskId);
    }
} else {
    $validator = new Util\Validator();
    $validator->setFields([
        ['name', true, 'string', 'The name must be set, and has a max length of 64 characters', ['maxlength' => 64]],
        ['classname', true, 'string', 'The class name must be set, and has a max length of 32 characters', ['maxlength' => 32]],
        ['description', true, 'string', 'The description must be set, and has a max length of 255 characters', ['maxlength' => 255]],
        ['interval', true, 'number', 'The interval must be a number'],
    ]);
    $err = $validator->validate($_POST) ? false : $validator->errorMessage();
    if ($err === false) {
        if ($_POST['submit'] == 'Create') {
            if (!$scheduler::isClassValid($_POST['classname'])) {
                $err = "Cannot import class " . $_POST['classname'];
            } else {
                $scheduler->createTask($_POST['name'], $_POST['classname'], $_POST['description'], intval($_POST['interval']),
                    isset($_POST['enabled']), isset($_POST['sane']), isset($_POST['debug'])
                );
            }
        } elseif ($_POST['submit'] == 'Edit') {
            if (!$taskId) {
                $err = 'Unknown or missing task id for edit';
            }
            $task = $scheduler->getTask($taskId);
            if ($task == null) {
                $err = "Task $taskId not found";
            }
            $scheduler->updateTask($taskId, $_POST['name'], $_POST['classname'], $_POST['description'],
                (int)$_POST['interval'], isset($_POST['enabled']), isset($_POST['sane']), isset($_POST['debug'])
            );
        }
    }
}

header('Location: tools.php?action=periodic&mode=edit');
