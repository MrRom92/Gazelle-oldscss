<?php

declare(strict_types=1);

namespace Gazelle;

require_once match ($_GET['type'] ?? 'inbox') {
    'viewconv' => 'viewconv.php',
    default    => 'inbox.php',
};
