<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if ($Viewer->disableBonusPoints()) {
    Error403::error('Your bonus points have been deactivated.');
}

switch ($_REQUEST['action'] ?? '') {
    case 'bprates':
        include_once 'bprates.php';
        break;
    case 'cacheflush':
        new Manager\Bonus()->flush();
        header("Location: bonus.php");
        exit;
    case 'history':
        include_once 'history.php';
        break;
    case 'prepare':
        include_once 'prepare.php';
        break;
    case 'purchase':
        include_once 'purchase.php';
        break;
    case 'donate':
    default:
        include_once 'shop.php';
        break;
}
