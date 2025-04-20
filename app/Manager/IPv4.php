<?php

namespace Gazelle\Manager;

/**
 * This class handles both the user IP site history as well as
 * the bans placed upon IP addresses.
 */

class IPv4 extends \Gazelle\Base {
    protected string $filterBegin;
    protected string $filterEnd;
    protected string $filterIpaddr;
    protected string $filterIpaddrRegexp;

    public function flush(): static {
        unset(
            $this->filterEnd,
            $this->filterBegin,
            $this->filterIpaddr,
            $this->filterIpaddrRegexp,
        );
        return $this;
    }

    public function register(\Gazelle\User $user, string $ipv4): int {
        $this->pg()->prepared_query("
            insert into ip_history
                   (id_user, ip, data_origin)
            values (?,       ?,  'site')
            on conflict (id_user, ip, data_origin) do update set
                total = ip_history.total + 1,
                seen = tstzrange(lower(ip_history.seen), now())
            ", $user->id, $ipv4
        );
        self::$db->prepared_query('
            INSERT INTO users_history_ips
                   (UserID, IP)
            VALUES (?,      ?)
            ON DUPLICATE KEY UPDATE EndTime = now()
            ', $user->id, $ipv4
        );
        $affected = self::$db->affected_rows();
        $user->setField('IP', $ipv4)
            ->setField('ipcc', (new \Gazelle\Util\GeoIP(new \Gazelle\Util\Curl()))->countryISO($ipv4))
            ->modify();
        self::$cache->delete_value(sprintf('ipv4_dup_' . str_replace('-', '_', $ipv4)));
        $this->flush();
        return $affected;
    }

    public function setFilterBegin(string $begin): static {
        $this->filterBegin = $begin;
        return $this;
    }

    public function setFilterEnd(string $end): static {
        $this->filterEnd   = $end;
        return $this;
    }

    public function setFilterIpaddr(string $filterIpaddr): static {
        $this->filterIpaddr = $filterIpaddr;
        return $this;
    }

    public function setFilterIpaddrRegexp(string $filterIpaddrRegexp): static {
        $this->filterIpaddrRegexp = $filterIpaddrRegexp;
        return $this;
    }

    public function userTotal(\Gazelle\User $user): int {
        $cond = ['uhi.UserID = ?'];
        $args = [$user->id];
        if (isset($this->filterIpaddrRegexp)) {
            $cond[] = "uhi.IP REGEXP ?";
            $args[] = $this->filterIpaddrRegexp;
        }
        if (isset($this->filterIpaddr)) {
            $cond[] = "uhi.IP = ?";
            $args[] = $this->filterIpaddr;
        }
        if (isset($this->filterBegin)) {
            $cond[] = "uhi.StartTime BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)";
            array_push($args, $this->filterBegin, $this->filterEnd);
        }
        $where  = join(' AND ', $cond);
        return (int)self::$db->scalar("
            SELECT count(DISTINCT IP) FROM users_history_ips uhi WHERE $where
            ", ...$args
        );
    }

    public function duplicateTotal(\Gazelle\User $user): int {
        $cacheKey = "ipv4_dup_" . str_replace('.', '_', $user->ipaddr());
        $value = self::$cache->get_value($cacheKey);
        if ($value === false) {
            $value = (int)self::$db->scalar("
                SELECT count(*) FROM users_history_ips WHERE IP = ?
                ", $user->ipaddr()
            );
            self::$cache->cache_value($cacheKey, $value, 3600);
        }
        return max(0, (int)$value - 1);
    }

    /**
     * returns array of userids that match filters, excluding specified user
     */
    public function userOther(\Gazelle\User $user): array {
        $cond = ['uhi.UserID != ?'];
        $args = [$user->id];
        if (isset($this->filterIpaddrRegexp)) {
            $cond[] = "uhi.IP REGEXP ?";
            $args[] = $this->filterIpaddrRegexp;
        }
        if (isset($this->filterIpaddr)) {
            $cond[] = "uhi.IP = ?";
            $args[] = $this->filterIpaddr;
        }
        if (isset($this->filterBegin)) {
            $cond[] = "uhi.StartTime BETWEEN ? AND ?";
            array_push($args, $this->filterBegin, $this->filterEnd);
        }
        $where  = join(' AND ', $cond);
        self::$db->prepared_query("
            SELECT DISTINCT UserID FROM users_history_ips uhi WHERE $where
            ", ...$args
        );
        return array_map(
            fn ($v) => $v['UserID'],
            self::$db->to_array(false, MYSQLI_ASSOC, false)
        );
    }

    public function userPage(\Gazelle\User $user, int $limit, int $offset): array {
        self::$db->prepared_query("SET SESSION group_concat_max_len = 50000");
        $cond = ['i.UserID = ?'];
        $args = [$user->id];
        if (isset($this->filterIpaddrRegexp)) {
            $cond[] = "i.IP REGEXP ?";
            $args[] = $this->filterIpaddrRegexp;
        }
        if (isset($this->filterIpaddr)) {
            $cond[] = "i.IP = ?";
            $args[] = $this->filterIpaddr;
        }
        $where  = join(' AND ', $cond);
        $args[] = $limit;
        $args[] = $offset;
        self::$db->prepared_query("
            SELECT uhi.IP as ip_addr,
                count(DISTINCT UserID) as nr_users,
                group_concat(
                    concat(UserID, '/', StartTime, '/', coalesce(EndTime, now()))
                    ORDER BY if(UserID = ?, 0, 1), StartTime DESC
                ) AS ranges,
                min(uhi.StartTime) AS min_start,
                coalesce(max(uhi.EndTime), now()) AS max_end,
                exists (SELECT ib.ID FROM ip_bans ib WHERE inet_aton(uhi.IP) BETWEEN ib.FromIP AND ib.ToIP) AS is_banned
            FROM users_history_ips uhi
            WHERE IP IN (
                    SELECT DISTINCT i.IP
                    FROM users_history_ips i
                    WHERE $where
                )
            GROUP BY uhi.IP
            ORDER BY max_end DESC, ip_addr
            LIMIT ? OFFSET ?
            ", $user->id, ...$args
        );
        return self::$db->to_array(false, MYSQLI_ASSOC, false);
    }
}
