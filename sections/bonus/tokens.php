<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

/**
 * @var string $Label
 */

authorize();

if (!preg_match('/^token-[1-4]$/', $Label, $match)) {
    Error403::error();
}

$viewerBonus = new \Gazelle\User\Bonus($Viewer);
if (!$viewerBonus->purchaseToken($Label)) {
    Error400::error(
        "You aren't able to buy those tokens. Do you have enough bonus points?"
    );
}

header('Location: bonus.php?complete=' . urlencode($Label));
