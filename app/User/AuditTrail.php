<?php

namespace Gazelle\User;

use Gazelle\Enum\UserAuditEvent;
use Gazelle\Enum\UserAuditOrder;

class AuditTrail extends \Gazelle\BaseUser {
    public function flush(): static {
        return $this;
    }

    public function addEvent(UserAuditEvent $event, string $note, \Gazelle\User|null $creator = null): int {
        return $this->pg()->insert("
            insert into user_audit_trail
                   (id_user, event, note, id_user_creator)
            values (?,       ?,     ?,    ?)
            returning id_user_audit_trail
            ", $this->id(), $event->value, $note, $creator?->id()
        );
    }

    /**
     * Used to migrate the old staff notes into the audit trail
     */
    public function addHistoricalEvent(string $date, string $note, \Gazelle\Manager\User $manager): int {
        $creator = null;
        if (str_starts_with($note, 'Disabled for inactivity')) {
            $event = UserAuditEvent::activity;
        } elseif (str_starts_with($note, 'Class changed to ')) {
            $event = UserAuditEvent::userclass;
        } elseif (preg_match('/^Leeching (?:ability|privileges) suspended |Taken off ratio watch /', $note)) {
            $note = str_replace('Leeching ability', 'Leeching privileges', $note); // consistency
            $event = UserAuditEvent::ratio;
        } else {
            if (preg_match('/( by ([\w.-]+))/', $note, $match)) {
                $creator = $manager->find("@{$match[2]}");
                $note    = str_replace($match[1], '.', $note);
            }
            $event = UserAuditEvent::historical;
        }
        return $this->pg()->insert("
            insert into user_audit_trail
                   (id_user, event, note, created, id_user_creator)
            values (?,       ?,     ?,    ?,       ?)
            returning id_user_audit_trail
            ", $this->id(), $event->value, $note, $date, (int)$creator?->id()
        );
    }

    public function hasEvent(UserAuditEvent $event): bool {
        return (bool)$this->pg()->scalar("
            select 1
            from user_audit_trail
            where id_user = ?
                and event = ?
            ", $this->id(), $event->value
        );
    }

    public function eventList(array $idList, UserAuditOrder $order = UserAuditOrder::created): array {
        return $this->pg()->all("
            select id_user_audit_trail,
                id_user_creator,
                event,
                note,
                created
            from user_audit_trail
            where id_user = ?
                and id_user_audit_trail in (" . placeholders($idList) . ")
            order by {$order->value}
            ", $this->id(), ...$idList
        );
    }

    public function fullEventList(UserAuditOrder $order = UserAuditOrder::created): array {
        return $this->pg()->all("
            select id_user_audit_trail,
                id_user_creator,
                event,
                note,
                created
            from user_audit_trail
            where id_user = ?
            order by {$order->value}
            ", $this->id()
        );
    }

    /**
     * Return the id of the most recent audit event. (0 if no events)
     */
    public function lastEventId(): int {
        return (int)$this->pg()->scalar("
            select id_user_audit_trail
            from user_audit_trail
            where id_user = ?
            order by id_user_audit_trail desc
            limit 1
            ", $this->id()
        );
    }

    /**
     * Migrate the old users_info.AdminComments to the new audit trail.
     * Returns 0 if the user has already been migrated, otherwise
     * returns the id_user_audit_trail value
     */
    public function migrate(\Gazelle\Manager\User $manager): int {
        if ($this->hasEvent(UserAuditEvent::historical)) {
            return 0;
        }
        $prevDate   = false;
        $lastEvent  = 0;
        // split the legacy staff notes into separate entries
        $historical = preg_split("/\r?\n\r?\n/", $this->user->staffNotes());
        if ($historical) {
            foreach (array_reverse($historical) as $entry) {
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - (.*)/s', $entry, $match)) {
                    $date = $match[1];
                    $note = $match[2];
                } else {
                    // this note is not prefixed with a timestamp. If this is the
                    // first note, use the date the user was created, otherwise
                    // use the timestamp of the previous event.
                    $date = $prevDate === false ? $this->user->created() : $prevDate;
                    $note = $entry;
                }
                $lastEvent = $this->addHistoricalEvent($date, $note, $manager);
                $prevDate  = $date;
            }
        }
        if ($lastEvent === 0) {
            $lastEvent = $this->addEvent(UserAuditEvent::historical, "no prior staff notes");
        }
        return $lastEvent;
    }

    public function removeEvent(int $eventId): int {
        return $this->pg()->prepared_query("
            delete from user_audit_trail
            where id_user_audit_trail = ?
                and id_user = ?
            ", $eventId, $this->id()
        );
    }

    public function resetAuditTrail(): int {
        return $this->pg()->prepared_query("
            delete from user_audit_trail where id_user = ?
            ", $this->id()
        );
    }

    /**
     * When updating a list of events, copy the list over to the revision table.
     * Then remove all the events in the audit table except for the oldest one,
     * and update its note with the new contents.
     */
    public function modifyEventList(array $idList, string $note, \Gazelle\User $user): int {
        $affected = $this->pg()->prepared_query("
            insert into user_audit_trail_revision
            (id_user_audit_trail, id_user, id_user_creator, created, event, note, revision)
            select uat.id_user_audit_trail, 
                uat.id_user,
                uat.id_user_creator,
                uat.created,
                uat.event,
                uat.note,
                1 + count(uatr.id_user_audit_trail) as revision
            from user_audit_trail uat
            left join user_audit_trail_revision uatr using (id_user_audit_trail)
            where id_user_audit_trail in (" . placeholders($idList) . ")
            group by uat.id_user_audit_trail
            ", ...$idList
        );
        if (!$note) {
            // delete
            $this->pg()->prepared_query("
                delete from user_audit_trail
                where id_user_audit_trail in (" . placeholders($idList) . ")
                ", ...$idList
            );
        } elseif (count($idList)) {
            // edit
            $eventId = min($idList);
            $this->pg()->prepared_query("
                delete from user_audit_trail
                where id_user_audit_trail != ?
                    and id_user_audit_trail in (" . placeholders($idList) . ")
                ", $eventId, ...$idList
            );
            $this->pg()->prepared_query("
                update user_audit_trail set
                    note = ?,
                    id_user_creator = ?
                where id_user_audit_trail = ?
                ", $note, $user->id(), $eventId
            );
        }
        return $affected;
    }
}
