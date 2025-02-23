<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$collage = (new Manager\Collage())->findById((int)($_GET['collageid'] ?? 0));
if (is_null($collage)) {
    error(404);
}
$collage->toggleSubscription($Viewer);
