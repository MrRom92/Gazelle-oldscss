<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

if ((new User\Bookmark($Viewer))->remove($_GET['type'], (int)$_GET['id'])) {
    print(json_encode('OK'));
} else {
    json_error('bad parameters');
}
