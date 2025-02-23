<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$wikiMan = new Manager\Wiki();
$article = $wikiMan->findById((int)$_GET['id']);
if (is_null($article)) {
    error(404);
}
if (!$article->readable($Viewer)) {
    error(403);
}

echo $Twig->render('wiki/revision-list.twig', [
    'article' => $article,
]);
