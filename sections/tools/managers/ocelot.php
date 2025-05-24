<?php

declare(strict_types=1);

namespace Gazelle;

/* This page is called only by Ocelot */

if (
    !(
           ($_SERVER['REMOTE_ADDR'] ?? '') === TRACKER_HOST
        && ($_GET['key']            ?? '') === TRACKER_SECRET
        && ($_GET['type']           ?? '') === 'expiretoken'
        && isset($_GET['tokens'])
    )
) {
    Error403::error();
}

new Tracker()->expireFreeleechTokens($_GET['tokens']);
