<?php

declare(strict_types=1);

namespace Gazelle;

if (isset($_REQUEST['action'])) {
    include_once 'reports_handle.php';
} else {
    include_once 'reports.php';
}
