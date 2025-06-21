<?php

declare(strict_types=1);

namespace Gazelle;

authorize();

$tgMan = new Manager\TGroup();
$tgroup = $tgMan->findById((int)$_POST['groupid']);
if (is_null($tgroup)) {
    Error404::error();
}

if ($tgroup->addArtists($_POST['importance'], $_POST['aliasname']) < 1) {
    Error400::error("artist already added");
}

header('Location: ' . redirectUrl($tgroup->location()));
