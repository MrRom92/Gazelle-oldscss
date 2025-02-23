<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$alias = $_GET['alias'] ?? '';
$article = (new Manager\Wiki())->findByAlias($alias);
if (is_null($article)) {
    error(404);
}

if (!$article->editable($Viewer)) {
    error(403);
}

$article->removeAlias($alias);
