<?php
/** @phpstan-var \Twig\Environment $Twig */
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

if (!$Viewer->permitted('admin_audit_edit')) {
    error(403);
}
authorize();

$userMan = new Gazelle\Manager\User();
$user = $userMan->findById((int)($_REQUEST['id'] ?? 0));
if (is_null($user)) {
    error(404);
}

$idList = $_REQUEST['id_list'] ?? '';
if (!$idList) {
    header("Location: ?action=audit&edit=1&id={$user->id()}");
    exit;
}
if ($Viewer->hashHmac('audit', $idList) !== $_REQUEST['sig']) {
    error('bad signature');
}
$user->auditTrail()->modifyEventList(
    array_map('intval', explode(',', $idList)),
    trim($_REQUEST['note']),
    $Viewer
);

header("Location: {$user->location()}&action=audit");
