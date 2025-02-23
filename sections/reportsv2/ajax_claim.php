<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_reports')) {
    error(403);
}

echo (new Manager\Torrent\Report(new Manager\Torrent()))
    ->findById((int)($_GET['id'] ?? 0))
    ?->claim($Viewer) ?? 0;
