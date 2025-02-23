<?php
/** @phpstan-var \Gazelle\User $Viewer */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('torrents_edit')) {
    error(403);
}

authorize();

$artistMan = new Manager\Artist();
$artist = $artistMan->findById((int)($_POST['artistid'] ?? 0));
if (is_null($artist) || empty($_POST['aliasid'])) {
    error(404);
} elseif ($artist->isLocked() && !$Viewer->permitted('users_mod')) {
    error('This artist is locked.');
}

$aliasId = (int)$_POST['aliasid'];
$newName = Artist::sanitize($_POST['name']);
if (empty($newName)) {
    error('No new name given.');
} elseif (!isset($artist->aliasList()[$aliasId])) {
    error('Could not find existing alias ID');
} elseif ($artist->aliasList()[$aliasId]['name'] === $newName) {
    error('The new name is identical to the old name."');
}
$oldName = $artist->aliasList()[$aliasId]['name'];

$otherArtist = $artistMan->findByName($newName);
if (!is_null($otherArtist) && $otherArtist->id() !== $artist->id()) {
    error("An artist with this alias already exists: {$otherArtist->name()} ({$otherArtist->id()})");
}

$result = $artist->renameAlias(
    $aliasId,
    $newName,
    $Viewer,
    new Manager\Request(),
    new Manager\TGroup(),
);

if (is_null($result)) {
    error("The specified name is already in use.");
}

header("Location: artist.php?artistid={$artist->id()}&action=edit");
