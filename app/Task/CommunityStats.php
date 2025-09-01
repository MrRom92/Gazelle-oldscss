<?php

namespace Gazelle\Task;

use Gazelle\Stats\Users   as UsersStats;
use Gazelle\Stats\TGroups as TGroupsStats;

class CommunityStats extends \Gazelle\Task {
    public function run(): void {
        $usersStats = new UsersStats();
        $this->processed = new TGroupsStats()->refresh()
            + $usersStats->refresh()
            + $usersStats->refreshUseragentTracker();
    }
}
