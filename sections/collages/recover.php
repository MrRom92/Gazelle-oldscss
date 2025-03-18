<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('site_collages_recover')) {
    Error403::error();
}

$_POST['id'] = (int)($_POST['id'] ?? 0);
$_POST['name'] = trim($_POST['name'] ?? '');

if (!empty($_POST['id']) || $_POST['name'] !== '') {
    authorize();
    $collageMan = new Manager\Collage();
    $collage = null;

    if (!empty($_POST['id'])) {
        $collage = $collageMan->recoverById($_POST['id']);
    }
    if (!$collage && $_POST['name'] !== '') {
        $collage = $collageMan->recoverByName($_POST['name']);
    }
    if (!$collage) {
        Error404::error('Collage is completely deleted');
    } else {
        $collageId = $collage->flush()->id();
        $collage->logger()->general("Collage $collageId was recovered by " . $Viewer->username());
        header('Location: ' . $collage->location());
        exit;
    }
}

echo $Twig->render('collage/recover.twig', [
    'viewer' => $Viewer,
]);
