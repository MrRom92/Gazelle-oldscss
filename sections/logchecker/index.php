<?php

declare(strict_types=1);

namespace Gazelle;

require_once match ($_REQUEST['action'] ?? 'test') {
    'update'      => 'update.php',
    'upload'      => 'upload.php',
    'take_upload' => 'upload_handle.php',
    'take_test'   => 'test_handle.php',
    default       => 'test.php',
};
