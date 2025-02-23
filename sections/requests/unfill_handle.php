<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

authorize();

$request = (new Manager\Request())->findById((int)$_REQUEST['id']);
if (is_null($request)) {
    error(404);
}
if (
    $request->fillerId() === 0
    || (
        !in_array($Viewer->id(), [$request->userId(), $request->fillerId()])
        && !$Viewer->permitted('site_moderate_requests')
    )
) {
    error(403);
}

$request->unfill($Viewer, trim($_POST['reason']), new Manager\Torrent());

header('Location: ' . $request->location());
