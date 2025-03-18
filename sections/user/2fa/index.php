<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$user = (new Manager\User())->findById((int)($_REQUEST['userid'] ?? 0));
if (is_null($user)) {
    Error404::error();
}
if ($user->id() != $Viewer->id() && !$Viewer->permitted('users_mod')) {
    Error403::error();
}

switch ($_GET['do'] ?? '') {
    case 'configure':
        if ($user->MFA()->enabled()) {
            Error400::error('MFA is already configured');
        }
        include_once 'configure.php';
        break;

    case 'complete':
        include_once 'complete.php';
        break;

    case 'remove':
        include_once 'remove.php';
        break;

    default:
        Error404::error();
}
