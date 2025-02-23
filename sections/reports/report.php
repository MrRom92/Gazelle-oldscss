<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$id = (int)$_GET['id'];
if (!$id) {
    error(404);
}

require_once 'array.php';
if (!isset($Types[$_GET['type'] ?? ''])) {
    error(403);
}
$type = $_GET['type'];
$reportType = $Types[$type];

switch ($type) {
    case 'user':
        $user = (new Manager\User())->findById($id);
        if (is_null($user)) {
            error(404);
        }
        $report = new Report\User($id, $user);
        break;

    case 'request':
        $request = (new Manager\Request())->findById($id);
        if (is_null($request)) {
            error(404);
        }
        $report = new Report\Request($id, $request);
        break;

    case 'request_update':
        $request = (new Manager\Request())->findById($id);
        if (is_null($request)) {
            error(404);
        }
        if ($request->isFilled() || $request->categoryName() != 'Music' || $request->year() != 0) {
            error(403);
        }
        $report = (new Report\Request($id, $request))->isUpdate(true);
        break;

    case 'collage':
        $collage = (new Manager\Collage())->findById($id);
        if (is_null($collage)) {
            error(404);
        }
        $report = new Report\Collage($id, $collage);
        break;

    case 'thread':
        $thread = (new Manager\ForumThread())->findById($id);
        if (is_null($thread)) {
            error(404);
        }
        if (!$Viewer->readAccess($thread->forum())) {
            error(403);
        }
        $report = new Report\ForumThread($id, $thread);
        break;

    case 'post':
        $post = (new Manager\ForumPost())->findById($id);
        if (is_null($post)) {
            error(404);
        }
        if (!$Viewer->readAccess($post->thread()->forum())) {
            error(403);
        }
        $report = new Report\ForumPost($id, $post);
        break;

    case 'comment':
        $comment = (new Manager\Comment())->findById($id);
        if (is_null($comment)) {
            error(404);
        }
        $report = (new Report\Comment($id, $comment))->setContext($reportType['title']);
        break;
    default:
        error('Unknown report target');
}

echo $Twig->render('report/create.twig', [
    'id'          => $id,
    'release'     => (new ReleaseType())->list(),
    'report'      => $report,
    'report_type' => $reportType,
    'type'        => $type,
    'viewer'      => $Viewer,
]);
