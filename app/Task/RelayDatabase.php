<?php

namespace Gazelle\Task;

class RelayDatabase extends \Gazelle\Task {
    public function run(): void {
        $this->processed += new \Gazelle\RelayDatabase()->relay();
    }
}
