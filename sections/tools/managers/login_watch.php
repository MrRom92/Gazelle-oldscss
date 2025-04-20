<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permittedAny('admin_login_watch', 'admin_manage_ipbans')) {
    Error403::error();
}

$watch = new LoginWatch('0.0.0.0');
if ($_POST) {
    authorize();
    $canBan  = $Viewer->permitted('admin_manage_ipbans');
    $ban     = [];
    $clear   = [];
    foreach (array_key_filter_and_map('admin-', $_POST) as $id => $val) {
        if ($canBan && $val === 'ban') {
            $ban[] = $id;
        } elseif ($val === 'clear') {
            $clear[] = $id;
        }
    }
    if ($ban) {
        $nrBan = $watch->setBan(
            $_REQUEST['reason'] ?? "Banned by {$Viewer->username()} from login watch",
            $ban,
            $Viewer,
        );
    }
    if ($clear) {
        $nrClear = $watch->setClear($clear, $Viewer);
    }
}

$paginator = new Util\Paginator(IPS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($watch->activeTotal());

echo $Twig->render('admin/login-watch.twig', [
    'nr_ban'    => $nrBan ?? null,
    'nr_clear'  => $nrClear ?? null,
    'paginator' => $paginator,
    'viewer'    => $Viewer,
    'watch'     => $watch,
]);
