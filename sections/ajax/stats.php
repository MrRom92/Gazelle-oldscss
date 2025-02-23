<?php

declare(strict_types=1);

namespace Gazelle;

echo (new Json\Stats\General(
    new Stats\Request(),
    new Stats\Torrent(),
    new Stats\Users(),
))
    ->setVersion(2)
    ->response();
