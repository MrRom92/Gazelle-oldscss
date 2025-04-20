<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect

declare(strict_types=1);

namespace Gazelle;

/*
 * This is the AJAX page that gets called from the JavaScript
 * function NewReport(), any changes here should probably be
 * replicated on static.php.
 */

if (!$Viewer->permitted('admin_reports')) {
    Error403::error();
}

$torMan    = new Manager\Torrent();
$reportMan = new Manager\Torrent\Report($torMan);

$report = $reportMan->findNewest();
if (is_null($report->torrent())) {
    echo $Twig->render('reportsv2/deleted.twig', [
        'report'  => $report,
    ]);
    $report->resolve('Report already dealt with (torrent deleted)');
    exit;
}

$report->claim($Viewer);
echo $Twig->render('reportsv2/new.twig', [
    'category_list' => (new Manager\Torrent\ReportType())
        ->categoryList($report->reportType()->categoryId()),
    'report'        => $report,
    'request_list'  => (new Manager\Request())->findByTorrentReported($report->torrent()),
    'size'          => '(' . number_format($report->torrent()->size() / (1024 * 1024), 2) . ' MiB)',
    'torrent'       => $report->torrent(),
    'other'         => [
        'group'     => $reportMan->totalReportsTGroup($report->torrent()->group()) - 1,
        'uploader'  => $reportMan->totalReportsUploader($report->torrent()->uploader()) - 1,
    ],
    'viewer'        => $Viewer,
]);
