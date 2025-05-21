<?php

namespace Gazelle;

class RelayDatabase extends Base {
    public function relay(): int {
        return new Manager\Request()->relay();
    }
}
