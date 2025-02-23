<?php

declare(strict_types=1);

namespace Gazelle;

require_once match ($_REQUEST['action'] ?? '') {
    'add'        => 'add.php',
    'Save notes' => 'comment.php',
    'Unfriend'   => 'remove.php',
    default      => 'friends.php',
};
