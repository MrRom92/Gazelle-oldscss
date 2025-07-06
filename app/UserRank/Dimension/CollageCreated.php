<?php

namespace Gazelle\UserRank\Dimension;

class CollageCreated extends \Gazelle\UserRank\AbstractUserRank {
    public function cacheKey(): string {
        return 'rank_data_collagecontrib';
    }

    public function selector(): string {
        return "
            SELECT collage_total
            FROM user_summary
            WHERE collage_total > 0
            ORDER BY 1
        ";
    }
}
