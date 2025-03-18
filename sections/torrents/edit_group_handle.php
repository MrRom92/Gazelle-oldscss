<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

authorize();

if (!$Viewer->permitted('site_edit_wiki')) {
    Error403::error();
}
if (!$Viewer->permitted('torrents_edit_vanityhouse') && isset($_POST['vanity_house'])) {
    Error403::error();
}
$tgroup = (new Manager\TGroup())->findById((int)$_REQUEST['groupid']);
if (is_null($tgroup)) {
    Error404::error();
}

$logInfo = [];
if (($_GET['action'] ?? '') == 'revert') {
    // we're reverting to a previous revision
    $revisionId = (int)$_GET['revisionid'];
    if (!$revisionId) {
        Error400::error('No revision specified to revert');
    }
    if (empty($_GET['confirm'])) {
        echo $Twig->render('tgroup/confirm-revert.twig', [
            'group_id'    => $tgroup->id(),
            'revision_id' => $revisionId,
            'viewer'      => $Viewer,
        ]);
        exit;
    }
    $revert = $tgroup->revertRevision($Viewer->id(), $revisionId);
    if (is_null($revert)) {
        Error404::error();
    }
    [$Body, $Image] = $revert;
} else {
    if ($tgroup->categoryName() === 'Music') {
        // edit, variables are passed via POST
        $ReleaseType = (int)$_POST['releasetype'];
        $rt = new ReleaseType();
        $newReleaseTypeName = $rt->findNameById($ReleaseType);
        if (!$newReleaseTypeName) {
            Error400::error();
        }
        if ($ReleaseType != $tgroup->releaseType()) {
            $tgroup->setField('ReleaseType', $ReleaseType);
            $logInfo[] = "Release type changed from "
                . $rt->findNameById($tgroup->releaseType())
                . " to $newReleaseTypeName";
        }

        if ($Viewer->permitted('torrents_edit_vanityhouse')) {
            $showcase = isset($_POST['vanity_house']) ? 1 : 0;
            if ($tgroup->isShowcase() != $showcase) {
                $tgroup->setField('VanityHouse', $showcase);
                $logInfo[] = 'Vanity House status changed to ' . ($showcase ? 'true' : 'false');
            }
        }
    }

    if (empty($_POST['image'])) {
        $Image = '';
    } else {
        $Image = $_POST['image'];
        if (!preg_match(IMAGE_REGEXP, $Image)) {
            Error400::error(html_escape($Image) . " does not look like a valid image url");
        }
        $banned = (new Util\ImageProxy($Viewer))->badHost($Image);
        if ($banned) {
            Error400::error("Please rehost images from $banned elsewhere.");
        }
    }

    $Body = trim($_POST['body']);
    if ($_POST['summary']) {
        $logInfo[] = "summary: " . trim($_POST['summary']);
    }
    $revisionId = $tgroup->createRevision($Body, $Image, $_POST['summary']);
}

$imageFlush = ($Image != $tgroup->showFallbackImage(false)->image());

$tgroup->setField('WikiBody', $Body)
    ->setField('WikiImage', $Image)
    ->modify();

if ($imageFlush) {
    $tgroup->imageFlush();
}

$noCoverArt = isset($_POST['no_cover_art']);
if ($noCoverArt != $tgroup->hasNoCoverArt()) {
    $tgroup->toggleNoCoverArt($noCoverArt);
    $logInfo[] = "No cover art exception " . ($noCoverArt ? 'added' : 'removed');
}
if ($logInfo) {
    $tgroup->logger()->group($tgroup, $Viewer, implode(', ', $logInfo));
}

header('Location: ' . $tgroup->location());
