<?php

declare(strict_types=1);

namespace Gazelle;

$body = new Manager\Comment()->findBodyById((int)($_GET['postid'] ?? 0));
if (is_null($body)) {
    Error404::error();
}

header('Content-type: text/plain');
echo $body;
