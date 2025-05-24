<?php

declare(strict_types=1);

namespace Gazelle;

$user = new Manager\User()->findByUsername($_GET['search'] ?? $_GET['username'] ?? '');
if (is_null($user)) {
    Error404::error("There is no-one here with that name.");
}
header('Location: ' . $user->location());
