<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$contestMan = new Manager\Contest();

switch ($_GET['action'] ?? '') {
    case 'leaderboard':
        include 'leaderboard.php';
        break;
    case 'admin':
    case 'create':
        include 'admin.php';
        break;
    default:
        echo $Twig->render('contest/intro.twig', [
            'contest' => $contestMan->currentContest(),
            'viewer'  => $Viewer,
        ]);
}
