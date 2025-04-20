<?php

namespace Gazelle\Manager;

class ForumCategory extends \Gazelle\BaseManager {
    final public const LIST_KEY = 'forum_cat';
    final protected const ID_KEY = 'zz_fc_%d';

    /**
     * Create a forum category
     */
    public function create(string $name, int $sequence): \Gazelle\ForumCategory {
        self::$db->prepared_query("
            INSERT INTO forums_categories
                   (Name, Sort)
            VALUES (?,    ?)
            ", trim($name), $sequence
        );
        $id = self::$db->inserted_id();
        self::$cache->delete_value(self::LIST_KEY);
        return $this->findById($id);
    }

    public function findById(int $id): ?\Gazelle\ForumCategory {
        $key = sprintf(self::ID_KEY, $id);
        $fcatId = self::$cache->get_value($key);
        if ($fcatId === false) {
            $fcatId = (int)self::$db->scalar("
                SELECT ID FROM forums_categories WHERE ID = ?
                ", $id
            );
            if ($fcatId) {
                self::$cache->cache_value($key, $fcatId, 7200);
            }
        }
        return $fcatId ? new \Gazelle\ForumCategory($fcatId) : null;
    }

    /**
     * Get list of forums categories
     */
    public function forumCategoryList(): array {
        $list = self::$cache->get_value(self::LIST_KEY);
        if ($list === false) {
            self::$db->prepared_query("
                SELECT fc.ID AS category_id,
                    fc.Name  AS name,
                    fc.Sort  AS sequence
                FROM forums_categories fc
                ORDER BY fc.Sort,
                    fc.Name
            ");
            $list = self::$db->to_pair('category_id', 'name');
            self::$cache->cache_value(self::LIST_KEY, $list, 0);
        }
        return $list;
    }

    /**
     * Get list of forums categories by usage
     */
    public function usageList(): array {
        self::$db->prepared_query("
            SELECT fc.ID AS id,
                fc.Name  AS name,
                fc.Sort  AS sequence,
                count(f.CategoryID) as total
            FROM forums_categories as fc
            LEFT JOIN forums f ON (f.CategoryID = fc.ID)
            GROUP BY fc.ID
            ORDER BY fc.Sort
        ");
        return self::$db->to_array('id', MYSQLI_ASSOC, false);
    }
}
