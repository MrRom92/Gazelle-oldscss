<?php
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

use OrpheusNET\Logchecker\Logchecker;

echo $Twig->render('logchecker/test.twig', [
    'accepted' => Logchecker::getAcceptValues(),
]);
