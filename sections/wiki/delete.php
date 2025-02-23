<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

if (!$Viewer->permitted('admin_manage_wiki')) {
    error(403);
}

$article = (new Manager\Wiki())->findById((int)$_GET['id']);
if (is_null($article)) {
    error(404);
}
if (!$article->editable($Viewer)) {
    error(403);
}
if ($article->id() == INDEX_WIKI_PAGE_ID) {
    error('You cannot delete the main wiki article.');
}

$article->logger()->general(
    "Wiki article {$article->id()} \"{$article->title()}\" was deleted by {$Viewer->username()}"
);
$article->remove();

header("location: wiki.php");
