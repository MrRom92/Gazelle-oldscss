<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$postId = (int)($_GET['post'] ?? 0);
$pm = (new Manager\StaffPM())->findByPostId($postId);
if (is_null($pm)) {
    error(404);
}
if (!$pm->visible($Viewer)) {
    error(403);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'username' => (new Manager\User())->findById((int)$pm->postUserId($postId))?->username(),
    'body'     => $pm->postBody($postId),
]);
