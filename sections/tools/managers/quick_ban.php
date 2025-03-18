<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_ipbans')) {
    Error403::error();
}

if (isset($_GET['perform'])) {
    $IPv4Man = new Manager\IPv4();
    if ($_GET['perform'] == 'delete') {
        $IPv4Man->removeBan((int)$_GET['id']);
    } elseif ($_GET['perform'] == 'create') {
        $IPv4Man->createBan($Viewer, $_GET['ip'], $_GET['ip'], trim($_GET['notes']));
    } else {
        Error403::error();
    }
}
