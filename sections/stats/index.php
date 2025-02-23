<?php
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

switch ($_REQUEST['action'] ?? null) {
    case 'users':
        include_once 'users.php';
        break;
    case 'torrents':
        include_once 'torrents.php';
        break;
    default:
        echo $Twig->render('stats.twig');
        break;
}
