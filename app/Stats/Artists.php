<?php

namespace Gazelle\Stats;

class Artists extends \Gazelle\Base {
    public function updateUsage(): int {
        self::$db->begin_transaction();
        self::$db->prepared_query("
            DELETE FROM artist_usage
        ");
        self::$db->prepared_query("
            INSERT INTO artist_usage (artist_id, role, artist_role_id, uses)
            SELECT aa.ArtistID, ta.Importance, ta.artist_role_id, count(*) AS uses
            FROM torrents_artists ta
            INNER JOIN artists_alias aa USING (AliasID)
            GROUP BY aa.ArtistID, ta.Importance, ta.artist_role_id
        ");
        $affected = self::$db->affected_rows();
        self::$db->commit();
        return $affected;
    }
}
