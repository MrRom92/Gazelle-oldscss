<?php
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$tgroupId = (int)($_GET['id'] ?? 0);
if (!$tgroupId) {
    // we may not have a torrent group because it has already been merged elsewhere
    // so the best we can hope for is something that looks like a positive integer
    Error404::error();
}
$tgroup = new Manager\TGroup()->findById($tgroupId);

echo $Twig->render('tgroup/group-log.twig', [
    'id'     => $tgroupId,
    'tgroup' => $tgroup,
    'log'    => new Manager\SiteLog()->tgroupLogList($tgroupId),
]);
