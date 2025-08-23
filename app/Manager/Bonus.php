<?php

namespace Gazelle\Manager;

use Gazelle\Enum\UserStatus;

class Bonus extends \Gazelle\Base {
    final public const CACHE_OPEN_POOL = 'bonus_pool'; // also defined in \Gazelle\Bonus
    final protected const CACHE_ITEM   = 'bonus_item';

    protected array $items;

    /**
     * Return the global discount rate for the shop
     *
     * @return int Discount rate (0: normal price, 100: everything is free :)
     */
    public function discount(): int {
        return (int)new SiteOption()->findValueByName('bonus-discount');
    }

    public function itemList(): array {
        if (!isset($this->items)) {
            $items = self::$cache->get_value(self::CACHE_ITEM);
            if ($items === false) {
                $discount = $this->discount();
                self::$db->prepared_query("
                    SELECT ID,
                        Price * (greatest(0, least(100, 100 - ?)) / 100) as Price,
                        Amount, MinClass, FreeClass, Label, Title, sequence,
                        IF (Label REGEXP '^other-', 'NoOp', 'ConfirmPurchase') AS JS_on_click,
                        IF (Label REGEXP '^title-bb-[yn]', 'NoOp', 'ConfirmPurchase') AS JS_on_click
                    FROM bonus_item
                    ORDER BY sequence
                    ", $discount
                );
                $items = [];
                foreach (self::$db->to_array(false, MYSQLI_ASSOC) as $row) {
                    $row['Price'] = (int)$row['Price'];
                    $items[$row['Label']] = $row;
                }
                self::$cache->cache_value(self::CACHE_ITEM, $items, 0);
            }
            $this->items = $items;
        }
        return $this->items;
    }

    public function flushPriceCache(): void {
        unset($this->items);
        self::$cache->delete_value(self::CACHE_ITEM);
    }

    public function openPoolList(): array {
        $key = self::CACHE_OPEN_POOL;
        $pool = self::$cache->get_value($key);
        if ($pool === false) {
            $pool = self::$db->rowAssoc("
                SELECT bonus_pool_id, name, total
                FROM bonus_pool
                WHERE now() BETWEEN since_date AND until_date
                ORDER BY since_date
                LIMIT 1
            ") ?? [];
            self::$cache->cache_value($key, $pool, 3600);
        }
        return $pool;
    }

    public function addMultiPoints(int $points, array $ids = []): int {
        if (empty($ids)) {
            return 0;
        }
        self::$db->prepared_query("
            UPDATE user_bonus SET
                points = points + ?
            WHERE user_id in (" . placeholders($ids) . ")
            ", $points, ...$ids
        );
        self::$cache->delete_multi(array_map(fn($k) => "user_stats_$k", $ids));
        self::$cache->delete_multi(array_map(fn($k) => "u_$k", $ids));
        return self::$db->affected_rows();
    }

    public function addGlobalPoints(int $points): int {
        self::$db->prepared_query("
            SELECT um.ID
            FROM users_main um
            WHERE NOT EXISTS (
                    SELECT 1 FROM user_has_attr uha
                    INNER JOIN user_attr ua ON (ua.ID = uha.UserAttrID AND ua.Name IN ('disable-bonus-points', 'no-fl-gifts'))
                    WHERE uha.UserID = um.ID
                )
                AND um.Enabled = ?
            ", UserStatus::enabled->value
        );
        return $this->addMultiPoints($points, self::$db->collect('ID'));
    }

    public function addActivePoints(int $points, string $since): int {
        self::$db->prepared_query("
            SELECT um.ID
            FROM users_main um
            INNER JOIN user_last_access ula ON (ula.user_id = um.ID)
            WHERE NOT EXISTS (
                    SELECT 1 FROM user_has_attr uha
                    INNER JOIN user_attr ua ON (ua.ID = uha.UserAttrID AND ua.Name IN ('disable-bonus-points', 'no-fl-gifts'))
                    WHERE uha.UserID = um.ID
                )
                AND um.Enabled      = ?
                AND ula.last_access >= ?
            ", UserStatus::enabled->value, $since
        );
        return $this->addMultiPoints($points, self::$db->collect('ID'));
    }

    public function addUploadPoints(int $points, string $since): int {
        self::$db->prepared_query("
            SELECT DISTINCT um.ID
            FROM users_main um
            INNER JOIN torrents t ON (t.UserID = um.ID)
            WHERE NOT EXISTS (
                    SELECT 1 FROM user_has_attr uha
                    INNER JOIN user_attr ua ON (ua.ID = uha.UserAttrID AND ua.Name IN ('disable-bonus-points', 'no-fl-gifts'))
                    WHERE uha.UserID = um.ID
                )
                AND um.Enabled = ?
                AND t.created >= ?
            ", UserStatus::enabled->value, $since
        );
        return $this->addMultiPoints($points, self::$db->collect('ID'));
    }

    public function addSeedPoints(int $points): int {
        self::$db->prepared_query("
            SELECT DISTINCT um.ID
            FROM users_main um
            INNER JOIN xbt_files_users xfu ON (xfu.uid = um.ID)
            WHERE NOT EXISTS (
                    SELECT 1 FROM user_has_attr uha
                    INNER JOIN user_attr ua ON (ua.ID = uha.UserAttrID AND ua.Name IN ('disable-bonus-points', 'no-fl-gifts'))
                    WHERE uha.UserID = um.ID
                )
                AND xfu.remaining = 0
                AND xfu.active    = 1
                AND um.Enabled    = ?
            ", UserStatus::enabled->value
        );
        return $this->addMultiPoints($points, self::$db->collect('ID'));
    }

    public function givePoints(\Gazelle\Task|null $task = null): int {
        //------------------------ Update Bonus Points -------------------------//
        // calculation:
        // size (convert from bytes to GB) is in torrents
        // seedtime (convert from hours to days) is in xbt_files_history
        // seeders is in torrents_leech_stats
        // bonus_scale how the torrent size is adjusted according to category

        self::$db->dropTemporaryTable("bonus_update");
        self::$db->prepared_query("
            CREATE TEMPORARY TABLE bonus_update (
                user_id int NOT NULL PRIMARY KEY,
                delta float(20, 5) NOT NULL
            )
        ");
        self::$db->prepared_query("
            SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED
        ");
        self::$db->prepared_query("
            INSERT INTO bonus_update (user_id, delta)
            SELECT xfu.uid,
                sum(category_bonus_accrual(t.Size, xfh.seedtime, tls.Seeders, c.bonus_scale))
            FROM (
                SELECT DISTINCT uid, fid
                FROM xbt_files_users
                WHERE remaining = 0
                    AND active  = 1
                    AND mtime   > unix_timestamp(now() - INTERVAL 1 HOUR)
            )                               xfu
            INNER JOIN xbt_files_history    xfh USING (uid, fid)
            INNER JOIN users_main           um  ON (um.ID = xfu.uid)
            INNER JOIN torrents             t   ON (t.ID = xfu.fid)
            INNER JOIN torrents_leech_stats tls ON (tls.TorrentID = t.ID)
            INNER JOIN torrents_group       tg  ON (tg.ID = t.GroupID)
            INNER JOIN category             c   ON (c.category_id = tg.CategoryID)
            WHERE NOT EXISTS (
                    SELECT 1 FROM user_has_attr uha
                    INNER JOIN user_attr ua ON (ua.ID = uha.UserAttrID AND ua.Name IN ('disable-bonus-points'))
                    WHERE uha.UserID = um.ID
                )
                AND um.Enabled    = ?
            GROUP BY xfu.uid
            ", UserStatus::enabled->value
        );
        self::$db->prepared_query("
            SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ
        ");
        $task?->info('bonus_update table constructed');

        self::$db->prepared_query("
            INSERT INTO user_bonus
                (user_id, points)
            SELECT bu.user_id, bu.delta
            FROM bonus_update bu
            ON DUPLICATE KEY UPDATE points = points + bu.delta
        ");
        $processed = self::$db->affected_rows();
        $task?->info('user_bonus updated');

        /* flush their stats */
        self::$db->prepared_query("
            SELECT concat('u_', bu.user_id) FROM bonus_update bu
        ");
        if (self::$db->has_results()) {
            self::$cache->delete_multi(self::$db->collect(0));
        }
        self::$db->dropTemporaryTable("bonus_update");

        return $processed;
    }
}
