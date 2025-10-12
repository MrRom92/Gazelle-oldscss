<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

$filter = $_GET['filter'] ?? '';

$userMan = new Manager\User();
if (empty($_GET['userid'])) {
    $user = $Viewer;
} else {
    $userId = (int)$_GET['userid'];
    if ($userId !== $Viewer->id && !$Viewer->permitted('admin_reports')) {
        Error403::error();
    }
    $user = $userMan->findById($userId);
    if (is_null($user)) {
        Error404::error();
    }
}
$ownProfile = $user->id === $Viewer->id;

if (!empty($filter) && !in_array($filter, ['resolved', 'open'])) {
    Error400::error('Invalid filter specified');
}

$search = new Search\Torrent\Report($filter, '', new Manager\Torrent\ReportType(), $userMan)
    ->setReporter($user)
    ->setOrderBy('r.ReportedTime DESC');

$paginator = new Util\Paginator(50, (int)($_REQUEST['page'] ?? 1));
$paginator->setTotal($search->total());

$torManager = new Manager\Torrent();
$reportManager = new Manager\Torrent\Report($torManager);

echo $Twig->render('user/report-history.twig', [
    'filter'    => $filter,
    'list'      => $search->page($reportManager, $paginator->limit(), $paginator->offset()),
    'paginator' => $paginator,
    'user'      => $user,
    'viewer'    => $Viewer,
]);
