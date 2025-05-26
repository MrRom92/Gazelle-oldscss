<?php

namespace Gazelle\Util;

/**
 * tracks a couple of recently disabled users in memcached
 */
class DisabledUserHistory extends \Gazelle\Base {
    protected const string CACHE_KEY = 'recently_disabled_users';
    protected const int MAX_ENTRIES = 50;

    public static function add(\Gazelle\User $user, string $reason): void {
        $list = static::$cache->get_value(static::CACHE_KEY) ?: [];
        $list[] = [$user->id, $reason, time()];
        $list = array_slice($list, -static::MAX_ENTRIES);
        static::$cache->cache_value(static::CACHE_KEY, $list, 3 * 24 * 60 * 60);
    }

    /**
     * @return array [[user_id, disable_reason, timestamp],...]
     */
    public static function get(): array {
        return static::$cache->get_value(static::CACHE_KEY) ?: [];
    }
}
