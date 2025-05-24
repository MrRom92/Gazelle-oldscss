<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

echo new Json\Stats\User(new Stats\Users(), $Viewer)
    ->setVersion(2)
    ->response();
