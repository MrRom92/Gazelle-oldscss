<?php

namespace Gazelle\User\Notification;

class Collage extends AbstractNotification {
    public function className(): string {
        return 'confirmation';
    }

    public function clear(): int {
        self::$db->prepared_query("
            UPDATE users_collage_subs SET
                LastVisit = now()
            WHERE UserID = ?
            ", $this->user->id
        );
        $affected = self::$db->affected_rows();
        self::$cache->delete_value(sprintf(\Gazelle\Collage::SUBS_NEW_KEY, $this->user->id));
        return $affected;
    }

    public function load(): bool {
        if ($this->user->permitted('site_collages_subscribe')) {
            $total = $this->user->collageUnreadCount();
            if ($total) {
                $this->title = 'You have ' . article($total) . ' collage update' . plural($total);
                $this->url   = 'userhistory.php?action=subscribed_collages';
                return true;
            }
        }
        return false;
    }

    public function clearCollage(\Gazelle\Collage $collage): int {
        self::$db->prepared_query("
            UPDATE users_collage_subs SET
                LastVisit = now()
            WHERE UserID = ? AND CollageID = ?
            ", $this->user->id, $collage->id
        );
        $affected = self::$db->affected_rows();
        self::$cache->delete_value(sprintf(\Gazelle\Collage::SUBS_NEW_KEY, $this->user->id));
        return $affected;
    }
}
