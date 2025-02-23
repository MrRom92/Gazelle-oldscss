<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$notifier = new User\Notification\Collage($Viewer);
if (!isset($_REQUEST['collageid'])) {
    $notifier->clear();
} else {
    $collageId = (int)$_REQUEST['collageid'];
    if (!$collageId) {
        error(404);
    }
    $notifier->clearCollage($collageId);
}

header('Location: userhistory.php?action=subscribed_collages');
