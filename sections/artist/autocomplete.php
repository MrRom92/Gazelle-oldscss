<?php

declare(strict_types=1);

namespace Gazelle;

header('Content-Type: application/json; charset=utf-8');

$prefix = trim(urldecode($_GET['query'] ?? ''));
echo json_encode([
    'query'       => $prefix,
    'suggestions' => new Manager\Artist()->autocompleteList($prefix),
]);
