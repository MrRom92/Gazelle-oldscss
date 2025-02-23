<?php

declare(strict_types=1);

namespace Gazelle;

match ($_GET['type'] ?? '') {
    'posts' => include_once 'post_history.php',
    default => json_error('bad type'),
};
