<?php

namespace Gazelle\User;

/**
 * The functionality for a user to invite other users is delegated to this class.
 */

class Invite extends \Gazelle\BaseUser {
    public function flush(): static {
        $this->user()->flush();
        return $this;
    }

    public function issueInvite(): bool {
        if ($this->user()->permitted('site_send_unlimited_invites')) {
            return true;
        }
        self::$db->prepared_query("
            UPDATE users_main SET
                Invites = GREATEST(Invites, 1) - 1
            WHERE ID = ?
            ", $this->user->id
        );
        $affected = self::$db->affected_rows();
        $this->flush();
        return $affected > 0;
    }

    public function pendingTotal(): int {
        return (int)self::$db->scalar("
            SELECT count(*) FROM invites WHERE InviterID = ?
            ", $this->user->id
        );
    }

    public function pendingList(): array {
        self::$db->prepared_query("
            SELECT i.InviteKey AS invite_key,
                i.Email        AS email,
                i.Expires      AS expires,
                ivs.name       AS source_name
            FROM invites i
            LEFT JOIN invite_source_pending ivsp ON (ivsp.invite_key = i.InviteKey)
            LEFT JOIN invite_source ivs USING (invite_source_id)
            WHERE i.InviterID = ?
            ORDER BY i.Expires
            ", $this->user->id
        );
        return self::$db->to_array('invite_key', MYSQLI_ASSOC, false);
    }

    public function total(): int {
        return (int)self::$db->scalar("
            SELECT count(*) FROM users_main WHERE inviter_user_id = ?
            ", $this->user->id
        );
    }

    public function page(string $orderBy, string $direction, int $limit, int $offset): array {
        self::$db->prepared_query("
            SELECT um.ID
            FROM users_main AS um
            INNER JOIN users_leech_stats AS uls ON (uls.UserID = um.ID)
            LEFT  JOIN user_last_access AS ula ON (ula.user_id = um.ID)
            WHERE um.inviter_user_id = ?
            ORDER BY $orderBy $direction
            LIMIT ? OFFSET ?
            ", $this->user->id, $limit, $offset
        );
        return self::$db->collect(0, false);
    }

    /**
     * Revoke an active invitation (restore previous invite total)
     */
    public function revoke(string $key): int {
        self::$db->begin_transaction();
        self::$db->prepared_query("
            DELETE FROM invites WHERE InviteKey = ?
            ", $key
        );
        $affected = self::$db->affected_rows();
        if ($affected == 0) {
            self::$db->rollback();
            return 0;
        }
        if ($this->user()->permitted('site_send_unlimited_invites')) {
            self::$db->commit();
            return $affected;
        }
        self::$db->prepared_query("
            DELETE FROM invite_source_pending WHERE invite_key = ?
            ", $key
        );
        $affected += self::$db->affected_rows();
        self::$db->prepared_query("
            UPDATE users_main SET
                Invites = Invites + 1
            WHERE ID = ?
            ", $this->user->id
        );
        $affected += self::$db->affected_rows();
        self::$db->commit();
        $this->user()->flush();
        return $affected;
    }
}
