<?php

namespace Gazelle\Json;

use Gazelle\Manager\User as UserManager;
use Gazelle\BonusItem    as BonusItem;
use Gazelle\User         as User;

class BonusUserOther extends \Gazelle\Json {
    public function __construct(
        protected User        $user,
        protected BonusItem   $item,
        protected string      $username,
        protected UserManager $manager = new UserManager(),
    ) {}

    public function payload(): array {
        $other = $this->manager->findByUsername($this->username);
        if (is_null($other)) {
            return [
                'found'    => false,
                'username' => $this->username,
            ];
        }
        $points = $this->user->bonusPointsTotal();
        $price  = $this->item->priceForTokenOther($this->user, $other);
        if ($price === false) {
            json_error("bad item label received");
        }
        return [
            'found'    => true,
            'accept'   => !$other->hasAttr('no-fl-gifts'),
            'enabled'  => $other->isEnabled(),
            'id'       => $other->id,
            'price'    => $price,
            'percent5' => $points === 0 ? 0 : ceil(($price / $points) * 20) * 5,
            'username' => $other->username(),
        ];
    }
}
