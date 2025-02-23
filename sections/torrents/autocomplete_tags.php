<?php

declare(strict_types=1);

namespace Gazelle;

header('Content-Type: application/json; charset=utf-8');

$tagMan = new \Gazelle\Manager\Tag();
$word = $tagMan->sanitize($_GET['query']);

echo json_encode([
    'query' => $word,
    'suggestions' => $tagMan->autocompleteAsJson($word),
]);
