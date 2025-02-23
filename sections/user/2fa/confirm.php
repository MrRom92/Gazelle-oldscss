<?php
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

echo $Twig->render('user/2fa/remove.twig', [
    'bad' => isset($_GET['invalid']),
]);
