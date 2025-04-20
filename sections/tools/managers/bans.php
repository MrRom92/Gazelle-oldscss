<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_ipbans')) {
    Error403::error();
}

$manager = new Manager\Ban();

$message = false;
if (isset($_POST['add'])) {
    $validator = new Util\Validator();
    $validator->setFields([
        ['ip', true, 'regex', 'You must specify an IP CIDR address.',
            ['regex' => '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(?:/\d{1,2})?#']
        ],
        ['note', true, 'string','You must include the reason for the ban.'],
    ]);
    if (!$validator->validate($_POST)) {
        Error400::error($validator->errorMessage());
    }
    $id = $manager->create($_POST['ip'], trim($_POST['note']), $Viewer);
} elseif (isset($_POST['toggle'])) {
    $ban = $manager->findById((int)($_POST['id'] ?? 0));
    if (is_null($ban)) {
        Error404::error("Ban record not found for that id");
    }
    $ban->setField('is_active', $ban->isActive() ? 'false' : 'true')
        ->modify();
    $ban->addNote($ban->isActive() ? 'Enabled' : 'Disabled', $Viewer);
    $message = "Ban on address {$ban->ip()} "
        . ($ban->isActive() ? "enabled" : "disabled");
} else {
    if (!empty($_REQUEST['note'])) {
        $manager->setFilterNotes($_REQUEST['note']);
    }
    if (!empty($_REQUEST['ip']) && preg_match(IP_REGEXP, $_REQUEST['ip'])) {
        $manager->setFilterIpaddr($_REQUEST['ip']);
    }
}

$paginator = new Util\Paginator(IPS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($manager->total());

echo $Twig->render('admin/ipaddr-bans.twig', [
    'ip'        => $_REQUEST['ip'] ?? '',
    'notes'     => $_REQUEST['notes'] ?? '',
    'header'    => $manager->header(),
    'list'      => $manager->page($paginator->limit(), $paginator->offset()),
    'message'   => $message,
    'paginator' => $paginator,
    'viewer'    => $Viewer,
]);
