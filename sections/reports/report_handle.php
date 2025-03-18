<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Util\Irc;

authorize();

$subjectId = (int)$_POST['id'];
if (!$subjectId || empty($_POST['type']) || ($_POST['type'] !== 'request_update' && empty($_POST['reason']))) {
    Error404::error();
}

require_once 'array.php';
/** @var array $Types */
if (!array_key_exists($_POST['type'], $Types)) {
    Error403::error();
}
$subjectType = (string)$_POST['type'];

if ($subjectType !== 'request_update') {
    $reason = $_POST['reason'];
} else {
    $year = trim($_POST['year']);
    if (empty($year) || !is_number($year)) {
        Error400::error('Year must be specified.');
    }
    $reason = "[b]Year[/b]: {$year}.\n\n";
    // If the release type is somehow invalid, return "Not given"; otherwise, return the release type.
    $reason .= '[b]Release type[/b]: ' . ((empty($_POST['releasetype']) || !is_number($_POST['releasetype']) || $_POST['releasetype'] == '0')
        ? 'Not given' : (new ReleaseType())->findNameById((int)$_POST['releasetype'])) . " . \n\n";
    $reason .= '[b]Additional comments[/b]: ' . $_POST['comment'];
}

$location = match ($subjectType) {
    'collage'        => "collages.php?id=$subjectId",
    'comment'        => "comments.php?action=jump&postid=$subjectId",
    'post'           => (new Manager\ForumPost())->findById($subjectId)?->location(), // could be null
    'request',
    'request_update' => "requests.php?action=view&id=$subjectId",
    'thread'         => "forums.php?action=viewthread&threadid=$subjectId",
    'user'           => "user.php?id=$subjectId",
    default          => null, // definitely a problem
};
if (is_null($location)) {
    Error400::error("Cannot generate a link to the reported item '$subjectType'");
}

$report = (new Manager\Report(new Manager\User()))->create($Viewer, $subjectId, $subjectType, $reason);
if (in_array($report->subjectType(), ['user', 'comment'])) {
    Irc::sendMessage(
        IRC_CHAN_MOD,
        "{$Viewer->username()} reported a $subjectType, see {$report->location()}"
    );
}
header("Location: $location");
