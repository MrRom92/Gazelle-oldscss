<?php

declare(strict_types=1);

namespace Gazelle;

require_once match ($_GET['method'] ?? '') {
    'transcode' => 'transcode.php',
    default     => 'better.php',
};
