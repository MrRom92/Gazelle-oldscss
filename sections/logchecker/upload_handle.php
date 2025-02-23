<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

use OrpheusNET\Logchecker\Logchecker;

ini_set('upload_max_filesize', 1_000_000);

$torrent = (new Manager\Torrent())->findById((int)$_POST['torrentid']);
if (is_null($torrent)) {
    error('No torrent is selected.');
}
if ($torrent->media() !== 'CD') {
    error('Media of torrent precludes adding a log.');
}
if ($torrent->uploaderId() != $Viewer->id() && !$Viewer->permitted('admin_add_log')) {
    error('Not your upload.');
}

$action = in_array($_POST['from_action'], ['upload', 'update']) ? $_POST['from_action'] : 'upload';
$logfileSummary = new LogfileSummary($_FILES['logfiles']);

if (!$logfileSummary->total()) {
    error("No logfiles uploaded.");
} else {
    $ripFiler = new File\RipLog();
    $htmlFiler = new File\RipLogHTML();

    $torrent->removeLogDb();
    $ripFiler->remove([$torrent->id(), null]);
    $htmlFiler->remove([$torrent->id(), null]);
    $torrentLogManager = new Manager\TorrentLog($ripFiler, $htmlFiler);

    $checkerVersion = Logchecker::getLogcheckerVersion();
    foreach ($logfileSummary->all() as $logfile) {
        $torrentLogManager->create($torrent, $logfile, $checkerVersion);
    }
    $torrent->modifyLogscore();
}

echo $Twig->render('logchecker/result.twig', [
    'summary' => $logfileSummary->all(),
    'action'  => $action,
]);
