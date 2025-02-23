<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permitted('users_view_email')) {
    error(403);
}

$user = (new Manager\User())->findById((int)$_GET['userid']);
if (is_null($user)) {
    error(404);
}

echo $Twig->render('user/email-history.twig', [
    'asn'     => new Search\ASN(),
    'history' => new User\History($user),
    'user'    => $user,
]);
