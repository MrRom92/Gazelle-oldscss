<?php

namespace Gazelle\Task;

class RatioRequirements extends \Gazelle\Task {
    public function run(): void {
        $result = new \Gazelle\Manager\User()->updateRatioRequirements();
        $sum = 0;
        foreach ($result as $label => $stage) {
            if ($label === '99-exception') {
                $this->info("$label {$stage['class']} message={$stage['message']}");
            } else {
                $this->info("$label n={$stage[0]} t={$stage[1]}");
                $sum += $stage[0];
            }
        }
        $this->processed = (int)$sum;
    }
}
