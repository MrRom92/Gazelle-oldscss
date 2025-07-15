<?php

namespace Gazelle\Intf;

interface Bookmarked {
    // objects that implement this can be bookmarked
    public function id(): int;

    public function bookmarkTable(): string;

    public function bookmarkColumnName(): string;
}
