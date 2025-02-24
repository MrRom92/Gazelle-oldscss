<?php
/** @phpstan-var \Twig\Environment $Twig */
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

if (!$Viewer->permitted('admin_audit_view')) {
    error(403);
}
$userMan = new Gazelle\Manager\User();
$user = $userMan->findById((int)($_REQUEST['id'] ?? 0));
if (is_null($user)) {
    error(404);
}

$idList = array_map('intval', $_REQUEST['idlist'] ?? []);
if (!$idList) {
    header("Location: ?action=audit&edit=1&id={$user->id()}");
    exit;
}

$eventList = $user->auditTrail()->eventList($idList);
echo $Twig->render('user/audit-edit.twig', [
    'id_list' => implode(
        ',',
        array_map(fn ($e) => $e['id_user_audit_trail'], $eventList),
    ),
    'note' => new Gazelle\Util\Textarea(
        'note',
        implode(
            "\n\n",
            array_map(fn ($e) => $e['note'], $eventList)
        ),
        90,
        10,
    ),
    'user'   => $user,
    'viewer' => $Viewer,
]);
