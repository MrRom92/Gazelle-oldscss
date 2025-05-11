<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (session_status() === PHP_SESSION_NONE) {
    session_start(['read_and_close' => true]);
}

$mfa  = $Viewer->MFA();
$valid = true;
if (!empty($_SESSION['private_key'])) {
    $secret = $_SESSION['private_key'];
    if (isset($_POST['mfa'])) {
        if ($mfa->verifyCode($secret, trim($_POST['mfa']))) {
            header("Location: user.php?action=mfa&do=complete&userid={$Viewer->id}");
            exit;
        }
        $valid = false;
    }
} else {
    $secret = $mfa->generateSessionSecret();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['private_key'] = $secret;
    session_write_close();
}

echo $Twig->render('user/mfa/configure.twig', [
    'valid'  => $valid,
    'qrcode' => $mfa->generateQrCode(secret: $secret, logo: QRCODE_LOGO),
    'secret' => $secret,
    'viewer' => $Viewer,
]);
