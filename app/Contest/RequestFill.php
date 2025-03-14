<?php

namespace Gazelle\Contest;

use Gazelle\Enum\UserStatus;

/* Contest based on how many requests filled
 *
 * Note: the queries all perform a join on the torrents table
 * which looks useless, but offers an additional guarantee
 * that the request is valid.
 */

class RequestFill extends AbstractContest {
    public function leaderboard(int $limit, int $offset): array {
        $key = sprintf(\Gazelle\Contest::CONTEST_LEADERBOARD_CACHE_KEY,
            $this->id, (int)($offset / CONTEST_ENTRIES_PER_PAGE)
        );
        $leaderboard = self::$cache->get_value($key);
        if ($leaderboard === false) {
            self::$db->prepared_query("
                SELECT
                    l.user_id,
                    l.entry_count,
                    l.last_entry_id,
                    t.created as last_upload,
                    t.GroupID as group_id
                FROM contest_leaderboard l
                INNER JOIN torrents t ON (t.ID = l.last_entry_id)
                INNER JOIN requests r ON (r.FillerID = t.UserID AND t.ID = r.TorrentID)
                INNER JOIN users_main um ON (um.ID = l.user_id)
                INNER JOIN xbt_files_users xfu ON (xfu.fid = t.ID AND xfu.uid = t.UserID)
                WHERE xfu.remaining   = 0
                    AND r.FillerID   != r.UserID
                    AND um.Enabled    = ?
                    AND  l.contest_id = ?
                ORDER BY l.entry_count DESC, t.created ASC, l.user_id ASC
                LIMIT ? OFFSET ?
                ", UserStatus::enabled->value, $this->id, $limit, $offset
            );
            $leaderboard = self::$db->to_array(false, MYSQLI_ASSOC, false);

            $torMan = new \Gazelle\Manager\Torrent();
            for ($i = 0, $leaderboardCount = count($leaderboard); $i < $leaderboardCount; $i++) {
                $torrent = $torMan->findById($leaderboard[$i]['last_entry_id']);
                $leaderboard[$i]['last_entry_link']
                    = $torrent->groupLink() . ' ' . $torrent->label();
            }
            self::$cache->cache_value($key, $leaderboard, 3600);
        }
        return $leaderboard;
    }

    public function ranker(): array {
        return ["
            SELECT r.FillerID    AS user_id,
                count(*)         AS nr,
                max(r.TorrentID) AS last_torrent
            FROM requests r
            INNER JOIN torrents t ON (t.ID = r.TorrentID)
            INNER JOIN users_main um ON (um.ID = r.FillerID)
            WHERE r.FillerId != r.UserID
                AND um.Enabled = ?
                AND r.created < ?
                AND r.TimeFilled BETWEEN ? AND ?
            GROUP BY r.FillerID
            ",
            [
                UserStatus::enabled->value,
                $this->begin,
                $this->begin, $this->end,
            ]
        ];
    }

    public function participationStats(): array {
        return self::$db->rowAssoc("
            SELECT count(DISTINCT r.ID) AS total_entries,
                count(DISTINCT um.ID) AS total_users
            FROM contest c,
                users_main um
            INNER JOIN requests r ON (r.FillerID = um.ID)
            INNER JOIN torrents t ON (t.ID = r.TorrentID)
            WHERE r.FillerId != r.UserID
                AND r.TimeFilled BETWEEN c.date_begin AND c.date_end
                AND r.created < c.date_begin
                AND um.Enabled   = ?
                AND c.contest_id = ?
            ", UserStatus::enabled->value, $this->id
        );
    }

    public function userPayout(): array {
        self::$db->prepared_query("
            WITH c AS (
                SELECT um.ID    AS user_id,
                    count(r.ID) AS total_entries
                FROM contest c,
                    users_main um
                INNER JOIN requests r ON (r.FillerID = um.ID)
                INNER JOIN torrents t ON (t.ID = r.TorrentID)
                WHERE r.FillerId != r.UserID
                    AND r.TimeFilled BETWEEN c.date_begin AND c.date_end
                    AND r.created < c.date_begin
                    AND um.Enabled   = ?
                    AND c.contest_id = ?
                GROUP BY um.ID
            )
            SELECT um.ID                     AS user_id,
                coalesce(c.total_entries, 0) AS total_entries
            FROM users_main um
            LEFT JOIN c ON (c.user_id = um.ID)
            WHERE um.Enabled = ?
                AND um.created <= (SELECT date_end FROM contest WHERE contest_id = ?)
            ", UserStatus::enabled->value, $this->id,
               UserStatus::enabled->value, $this->id,
        );
        return self::$db->to_array(false, MYSQLI_ASSOC, false);
    }

    public function requestPairs(): array {
        $key = "contest_pairs_" . $this->id;
        $pairs = self::$cache->get_value($key);
        if ($pairs === false) {
            self::$db->prepared_query("
                SELECT r.FillerID, r.UserID, count(*) AS nr
                FROM requests r
                INNER JOIN torrents t ON (t.ID = r.TorrentID)
                WHERE r.TimeFilled BETWEEN ? AND ?
                GROUP BY r.FillerID, r.UserId
                HAVING count(*) > 1
                ORDER BY count(*) DESC, r.FillerID ASC
                LIMIT 100
                ", $this->begin, $this->end
            );
            $pairs = self::$db->to_array(false, MYSQLI_ASSOC, false);
            self::$cache->cache_value('contest_pairs_' . $this->id, $pairs, 3600);
        }
        return $pairs;
    }
}
