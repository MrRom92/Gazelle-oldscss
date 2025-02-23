<?php

declare(strict_types=1);

namespace Gazelle;

match ($_GET['method'] ?? '') {
    'transcode' => include_once 'transcode.php',
    'single'    => include_once 'single.php',
    default     => json_error('bad method'),
};
