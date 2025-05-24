<?php

declare(strict_types=1);

namespace Gazelle;

echo new Json\Stats\Torrent(new Stats\Torrent())
    ->setVersion(2)
    ->response();
