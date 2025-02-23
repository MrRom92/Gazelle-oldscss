<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if ($Viewer->disableForums()) {
    print json_die('failure');
}

match ($_GET['type'] ?? 'main') {
    'main'       => include_once 'main.php',
    'viewforum'  => include_once 'forum.php',
    'viewthread' => include_once 'thread.php',
    default      => json_error('type'),
};
