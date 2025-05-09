<?php

namespace Gazelle\Manager;

class StaffGroup extends \Gazelle\BaseManager {
    final public const LIST_KEY = 'stgroup';
    final public const ID_KEY   = 'zz_sg_%d';

    public function create(int $sequence, string $name): \Gazelle\StaffGroup {
        self::$db->prepared_query("
            INSERT INTO staff_groups
                   (Sort, Name)
            Values (?,    ?)
            ", $sequence, $name
        );
        return new \Gazelle\StaffGroup(self::$db->inserted_id());
    }

    public function findById(int $id): ?\Gazelle\StaffGroup {
        $key = sprintf(self::ID_KEY, $id);
        $staffGroupId = self::$cache->get_value($key);
        if ($staffGroupId === false) {
            $staffGroupId = (int)self::$db->scalar("
                SELECT ID FROM staff_groups WHERE ID = ?
                ", $id
            );
            if ($staffGroupId) {
                self::$cache->cache_value($key, $staffGroupId, 7200);
            }
        }
        return $staffGroupId ? new \Gazelle\StaffGroup($staffGroupId) : null;
    }

    public function groupList(): array {
        self::$db->prepared_query("
            SELECT ID AS id,
                Sort AS sequence,
                Name AS name
            FROM staff_groups
            ORDER BY Sort
        ");
        return self::$db->to_array(false, MYSQLI_ASSOC);
    }
}
