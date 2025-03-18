<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_view_ips')) {
    Error403::error();
}

$manager = new Manager\DuplicateIP();
$paginator = new Util\Paginator(USERS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($manager->total(IP_OVERLAPS));

echo $Twig->render('admin/duplicate-ipaddr.twig', [
    'list'      => $manager->page(IP_OVERLAPS, $paginator->limit(), $paginator->offset()),
    'paginator' => $paginator,
]);
