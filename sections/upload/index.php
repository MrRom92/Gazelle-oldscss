<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_upload')) {
    Error403::error("Your userclass does not allow you to upload.");
}
if ($Viewer->disableUpload()) {
    Error403::error('Your upload privileges have been revoked.');
}

if (isset($_GET['action']) && $_GET['action'] == 'parse_html') {
    include_once 'parse_html.php';
} elseif (!empty($_POST['submit'])) {
    include_once 'upload_handle.php';
} else {
    include_once 'upload.php';
}
