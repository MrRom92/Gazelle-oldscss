<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

use Gazelle\Enum\NotificationType;

if (!$Viewer->permitted('admin_manage_blog')) {
    error(403);
}
authorize();

$body = trim($_POST['body']);
if (empty($body)) {
    error('The body of the blog article must not be empty');
}

$title = trim($_POST['title']);
if (empty($title)) {
    error('The title of the blog article must not be empty');
}

$thread = match ((int)($_POST['thread'] ?? -1)) {
    -1 => null,
     0 => (new Manager\ForumThread())->create(
        forum: new Forum(ANNOUNCEMENT_FORUM_ID),
        user:  $Viewer,
        title: $title,
        body:  $body,
    ),
    default => (new Manager\ForumThread())->findById((int)$_POST['thread']),
};

$blog = (new Manager\Blog())->create([
    'title'     => $title,
    'body'      => $body,
    'important' => isset($_POST['important']) ? 1 : 0,
    'threadId'  => $thread?->id(),
    'userId'    => $Viewer->id(),
]);

if ($thread && isset($_POST['subscribe'])) {
    (new User\Subscription($Viewer))->subscribe($thread);
}
$notification = new Manager\Notification();
$notification->push(
    $notification->pushableTokens(NotificationType::BLOG),
    "New blog article",
    $blog->title(),
    $blog->publicLocation()
);

Util\Irc::sendMessage(IRC_CHAN, "New blog article: " . $blog->title());

header('Location: blog.php');
