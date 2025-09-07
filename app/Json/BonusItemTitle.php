<?php

namespace Gazelle\Json;

use Gazelle\Manager\Bonus as BonusManager;

class BonusItemTitle extends \Gazelle\Json {
    public function __construct(
        protected string $label,
        protected string $title,
        protected BonusManager $manager = new BonusManager(),
    ) {}

    public function payload(): array {
        $item = $this->manager->findBonusItemByLabel($this->label);
        if (is_null($item)) {
            return [];
        }
        return [
            $item->label() === 'title-bb-y'
                ? \Text::full_format($this->title)
                : \Text::strip_bbcode($this->title)
        ];
    }
}
