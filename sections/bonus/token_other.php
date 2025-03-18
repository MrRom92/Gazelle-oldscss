<?php
/** @phpstan-var \Gazelle\User $Viewer */
/** @phpstan-var \Twig\Environment $Twig */

declare(strict_types=1);

namespace Gazelle;

/**
 * @var array  $Item
 * @var string $Label
 * @var int    $Price
 */

if (isset($_POST['confirm'])) {
    authorize();
    if (empty($_POST['user'])) {
        Error404::error('You have to enter a username to give tokens to.');
    }
    $user = (new Manager\User())->findByUsername(urldecode($_POST['user']));
    if (is_null($user)) {
        Error404::error(
            'Nobody with that name found. Try a user search and give them tokens from their profile page.'
        );
    } elseif ($user->id() == $Viewer->id()) {
        Error400::error('You cannot gift yourself tokens, they are cheaper to buy directly.');
    }
    $viewerBonus = new \Gazelle\User\Bonus($Viewer);
    if (!$viewerBonus->purchaseTokenOther($user, $Label, $_POST['message'] ?? '')) {
        Error400::error('Purchase for other not concluded. Either you lacked funds or they have chosen to decline FL tokens.');
    }
    header('Location: bonus.php?complete=' . urlencode($Label));
}

echo $Twig->render('bonus/token-other.twig', [
    'auth'     => $Viewer->auth(),
    'price'    => $Price,
    'label'    => $Label,
    'textarea' => new Util\Textarea('message', ''),
    'item'     => $Item['Title']
]);
