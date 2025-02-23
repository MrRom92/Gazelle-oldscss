<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$postId = (int)($_GET['post'] ?? 0);
$pm = (new Manager\PM($Viewer))->findByPostId($postId);
if (is_null($pm)) {
    error(403);
}

$body = $pm->postBody($postId);
if (is_null($body)) {
    error(404);
}

// This gets sent to the browser, which echoes it wherever
header('Content-type: text/plain');
echo $body;
