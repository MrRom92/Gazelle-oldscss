<?php

namespace Gazelle\Manager;

class PM extends \Gazelle\BaseUser {
    final public const tableName  = 'pm_conversations_users';
    protected const ID_KEY = 'zz_pm_%d_%d';

    public function flush(): static {
        $this->user()->flush();
        return $this;
    }

    public function link(): string {
        return $this->user()->link();
    }

    public function location(): string {
        return $this->user()->location();
    }

    public function findById(int $id): ?\Gazelle\PM {
        $key = sprintf(self::ID_KEY, $id, $this->user->id);
        $pmId = self::$cache->get_value($key);
        if ($pmId === false) {
            $pmId = (int)self::$db->scalar("
                SELECT pcu.ConvID
                FROM pm_conversations_users pcu
                WHERE pcu.ConvID = ?
                    AND pcu.UserID = ?
                ", $id, $this->user->id
            );
            if ($pmId) {
                self::$cache->cache_value($key, $pmId, 7200);
            }
        }
        return $pmId ? new \Gazelle\PM($pmId, $this->user) : null;
    }

    public function findByPostId(int $id): ?\Gazelle\PM {
        $pmId = (int)self::$db->scalar("
            SELECT pcu.ConvID
            FROM pm_conversations_users pcu
            INNER JOIN pm_messages      pm USING (ConvID)
            WHERE pcu.UserID = ?
                AND pm.ID = ?
            ", $this->user->id, $id
        );
        return $pmId ? new \Gazelle\PM($pmId, $this->user) : null;
    }

    public function remove(): int {
        // this class should probably not inherit from BaseUser...
        return 0;
    }
}
