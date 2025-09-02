<?php

namespace Gazelle\Search;

use Gazelle\Enum\UserTorrentSearch;

/**
 * Collect the IDs of torrents that a user has uploaded, snatched
 * or is seeding. This is used for creating ZIP downloads of the
 * corresponding .torrent files.
 */

class UserTorrent extends \Gazelle\Base {
    private int $limit;
    private int $offset;

    public function __construct(
        protected \Gazelle\User     $user,
        protected UserTorrentSearch $type,
    ) {}

    public function label(): string {
        return $this->type->value;
    }

    public function setLimit(int $limit): static {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(int $offset): static {
        $this->offset = $offset;
        return $this;
    }

    protected function sql(): string {
        return "FROM torrents AS t "
            . match ($this->type) {
                UserTorrentSearch::downloaded => "
                    INNER JOIN users_downloads AS ud ON (ud.TorrentID = t.ID)
                    WHERE ud.UserID = ?
                    ORDER BY ud.Time DESC",
                UserTorrentSearch::leeching => "
                    INNER JOIN xbt_files_users AS xfu ON (xfu.fid = t.ID)
                    WHERE xfu.active = 1 AND xfu.Remaining > 0 AND xfu.uid = ?
                    ORDER BY from_unixtime(xfu.mtime - xfu.timespent) DESC",
                UserTorrentSearch::seeding => "
                    INNER JOIN xbt_files_users AS xfu ON (t.ID = xfu.fid)
                    WHERE xfu.remaining = 0 AND xfu.uid = ?
                    ORDER BY from_unixtime(xfu.mtime - xfu.timespent) DESC",
                UserTorrentSearch::snatched => "
                    INNER JOIN xbt_snatched AS xs ON (t.ID = xs.fid)
                    WHERE xs.uid = ?
                    ORDER BY from_unixtime(xs.tstamp) DESC",
                UserTorrentSearch::snatchedUnseeded => "
                    INNER JOIN xbt_snatched AS xs ON (xs.fid = t.ID)
                        LEFT JOIN xbt_files_users AS xfu USING (uid, fid)
                    WHERE xfu.fid IS NULL AND xs.uid = ?
                    ORDER BY from_unixtime(xs.tstamp) DESC",
                UserTorrentSearch::uploadedUnseeded => "
                    LEFT JOIN xbt_files_users AS xfu ON (xfu.fid = t.ID AND xfu.uid = t.UserID)
                    WHERE xfu.fid IS NULL AND t.UserID = ?
                    ORDER BY t.created DESC",
                // UserTorrentSearch::uploaded
                default => "
                    WHERE t.UserID = ?
                    ORDER BY t.created DESC",
            };
    }

    public function total(): int {
        return (int)self::$db->scalar("
            SELECT COUNT(DISTINCT t.ID)
            {$this->sql()}
        ", $this->user->id);
    }

    public function idList(): array {
        $sql = "
            SELECT DISTINCT t.ID
            {$this->sql()}";

        $args = [$this->user->id];
        if (isset($this->limit)) {
            $sql .= "\nLIMIT ?";
            $args[] = $this->limit;
        }
        if (isset($this->offset)) {
            $sql .= "\nOFFSET ?";
            $args[] = $this->offset;
        }

        self::$db->prepared_query($sql, ...$args);
        return self::$db->collect(0);
    }
}
