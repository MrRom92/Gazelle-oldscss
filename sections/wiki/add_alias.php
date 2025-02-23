<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$article = (new Manager\Wiki())->findById((int)$_POST['article']);
if (is_null($article)) {
    error(404);
}
if (!$article->editable($Viewer)) {
    error(403);
}

try {
    $article->addAlias(trim($_POST['alias']), $Viewer);
} catch (DB\MysqlDuplicateKeyException) {
    error('The alias you attempted to add is already assigned to an article.');
}

header('Location: ' . $article->location());
