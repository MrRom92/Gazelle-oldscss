<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();
$Viewer->logout();
header("Location: /index.php");
