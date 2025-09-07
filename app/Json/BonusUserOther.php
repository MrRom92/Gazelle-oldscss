<?php

namespace Gazelle\Json;

use Gazelle\Manager\User as UserManager;

class BonusUserOther extends \Gazelle\Json {
    public function __construct(
        protected string      $username,
        protected UserManager $manager = new UserManager(),
    ) {}

    public function payload(): array {
        $other = $this->manager->findByUsername($this->username);
        return is_null($other)
            ? [
                'found'    => false,
                'username' => $this->username,
            ]
            : [
                'found'    => true,
                'accept'   => !$other->hasAttr('no-fl-gifts'),
                'enabled'  => $other->isEnabled(),
                'id'       => $other->id,
                'username' => $other->username(),
            ];
    }
}
