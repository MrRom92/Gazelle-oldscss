<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$type = $_GET['type'] ??  '';
$id   = (int)($_GET['id'] ?? 0);
if (!(new User\Bookmark($Viewer))->create($type, $id)) {
    json_error('bad parameters');
}

if ($type === 'request') {
    (new Manager\Request())->findById($id)?->updateBookmarkStats();
}
print(json_encode('OK'));
