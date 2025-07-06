<?php

namespace Gazelle\UserRank\Dimension;

class CollageContributed extends \Gazelle\UserRank\AbstractUserRank {
    public function cacheKey(): string {
        return 'rank_data_collagecontrib';
    }

    public function selector(): string {
        return "
            SELECT collage_contrib
            FROM user_summary
            WHERE collage_contrib > 0
            ORDER BY 1
        ";
    }
}
