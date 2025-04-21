<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

use OrpheusNET\Logchecker\Logchecker;

echo $Twig->render('logchecker/upload.twig', [
    'accepted' => Logchecker::getAcceptValues(),
    'list'     => (new Manager\Torrent())->missingLogfiles($Viewer->id),
]);
