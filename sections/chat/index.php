<?php
/** @phpstan-var ?\Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer || $Viewer->isLocked()) {
    include_once 'webirc_disabled.php';
    exit;
}

require_once match ($_REQUEST['action'] ?? '') {
    'webirc' => 'webirc.php',
    default  => 'join.php',
};
