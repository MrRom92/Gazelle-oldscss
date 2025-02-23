<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

$tgroup = (new Manager\TGroup())->findById((int)($_GET['id'] ?? 0));
if (is_null($tgroup)) {
    error(404);
}
$torMan = new Manager\Torrent();

echo $Twig->render('tgroup/edit.twig', [
    'body'         => new Util\Textarea('body', $tgroup->description(), 80, 20),
    'release_type' => (new ReleaseType())->list(),
    'tgroup'       => $tgroup->showFallbackImage(false),
    'viewer'       => $Viewer,
    'leech_type'   => $torMan->leechTypeList(),
    'leech_reason' => $torMan->leechReasonList(),
    'size'         => NEUTRAL_LEECH_THRESHOLD,
    'unit'         => NEUTRAL_LEECH_UNIT,
]);
