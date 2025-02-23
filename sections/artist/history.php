<?php
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$artistMan = new Manager\Artist();
$artist = $artistMan->findById((int)$_GET['artistid']);
if (is_null($artist)) {
    error(404);
}

echo $Twig->render('revision.twig', ['object' => $artist]);
