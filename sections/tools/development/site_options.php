<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

if (!$Viewer->permittedAny('admin_manage_permissions', 'users_mod')) {
    Error403::error();
}

$siteOption = new Manager\SiteOption();

if ($Viewer->permitted('admin_manage_permissions') && isset($_POST['submit'])) {
    authorize();

    $name    = trim($_POST['name']);
    $value   = trim($_POST['value']);
    $comment = trim($_POST['comment']);

    if ($_POST['submit'] == 'Delete') {
        $siteOption->remove($name);
    } else {
        $Val = new Util\Validator();
        $Val->setFields([
            ['name', true, 'regex', 'The name must be alphanumeric and may contain dashes or underscores. No spaces are allowed.', ['regex' => '/^[a-z][-_a-z0-9]{0,63}$/i']],
            ['value', true, 'string', 'You must specify a value for the option.'],
            ['comment', true, 'string', 'You must specify a comment for the option.'],
        ]);
        if (!$Val->validate($_POST)) {
            Error400::error($Val->errorMessage());
        }

        if ($_POST['submit'] == 'Edit') {
            $siteOption->modify((int)$_POST['id'], $name, $value, $comment);
        } else {
            $siteOption->create($name, $value, $comment);
        }
    }
}

echo $Twig->render('admin/site-option.twig', [
    'list'   => $siteOption->list(),
    'viewer' => $Viewer,
]);
