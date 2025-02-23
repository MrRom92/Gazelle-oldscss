<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('admin_manage_blog')) {
    error(403);
}
authorize();

$blog = (new Manager\Blog())->findById((int)($_GET['id'] ?? 0));
if (is_null($blog)) {
    error('Please provide the ID of a blog post from which to remove the thread link.');
}
$blog->removeThread();

header('Location: blog.php');
