<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$tgroup = (new Manager\TGroup())->findById((int)($_GET['id'] ?? 0));
if (!$tgroup) {
    error(404);
}

echo $Twig->render('torrent/edit-request.twig', [
    'textarea' => new Util\Textarea('edit_details', ''),
    'tgroup'   => $tgroup,
    'viewer'   => $Viewer,
]);
